<?php

namespace NotZ\Events;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use function array_unshift;
use function array_pop;
use function microtime;
use function round;
use function count;
use function array_filter;

class CpsCounter {

    private const ARRAY_MAX_SIZE = 100;
    private $clicksData = [];

    public function initPlayerClickData(Player $p) : void{
        $this->clicksData[$p->getLowerCaseName()] = [];
    }

    public function addClick(Player $p) : void{
        array_unshift($this->clicksData[$p->getLowerCaseName()], microtime(true));
        if(count($this->clicksData[$p->getLowerCaseName()]) >= self::ARRAY_MAX_SIZE){
            array_pop($this->clicksData[$p->getLowerCaseName()]);
        }
    }

    public function getClicks(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1) : float{
        if(!isset($this->clicksData[$player->getLowerCaseName()]) || empty($this->clicksData[$player->getLowerCaseName()])){
            return 0;
        }
        $ct = microtime(true);
        return round(count(array_filter($this->clicksData[$player->getLowerCaseName()], static function(float $t) use ($deltaTime, $ct) : bool{
            return ($ct - $t) <= $deltaTime;
        })) / $deltaTime, $roundPrecision);
    }

    public function removePlayerClickData(Player $p) : void{
        unset($this->clicksData[$p->getLowerCaseName()]);
    }
	
}
