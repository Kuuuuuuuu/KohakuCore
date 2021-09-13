<?php

namespace NotZ\Entity;

use NotZ\Core;
use pocketmine\entity\Monster;
use pocketmine\entity\EntityIds;

class Kill extends Monster{

    const NETWORK_ID = EntityIds::PLAYER;

    public $height = 0.7;
    public $width = 0.4;
    public $gravity = 0;

    public function getName(): string
    {
        return "Kill";
    }

    public function initEntity(): void
    {
    parent::initEntity();
    $this->setImmobile(true);
    $this->setHealth($this->getHealth());
    $this->setNameTagAlwaysVisible(true);
    $this->setScale(0.001);
    }

    public function onUpdate(int $currentTick): bool
    {
    $AllKills = Core::getInstance()->kill->getAll();
    arsort($AllKills);
    $top = 1;
    $nametag = "§f§l» §f§bMost Today Kills Player §f«";
    foreach($AllKills as $name => $value){
    if($top > 5)break;
    $nametag .= "\n§f{$top} §b- §e{$name} §fhas §e{$value} §fkills";
    $top++;
    }
    $this->setNameTag($nametag);
    return parent::onUpdate($currentTick);
    }
}