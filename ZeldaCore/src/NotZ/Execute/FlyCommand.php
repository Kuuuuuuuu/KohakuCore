<?php

declare(strict_types=1);

namespace NotZ\Execute;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use NotZ\Core;

class FlyCommand extends PluginCommand{
	
	private $plugin;
	
	public function __construct(Core $plugin){
		parent::__construct("fly", $plugin);
		$this->plugin=$plugin;
		$this->setDescription("Enable or disable fly for a player");
	}
	public function execute(CommandSender $player, string $commandLabel, array $args){
		if(!$player->hasPermission("core.command.fly")){
			 $player->sendMessage(Core::getInstance()->getPrefixCore() . "§cYou cannot execute this command.");
			 return;
		}
		$level=$player->getLevel()->getName();
		if($level !="world"){
			$player->sendMessage(Core::getInstance()->getPrefixCore() . "§cYou cannot enable fly here.");
			return;
		}
		if($player->getAllowFlight()===false){
			$player->setFlying(true);
			$player->setAllowFlight(true);
			$player->sendMessage(Core::getInstance()->getPrefixCore() . "§aYou enabled flight.");
		}else{
			$player->setFlying(false);
			$player->setAllowFlight(false);
			$player->sendMessage(Core::getInstance()->getPrefixCore() . "§aYou disabled flight.");
		}
	}
}