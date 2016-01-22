<?php

namespace NearChat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use NearChat\task\GetformatTask;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use onebone\economyapi\EconomyAPI;

class NearChat extends PluginBase implements Listener {
	private $config;
	/**
	 * 
	 * @var EconomyAPI
	 */
	private $economy;
	public function onEnable() {
		$this->LoadConfig ();
		$this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	public function LoadConfig() {
		$this->saveResource ( "config.yml" );
		$this->config = (new Config ( $this->getDataFolder () . "config.yml", Config::YAML ))->getAll ();
		if (!isset($this->config["speaker-cost"])) {
			$this->saveResource("config.yml", true);
			$this->config = (new Config ( $this->getDataFolder () . "config.yml", Config::YAML ))->getAll ();
		}
	}
	public function onChat(PlayerChatEvent $event) {
		if(! $event->getPlayer()->isOp()) {
			$event->setCancelled();
		}
		$this->getServer()->getScheduler()->scheduleDelayedTask(new GetformatTask($this, $event), 5);
	}
	/**
	 *
	 * @param Player $player        	
	 * @param string $message        	
	 * @param PlayerChatEvent $event        	
	 */
	public function sendChat(Player $player, $message) {
		if (! $player->isOp ()) {
			$this->getLogger ()->info ( $message );
			foreach ( $this->getServer ()->getOnlinePlayers () as $target ) {
				if (($player->distance  ($target->getPosition () ) < $this->config ["chat-distance"] && $player->getLevel ()->getName () == $target->getLevel ()->getName ()) || $target->isOp()) {
					$target->sendMessage ( $message );
				}
			}
		}
	}
	public function onCommand(CommandSender $sender, Command $command, $label, Array $args) {
		if (strtolower($command) == '확성기') {
			if(!isset($args[0])) {
				$sender->sendMessage("도움말: /확성기 <메세지>");
				return true;
			}
			if($this->economy == null) {
				$sender->sendMessage("EconomyAPI 플러그인이 없어서 이 명령어를 사용할 수 없습니다.");
				return true;
			}
			if($this->economy->reduceMoney($sender, $this->config["speaker-cost"]) == EconomyAPI::RET_CANCELLED) {
				$sender->sendMessage(TextFormat::RED."확성기를 사용할 돈이 부족합니다. (비용: {$this->config["speaker-cost"]}");
				return true;
			}
			$message = implode(" ", $args);
			$this->getServer()->broadcastMessage(TextFormat::AQUA."[확성기] ".$sender->getName()." > ".$message);
			$sender->sendMessage("{$this->config["speaker-cost"]}원을 내고 확성기를 사용하였습니다.");
		}
	}
}
?>