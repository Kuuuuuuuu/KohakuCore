<?php


declare(strict_types=1);

namespace NotZ\Task;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use NotZ\utils\BossBar\BossBar;
use NotZ\Core;

class RestartTask extends Task{

	public function onRun(int $tick) : void{
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
		   Core::getInstance()->restartsec--;
		   $restartTime = Core::getInstance()->restartsec;
		   switch($restartTime){
			  case 120:
				Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . TextFormat::GREEN . "Server will restart in" . TextFormat::YELLOW . " 2 minutes");
				return;
			  case 60:
				Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . TextFormat::GREEN . "Server will restart in" . TextFormat::YELLOW . " 1 minute");
				return;
			  case 30:
				Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . TextFormat::GREEN . "Server will restart in" . TextFormat::YELLOW . " 30 seconds");
				return;
			  case 10:
				Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . TextFormat::GREEN . "Server will restart in" . TextFormat::YELLOW . " 10 seconds");
				return;
			  case 5:
				Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . TextFormat::GREEN . "Server will restart in" . TextFormat::YELLOW . " 5 seconds");
				return;
			  case 4:
				Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . TextFormat::GREEN . "Server will restart in" . TextFormat::YELLOW . " 4 seconds");
				return;
			  case 3:
				Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . TextFormat::GREEN . "Server will restart in" . TextFormat::YELLOW . " 3 seconds");
				return;
			  case 2:
				Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . TextFormat::GREEN . "Server will restart in" . TextFormat::YELLOW . " 2 seconds");
				return;
			  case 1:
				Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . TextFormat::GREEN . "Server will restart in" . TextFormat::YELLOW . " 1 second");
				return;
			  case 0:
				Server::getInstance()->shutdown();
				return;
            }
		}
	}
}