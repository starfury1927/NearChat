<?php
namespace NearChat\task;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\player\PlayerChatEvent;
use NearChat\NearChat;
class GetformatTask extends PluginTask {
	private $event, $plugin;
	public function __construct(NearChat $owner, PlayerChatEvent $event) {
		parent::__construct($owner);
		$this->plugin = $owner;
		$this->event = &$event;
	}
	public function onRun($currentTick) {
		$this->plugin->sendChat($this->event->getPlayer(), $this->event->getFormat());
	}
}
?>