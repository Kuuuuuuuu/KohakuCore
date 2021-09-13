<?php

namespace NotZ\Execute;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use NotZ\Core;

class TcheckCommand  extends PluginCommand {
	
	private $plugin;
	public function __construct(Core $plugin){
		parent::__construct("tcheck", $plugin);
		$this->plugin=$plugin;
		$this->setDescription("UnBan Players");
	}
	
	public function execute(CommandSender $player, string $commandLabel, array $args){
		if($player->isOp()){
			 Core::$ban->openTcheckUI($player);
			  }else{
			 $player->sendMessage(Core::getInstance()->getPrefixCore() . "§cYou cannot execute this command.");
		}
	}
}