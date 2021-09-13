<?php

namespace NotZ\Execute;

use pocketmine\Server;
use NotZ\Core;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;

class RestartCommand extends PluginCommand {
	
	public function __construct(Core $plugin){
		parent::__construct("restart", $plugin);
		$this->plugin=$plugin;
	}
	
	public function execute(CommandSender $player, string $commandLabel, array $args){
		if($player->isOp()){
			  Core::getInstance()->restartsec = 31;
			  }else{
			  $player->sendMessage(Core::getInstance()->getPrefixCore() . "§cYou cannot execute this command.");
		}
	}
}