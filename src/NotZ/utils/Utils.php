<?php

namespace NotZ\utils;

use pocketmine\network\mcpe\protocol\{PlaySoundPacket, AddActorPacket};
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\{Server, Player};
use pocketmine\entity\{Entity};
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use NotZ\Core;

class Utils {
	
    public function Lightning(Player $player) :void {
        $light = new AddActorPacket();
        $light->type = "minecraft:lightning_bolt";
        $light->entityRuntimeId = Entity::$entityCount++;
        $light->metadata = [];
        $light->motion = null;
        $light->yaw = $player->getYaw();
        $light->pitch = $player->getPitch();
        $light->position = new Vector3($player->getX(), $player->getY(), $player->getZ());
        Server::getInstance()->broadcastPacket($player->getLevel()->getPlayers(), $light);
        $block = $player->getLevel()->getBlock($player->getPosition()->floor()->down());
        $particle = new DestroyBlockParticle(new Vector3($player->getX(), $player->getY(), $player->getZ()), $block);
        $player->getLevel()->addParticle($particle);
        $sound = new PlaySoundPacket();
        $sound->soundName = "ambient.weather.lightning.impact";
        $sound->x = $player->getX();
        $sound->y = $player->getY();
        $sound->z = $player->getZ();
        $sound->volume = 1;
        $sound->pitch = 1;
        Server::getInstance()->broadcastPacket($player->getLevel()->getPlayers(), $sound);
    }
}