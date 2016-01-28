<?php

namespace NearChat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\command\ConsoleCommandSender;

class NearChat extends PluginBase implements Listener {
	private $config, $mute = false;
	/**
	 *
	 * @var EconomyAPI
	 */
	private $economy;
	public function onEnable() {
		$this->LoadConfig ();
		$this->economy = $this->getServer ()->getPluginManager ()->getPlugin ( "EconomyAPI" );
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	public function LoadConfig() {
		$this->saveResource ( "config.yml" );
		$this->config = (new Config ( $this->getDataFolder () . "config.yml", Config::YAML ))->getAll ();
		if (! isset ( $this->config ["speaker-cost"] )) {
			$this->saveResource ( "config.yml", true );
			$this->config = (new Config ( $this->getDataFolder () . "config.yml", Config::YAML ))->getAll ();
		}
	}
	public function onPlayerCommand(PlayerCommandPreprocessEvent $event) {
		$player = $event->getPlayer ();
		$message = $event->getMessage ();
		$args = explode ( " ", $message );
		if (strtolower($args [0]) == '/me' and ! $player->isOp ()) {
			$player->sendMessage ( TextFormat::RED . "당신은 이 명령어를 사용할 권한이 없습니다." );
			$event->setCancelled ();
		}
	}
	public function onChat(PlayerChatEvent $event) {
		$player = $event->getPlayer ();
		if ($this->isMute () and ! $player->isOp ()) {
			$event->setCancelled ();
			$player->sendMessage ( TextFormat::RED . "현재 채팅을 할 수 없습니다." );
		}
		$recipients = [ new ConsoleCommandSender () ];
		if ($player->isOp ()) {
			$recipients = $this->getServer ()->getOnlinePlayers ();
		} else {
			foreach ( $this->getServer ()->getOnlinePlayers () as $target ) {
				if (($player->distance ( $target ) <= $this->config ['chat-distance'] and $player->getLevel () === $target->getLevel ()) or $target->isOp ()) {
					array_push ( $recipients, $target );
				}
			}
		}
		$event->setRecipients ( $recipients );
	}
	public function onCommand(CommandSender $sender, Command $command, $label, Array $args) {
		if (strtolower ( $command ) == '확성기') {
			if (! isset ( $args [0] )) {
				return false;
			}
			if ($this->economy == null) {
				$sender->sendMessage ( "EconomyAPI 플러그인이 없어서 이 명령어를 사용할 수 없습니다." );
				return true;
			}
			if ($this->isMute () && ! $sender->isOp ()) {
				$sender->sendMessage ( TextFormat::RED . "현재 채팅을 할 수 없습니다." );
				return true;
			}
			if ($this->economy->reduceMoney ( $sender, $this->config ["speaker-cost"] ) != EconomyAPI::RET_SUCCESS) {
				$sender->sendMessage ( TextFormat::RED . "확성기를 사용할 돈이 부족합니다. (비용: {$this->config["speaker-cost"]})" );
				return true;
			}
			$message = implode ( " ", $args );
			$this->getServer ()->broadcastMessage ( TextFormat::AQUA . "[확성기] " . $sender->getName () . " > " . $message );
			$sender->sendMessage ( "{$this->config["speaker-cost"]}원을 내고 확성기를 사용하였습니다." );
		} else if (strtolower ( $command ) == 'mute') {
			if ($this->isMute ()) {
				$this->setMute ( false );
				$this->getServer ()->broadcastMessage ( TextFormat::DARK_AQUA . "관리자가 채팅을 허용상태로 변경했습니다." );
			} else {
				$this->setMute ( true );
				$this->getServer ()->broadcastMessage ( TextFormat::DARK_AQUA . "관리자가 채팅을 비허용상태로 변경했습니다." );
			}
		}
		return true;
	}
	/**
	 *
	 * @return boolean
	 */
	public function isMute() {
		return $this->mute;
	}
	/**
	 *
	 * @param boolean $bool        	
	 */
	public function setMute($bool = true) {
		$this->mute = $bool;
	}
}
?>
