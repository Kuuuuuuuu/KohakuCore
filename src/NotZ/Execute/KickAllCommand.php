<?php

declare(strict_types=1);

namespace NotZ\Execute;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use NotZ\Core;

class KickAllCommand extends PluginCommand{
	
	private $plugin;
	
	public function __construct(Core $plugin){
		parent::__construct("kickall", $plugin);
		$this->plugin=$plugin;
		$this->setDescription("Kick all players on the server");
		$this->setPermission("core.command.kickall");
	}
	public function execute(CommandSender $player, string $commandLabel, array $args){
		if(!$player->isOp()){
			$player->sendMessage(Core::getInstance()->getPrefixCore() . "§cYou cannot execute this command.");
			return;
        }
        foreach($this->plugin->getServer()->getOnlinePlayers() as $online){
            if(!$online->isOp()){
                $online->kick("§aEveryone has been kicked, you may rejoin soon.", false);
            }
        }
	}
}