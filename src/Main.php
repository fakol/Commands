<?php

declare(strict_types=1);

namespace niko\Commands;

use pocketmine\player\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\entity\EntityDamageEvent;

class Main extends PluginBase implements Listener{
    public function onEnable() : void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function onDropItem(PlayerDropItemEvent $event) {
        $player = $event->getPlayer();
        if ($player->isCreative()){
	        $player->sendTip("§l§cВы не можете выкидывать вещи в креативе.");
            $event->cancel();
        }
    }
    public function onAttack(EntityDamageEvent $event) {
        if ($event instanceof EntityDamageByEntityEvent){
            $player = $event->getDamager();
            if ($player instanceof Player){
                if ($player->isCreative()) {
					$player->sendTip("§l§cВы не можете бить в креативе.");
                    $event->cancel();
                }
            }
        }
    }
}
