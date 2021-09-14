<?php

namespace NotZ\Task;

use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\server;
use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\Player;
use NotZ\Core;

class AutoClickTask extends Task {

    public function onRun(int $currentTick){
        $maxcps = 15;
        $prefix = "§bGuardian §f>> "; 
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
        	$name = $player->getName();
            $nowcps = Core::$cps->getClicks($player);
             if($nowcps > $maxcps) {
              	$message = ($name . " §eHas " . $nowcps . " §cCPS" . "§f(§a" . $player->getPing($name) . " §ePing §f/ §6" . Core::getInstance()->getPlayerControls($player) . "§f)");
              	Server::getInstance()->broadcastMessage($prefix . $message);
                  $player->kill();
                }
            }
       }
  }