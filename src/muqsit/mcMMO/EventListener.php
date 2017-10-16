<?php
namespace muqsit\mcMMO;

use pocketmine\event\{EventPriority, Listener};
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\{PlayerInteractEvent, PlayerJoinEvent};
use pocketmine\plugin\MethodEventExecutor;

class EventListener implements Listener{

    /** @var mcMMO */
    private $plugin;

    public function __construct(mcMMO $plugin){
        $this->plugin = $plugin;

        $this->registerHandler(BlockBreakEvent::class, "onBlockBreak", EventPriority::MONITOR, true);
        $this->registerHandler(PlayerInteractEvent::class, "onPlayerInteract", EventPriority::MONITOR, true);
        $this->registerHandler(PlayerJoinEvent::class, "onPlayerJoin", EventPriority::MONITOR, true);
    }

    /**
     * Registers an event.
     * Source: https://github.com/PEMapModder/HereAuth/blob/master/src/HereAuth/EventRouter.php
     *
     * @param string $event
     * @param string $method
     * @param int $priority
     * @param bool $ignoreCancelled
     */
    private function registerHandler(string $event, string $method, int $priority, bool $ignoreCancelled){
        assert(is_callable([$this, $method]), "Attempt to register nonexistent event handler " . static::class . "::$method");
        $this->plugin->getServer()->getPluginManager()->registerEvent($event, $this, $priority, new MethodEventExecutor($method), $this->plugin, $ignoreCancelled);
    }

    public function onPlayerJoin(PlayerJoinEvent $event){
        $this->plugin->addPlayer($event->getPlayer());
    }

    public function onPlayerInteract(PlayerInteractEvent $event){
        $action = $event->getAction();
        $itemHandler = $this->plugin->getHandlerManager()->getItemHandler();

        if($action === PlayerInteractEvent::RIGHT_CLICK_AIR){
            $itemHandler->handleReadiness($event->getPlayer(), $event->getItem());
        }elseif($action === PlayerInteractEvent::LEFT_CLICK_BLOCK){
            $skills = $this->plugin->getPlayer($event->getPlayer()->getId())->getSkillManager();
            if($itemHandler->isReady($event->getPlayer(), $event->getItem())){
                $skills->handleReadyItemInteract($event);
            }else{
                $skills->handleUnreadyItemInteract($event);
            }
        }
    }

    public function onBlockBreak(BlockBreakEvent $event){
        $item = $event->getItem();
        $block = $event->getBlock();

        $skills = $this->plugin->getPlayer($event->getPlayer()->getId())->getSkillManager();
        $skills->getWoodcutting()->handleBlockBreak($event);
    }
}