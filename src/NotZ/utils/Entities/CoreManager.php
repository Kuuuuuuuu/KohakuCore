<?php

namespace NotZ\utils\Entities;

use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\Player;

use NotZ\Core;

class CoreManager {
	
	public function setJoinEntityFFA(Player $player) {
		$player->saveNBT();
		$nbt = Entity::createBaseNBT(new Vector3((float)$player->getX(), (float)$player->getY(), (float)$player->getZ()));
		$nbt->setTag(clone $player->namedtag->getCompoundTag("Skin"));
		$human = new JoinCoreFFA($player->getLevel(), $nbt);
		$human->setNameTagVisible(true);
		$human->setNameTagAlwaysVisible(true);
		$human->yaw = $player->getYaw();
		$human->pitch = $player->getPitch();
		$human->spawnToAll();
	}
	
	 public function setJoinEntityKitpvp(Player $player) {
		$player->saveNBT();
		$nbt = Entity::createBaseNBT(new Vector3((float)$player->getX(), (float)$player->getY(), (float)$player->getZ()));
		$nbt->setTag(clone $player->namedtag->getCompoundTag("Skin"));
		$human = new JoinCoreKitPVP($player->getLevel(), $nbt);
		$human->setNameTagVisible(true);
		$human->setNameTagAlwaysVisible(true);
		$human->yaw = $player->getYaw();
		$human->pitch = $player->getPitch();
		$human->spawnToAll();
	}
	
	public function setJoinEntityMinigame(Player $player) {
		$player->saveNBT();
		$nbt = Entity::createBaseNBT(new Vector3((float)$player->getX(), (float)$player->getY(), (float)$player->getZ()));
		$nbt->setTag(clone $player->namedtag->getCompoundTag("Skin"));
		$human = new JoinCoreMinigame($player->getLevel(), $nbt);
		$human->setNameTagVisible(true);
		$human->setNameTagAlwaysVisible(true);
		$human->yaw = $player->getYaw();
		$human->pitch = $player->getPitch();
		$human->spawnToAll();
	}
	
	public function setJoinEntityBot(Player $player) {
		$player->saveNBT();
		$nbt = Entity::createBaseNBT(new Vector3((float)$player->getX(), (float)$player->getY(), (float)$player->getZ()));
		$nbt->setTag(clone $player->namedtag->getCompoundTag("Skin"));
		$human = new JoinCoreBot($player->getLevel(), $nbt);
		$human->setNameTagVisible(true);
		$human->setNameTagAlwaysVisible(true);
		$human->yaw = $player->getYaw();
		$human->pitch = $player->getPitch();
		$human->spawnToAll();
	}
	
}