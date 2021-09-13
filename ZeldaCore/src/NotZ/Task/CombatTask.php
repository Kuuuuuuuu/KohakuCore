<?php

namespace NotZ\Task;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use NotZ\Core;
use pocketmine\Server;

class CombatTask extends Task {

	public function onRun(int $currentTick) {
		foreach(Core::getInstance()->taggedPlayers as $name => $time) {
			$time--;
			if($time <= 0) {
				Core::getInstance()->setTagged($name, false);
				$player = Server::getInstance()->getPlayerExact($name);
				if($player instanceof Player) {
					$player->sendMessage(Core::getInstance()->getPrefixCore() . "§r§aYou are no longer in combat!");
				}
				return;
			}
			Core::getInstance()->taggedPlayers[$name]--;
		}
	}

}