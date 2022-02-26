<?php

declare(strict_types=1);

namespace niko\Commands;

use pocketmine\player\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as TF;
use pocketmine\player\GameMode;
use pocketmine\world\World;

class Main extends PluginBase implements Listener{
    /**
     * @var self
     */
    protected static Main $instance;
    /**
     * Version
     */
    public const VERSION = "1.0.2";

    public function onLoad() : void {
        self::$instance = $this;
        $this->saveResource("config.yml");
        $this->versionCheck($this->getConfig()->get("version") < "0.0.1");
    }

    public function onEnable() : void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("success enable");
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
        $cfg = $this->getConfig();
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . $cfg->get("only-game"));
            return true;
        }
        $player = $this->getServer()->getPlayerByPrefix($sender->getName());
        switch($cmd->getName()) {
            case "gm":
				if (isset($args[0])) {
					switch ($args[0]) {
						case "0":
							$player->setGamemode(GameMode::SURVIVAL());
							$sender->sendMessage(TF::GREEN . $cfg->get("on-survival"));
						break;
						case "1":
							$player->setGamemode(GameMode::CREATIVE());
							$sender->sendMessage(TF::GREEN . $cfg->get("on-creative"));
						break;
						case "2":
							$player->setGamemode(GameMode::ADVENTURE());
							$sender->sendMessage(TF::GREEN . $cfg->get("on-adventure"));
						break;
						case "3":
							$player->setGamemode(GameMode::SPECTATOR());
							$sender->sendMessage(TF::GREEN . $cfg->get("on-spectator"));
						break;
						default:
							$sender->sendMessage(TF::RED . $cfg->get("not-gm") . PHP_EOL . TF::WHITE . $cfg->get("usage"),"§c/gm <0, 1, 2, 3>§e.§r");
						break;
					}
				} else {
					$sender->sendMessage($cfg->get("usage"),"§c/gm <0, 1, 2, 3>§e.§r");
				}
			break;
            case "day":
                if($sender->hasPermission("gm.day")) {
                    foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
                        $world->setTime(World::TIME_DAY);
                    }
                    $sender->sendMessage($this->fts() . TF::GREEN . $cfg->get("time-day"));
                } else {
                    $sender->sendMessage($this->fts() . TF::RED . $cfg->get("not-permission"));
                }
            break;
            case "night":
                if($sender->hasPermission("gm.night")) {
                    foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
                        $world->setTime(World::TIME_NIGHT);
                    }
                    $sender->sendMessage($this->fts() . TF::GREEN . $cfg->get("time-night"));
                } else {
                    $sender->sendMessage($this->fts() . TF::RED . $cfg->get("not-permission"));
                }
            break;
        }
        return true;
    }

    public function fts() {
        $cfg = $this->getConfig();
        $cfg->get("mes-name");
    }

    public function onDropItem(PlayerDropItemEvent $event) {
        $cfg = $this->getConfig();
        $player = $event->getPlayer();
        if ($player->isCreative()){
	        $player->sendTip($cfg->get("not-creative-drop"));
            $event->cancel();
        }
    }

    public function onAttack(EntityDamageEvent $event) {
        $cfg = $this->getConfig();
        if ($event instanceof EntityDamageByEntityEvent){
            $player = $event->getDamager();
            if ($player instanceof Player){
                if ($player->isCreative()) {
					$player->sendTip($cfg->get("not-attack-creative"));
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @param bool $update
     */
    private function versionCheck(bool $update = true)
    {
        if (!$this->getConfig()->exists("version") || $this->getConfig()->get("version") !== self::VERSION) {
            if ($update == true) {
                $this->getLogger()->debug("OUTDATED CONFIG.YML!! You config.yml is outdated! Your config.yml will automatically updated!");
                rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "oldConfig.yml");
                $this->saveResource("config.yml");
                $this->getLogger()->debug("config.yml Updated for version: §b" . (self::VERSION) . "");
            } else {
                $this->getLogger()->warning("Your config.yml is outdated but that's not so bad.");
            }
        }
    }
}

