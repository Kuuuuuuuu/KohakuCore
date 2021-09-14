<?php

namespace NotZ\Execute;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use NotZ\Core;

class TbanCommand  extends PluginCommand {
	
	private $plugin;
	public function __construct(Core $plugin){
		parent::__construct("tban", $plugin);
		$this->plugin=$plugin;
		$this->setDescription("Ban Players");
	}
	
	public function execute(CommandSender $player, string $commandLabel, array $args){
		if($player->isOp()){
			 Core::$ban->openPlayerListUI($player);
			  }else{
			 $player->sendMessage(Core::getInstance()->getPrefixCore() . "§cYou cannot execute this command.");
		}
	}
}