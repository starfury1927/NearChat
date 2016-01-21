<?php

namespace NearChat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use NearChat\task\GetformatTask;

class NearChat extends PluginBase implements Listener {
	private $config;
	public function onEnable() {
		$this->LoadConfig ();
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	public function LoadConfig() {
		$this->saveResource ( "config.yml" );
		$this->config = (new Config ( $this->getDataFolder () . "config.yml", Config::YAML ))->getAll ();
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
				if ($player->distance ( $target->getPosition () ) < $this->config ["chat-distance"] && $player->getLevel ()->getName () == $target->getLevel ()->getName ()) {
					$target->sendMessage ( $message );
				}
			}
		}
	}
}
?>