<?php
declare(strict_types=1);

namespace DavyCraft648\AnimeQuotes\task;

use DavyCraft648\AnimeQuotes\Main;
use DavyCraft648\AnimeQuotes\utils\FormUtils;
use DavyCraft648\AnimeQuotes\utils\QuotesUtils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use function json_decode;

final class FetchQuotesTask extends AsyncTask{

	public const RANDOM = 0;
	public const CHARACTER = 1;
	public const ANIME = 2;

	public function __construct(private ?string $playerName, private ?string $url, private ?string $database, private int $search, private string $opt){}

	public function onRun() : void{
		if($this->url !== null && $this->search === self::RANDOM){
			try{
				$result = Internet::getURL($this->url, err: $error);
				if($error !== null || $result === null || $result->getCode() !== 200){
					$this->setResult(["type" => "error", "error" => $error, "code" => $result?->getCode(), "body" => $result?->getBody()]);
				}else{
					$data = json_decode($result->getBody());
					$this->setResult([
						"type" => "result",
						"imageUrl" => $data->img,
						"char" => $data->char_name,
						"anime" => $data->anime,
						"episode" => $data->episode,
						"date" => $data->date,
						"quotes" => $data->quotes
					]);
					if($this->database !== null){
						QuotesUtils::insertQuotes(
							$this->database,
							$data->char_name,
							$data->img,
							$data->anime,
							$data->episode,
							$data->date,
							$data->quotes
						);
					}
					return;
				}
			}catch(\Throwable $e){
				// $this->setResult(["type" => "crash", "message" => $e->getMessage(), "class" => get_class($e)]);
			}
		}
		$row = match ($this->search){
			self::RANDOM => QuotesUtils::getRandomQuotes($this->database),
			self::CHARACTER => QuotesUtils::getRandomQuotesByCharacter($this->database, $this->opt),
			self::ANIME => QuotesUtils::getRandomQuotesByAnime($this->database, $this->opt),
		};
		if($row === null){
			$this->setResult(["type" => "notfound", "msg" => "§c" . match ($this->search) {
				self::CHARACTER => "Karakter",
				self::ANIME => "Anime",
			} . " yang dicari tidak ada!"]);
			return;
		}

		$this->setResult([
			"type" => "result",
			"imageUrl" => $row["ImageUrl"],
			"char" => $row["CharName"],
			"anime" => $row["Anime"],
			"episode" => $row["Episode"],
			"date" => $row["DateAdded"],
			"quotes" => $row["Quotes"]
		]);
	}

	public function onCompletion() : void{
		Main::$process = false;
		if($this->playerName === null){
			return;
		}
		$player = Server::getInstance()->getPlayerExact($this->playerName);
		if($player === null){
			return;
		}
		$result = $this->getResult();
		switch($result["type"]){
			case "error":
				$player->sendMessage("§cTidak dapat mengambil data");
				break;
			case "crash":
				$player->sendMessage("§cTerjadi crash saat mengambil data");
				break;
			case "notfound":
				$player->sendMessage($result["msg"]);
				break;
			case "result":
				FormUtils::sendForm(
					$player,
					$result["imageUrl"],
					$result["char"],
					$result["anime"],
					$result["episode"],
					$result["date"],
					$result["quotes"]
				);
		}
	}
}
