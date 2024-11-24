<?php
declare(strict_types=1);

namespace DavyCraft648\AnimeQuotes\utils;

use DavyCraft648\AnimeQuotes\Main;
use pocketmine\utils\Config;
use function is_array;
use function substr;

final class QuotesUtils{

	public static ?string $link = null;
	public static string $language = "id";
	public static array $contents = [
		"character-name" => "Nama Karakter",
		"anime-name" => "Anime",
		"episode" => "Episode",
		"quotes-date" => "Tanggal"
	];

	public static string $databasePath;

	public static function init(Config $config) : void{
		$config->setDefaults([
			"link-updater" => null,
			"language" => self::$language,
			"contents" => self::$contents
		]);
		self::$link = $config->get("link-updater", self::$link);
		$language = $config->get("language", self::$language);
		if($language !== "id" && $language !== "en"){
			$config->set("language", self::$language);
		}else{
			self::$language = $language;
		}
		$contents = $config->get("contents", self::$contents);
		if(is_array($contents)){
			foreach($contents as $i => $name){
				if(isset(self::$contents[$i])){
					self::$contents[$i] = $name;
				}
			}
			foreach(self::$contents as $i => $name){
				if(!isset($contents[$i])){
					unset(self::$contents[$i]);
				}
			}
		}
		$config->set("contents", self::$contents);
		if($config->hasChanged()){
			$config->save();
		}

		Main::getInstance()->saveResource("quotes-" . self::$language . ".sqlite3");
		self::$databasePath = substr($config->getPath(), 0, -10) . "quotes-" . self::$language . ".sqlite3";
		self::initDB(self::$databasePath);
	}

	private static function initDB(string $path) : void{
		$database = new \SQLite3($path);
		$database->exec("CREATE TABLE IF NOT EXISTS quotes(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            CharName TEXT NOT NULL,
            ImageUrl TEXT NOT NULL,
            Anime TEXT NOT NULL,
            Episode TEXT NOT NULL,
            DateAdded TEXT NOT NULL,
            Quotes TEXT NOT NULL
        )");
		$database->close();
	}

	public static function insertQuotes(
		string $databasePath,
		string $charName,
		string $imageUrl,
		string $anime,
		string $episode,
		string $dateAdded,
		string $quotes
	) : void{
		$database = new \SQLite3($databasePath);
		$stmt = $database->prepare("INSERT OR IGNORE INTO quotes(id, CharName, ImageUrl, Anime, Episode, DateAdded, Quotes) VALUES (
			(SELECT id FROM quotes WHERE CharName = :CharName AND ImageUrl = :ImageUrl AND Anime = :Anime AND Episode = :Episode AND DateAdded = :DateAdded),
			:CharName, :ImageUrl, :Anime, :Episode, :DateAdded, :Quotes
		)");
		$stmt->bindValue(":CharName", $charName, SQLITE3_TEXT);
		$stmt->bindValue(":ImageUrl", $imageUrl, SQLITE3_TEXT);
		$stmt->bindValue(":Anime", $anime, SQLITE3_TEXT);
		$stmt->bindValue(":Episode", $episode, SQLITE3_TEXT);
		$stmt->bindValue(":DateAdded", $dateAdded, SQLITE3_TEXT);
		$stmt->bindValue(":Quotes", $quotes, SQLITE3_TEXT);
		$stmt->execute();
		$stmt->close();
		$database->close();
	}

	public static function getRandomQuotes(string $databasePath) : array{
		$database = new \SQLite3($databasePath);
		$result = $database->query("SELECT * FROM quotes ORDER BY RANDOM() LIMIT 1");
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$database->close();
		return $row;
	}

	public static function getRandomQuotesByCharacter(string $databasePath, string $character) : ?array{
		$database = new \SQLite3($databasePath);
		$query = $database->prepare("SELECT * FROM quotes WHERE LOWER(CharName) LIKE LOWER(:CharName) ORDER BY RANDOM() LIMIT 1");
		$query->bindValue(':CharName', "%$character%", SQLITE3_TEXT);
		$result = $query->execute();
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$database->close();
		if($row === false){
			return null;
		}
		return $row;
	}

	public static function getRandomQuotesByAnime(string $databasePath, string $anime) : ?array{
		$database = new \SQLite3($databasePath);
		$query = $database->prepare("SELECT * FROM quotes WHERE LOWER(Anime) LIKE LOWER(:Anime) ORDER BY RANDOM() LIMIT 1");
		$query->bindValue(':Anime', "%$anime%", SQLITE3_TEXT);
		$result = $query->execute();
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$database->close();
		if($row === false){
			return null;
		}
		return $row;
	}

	public static function getAllCharacters(string $databasePath) : array{
		$database = new \SQLite3($databasePath);
		$result = $database->query("SELECT DISTINCT CharName FROM quotes");
		$ret = [];
		while($row = $result->fetchArray(SQLITE3_ASSOC)){
			$ret[] = $row["CharName"];
		}
		$database->close();
		return $ret;
	}

	public static function getAllAnime(string $databasePath) : array{
		$database = new \SQLite3($databasePath);
		$result = $database->query("SELECT DISTINCT Anime FROM quotes");
		$ret = [];
		while($row = $result->fetchArray(SQLITE3_ASSOC)){
			$ret[] = $row["Anime"];
		}
		$database->close();
		return $ret;
	}
}
