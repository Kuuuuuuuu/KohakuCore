<?php

namespace NotZ\Task;

use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\utils\{Config, TextFormat as Color};
use pocketmine\entity\{Effect, EffectInstance};

use NotZ\utils\Entities\{JoinCoreFFA, JoinCoreMinigame, JoinCoreKitPVP, JoinCoreBot};
use NotZ\Core;

class EntityTask extends Task {
	
	public function onRun(int $currentTick){
		$level = Server::getInstance()->getDefaultLevel();
		foreach ($level->getEntities() as $entity) {
			if ($entity instanceof JoinCoreFFA) {
				$entity->setNameTag("§ePractice\n§7Click to Play");
				$entity->setNameTagAlwaysVisible(true);
				$entity->setImmobile(true);
				$entity->setScale(1.5);
			}
		}
	}
}