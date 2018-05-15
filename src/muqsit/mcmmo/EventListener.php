<?php
namespace muqsit\mcmmo;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Tool;
use pocketmine\utils\TextFormat;

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

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$action = $event->getAction();
		if($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK || $action === PlayerInteractEvent::RIGHT_CLICK_AIR){
			$item = $event->getItem();
			if($item instanceof Tool){
				$player = $event->getPlayer();
				$skill_manager = $this->plugin->getSkillManager($player);
				$skill = $skill_manager->getSkillByItem($item);
				if($skill !== null){
					$player->sendMessage($skill_manager->activateAbility($skill->getId()) ? TextFormat::GREEN . "**" . strtoupper($skill->getAbilityName()) . TextFormat::GREEN . " ACTIVATED**" :
						TextFormat::RED . "You are too tired to use that ability again. " . TextFormat::YELLOW . "(" . $skill->getAbilityCooldownExpire() . "s)"
					);
				}
			}
		}
	}
}