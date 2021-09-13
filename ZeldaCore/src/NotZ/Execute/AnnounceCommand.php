<?php

declare(strict_types=1);

namespace NotZ\Execute;

use pocketmine\Player;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use NotZ\Core;

class AnnounceCommand extends PluginCommand{
	
	private $plugin;
	
	public function __construct(Core $plugin){
		parent::__construct("announce", $plugin);
		$this->plugin=$plugin;
		$this->setDescription("Send an announcment to all players");
		$this->setAliases(["ano"]);
	}
	public function execute(CommandSender $player, string $commandLabel, array $args){
		if(!$player->hasPermission("core.command.announce")){
			$player->sendMessage(Core::getInstance()->getPrefixCore() . "§cYou cannot execute this command.");
			return;
		}
		$message=implode(" ", $args);
		$this->plugin->getServer()->broadcastMessage(Core::getInstance()->getPrefixCore() . "§r§c" . $message);
	}
}