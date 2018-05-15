<?php
namespace muqsit\mcmmo;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener{

	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin){
		$this->plugin = $plugin;
	}

	public function onPlayerLogin(PlayerLoginEvent $event) : void{
		$this->plugin->getDatabase()->load($event->getPlayer());
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$this->plugin->getDatabase()->save($event->getPlayer(), true);
	}
}