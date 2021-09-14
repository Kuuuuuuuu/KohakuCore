<?php

namespace NotZ\Execute;

use NotZ\Core;
use pocketmine\command\{CommandSender, PluginCommand};
use pocketmine\utils\TextFormat;
use pocketmine\Server;

class VersionCommand extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("version", $plugin);
        $this->setDescription("Check Version Software Server");
        $this->plugin = $plugin;
    }
    
	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (isset($args[0])){
			$sender->sendMessage("§e>> §bThis server is running ZeldaCore §eImplementing §cPHP 7.4");
		} else {
			$sender->sendMessage("§e>> §bThis server is running ZeldaCore §eImplementing §cPHP 7.4");
		}
	}
}