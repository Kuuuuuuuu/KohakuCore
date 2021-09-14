<?php

namespace NotZ\Task;

use pocketmine\scheduler\Task;
use pocketmine\server;
use pocketmine\Player;
use NotZ\Core;

class TimeTask extends Task {

    public function onRun(int $currentTick){
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
             $player->getLevel()->setTime(6000);
             $player->getLevel()->stopTime();
             $player->setFood(20);
        }
    }
}