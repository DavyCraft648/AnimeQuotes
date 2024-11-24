<?php
declare(strict_types=1);

namespace DavyCraft648\AnimeQuotes;

use DavyCraft648\AnimeQuotes\task\FetchQuotesTask;
use DavyCraft648\AnimeQuotes\utils\FormUtils;
use DavyCraft648\AnimeQuotes\utils\QuotesUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use vezdehod\packs\ContentFactory;
use function array_shift;
use function implode;

final class Main extends PluginBase{

	public static bool $process = false;
	private static Main $instance;

	public static function getInstance() : Main{
		return self::$instance;
	}

	protected function onLoad() : void{
		self::$instance = $this;
		$this->saveDefaultConfig();
		QuotesUtils::init($this->getConfig());
		FormUtils::init(ContentFactory::create($this));
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(!($sender instanceof Player)){
			return true;
		}
		$sub = array_shift($args);
		if($sub === "help"){
			return false;
		}
		if(self::$process){
			$sender->sendMessage("§cSedang proses yang lainnya");
			return true;
		}
		self::$process = true;
		$sender->sendMessage("§aMemproses...");
		$this->getServer()->getAsyncPool()->submitTask(new FetchQuotesTask(
			$sender->getName(),
			QuotesUtils::$language === "id" ? QuotesUtils::$link : null,
			QuotesUtils::$databasePath,
			match ($sub){
				"karakter", "character", "char" => FetchQuotesTask::CHARACTER,
				"anime", "title" => FetchQuotesTask::ANIME,
				default => FetchQuotesTask::RANDOM
			},
			implode(" ", $args)
		));
		return true;
	}
}
