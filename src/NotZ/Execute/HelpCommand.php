<?php

namespace NotZ\Execute;

use pocketmine\Player;
use pocketmine\Server;
use NotZ\Core;
use pocketmine\command\{CommandSender, PluginCommand};

class HelpCommand extends PluginCommand {
	
	private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("help", $plugin);
        $this->plugin = $plugin;
    }
      
      public function execute(CommandSender $d, $label, array $args) {
      	$d->sendMessage("§bZelda §aHelp Command\n§f>> §b/Core - §aZeldaCore Command\n§f>> §b/tps - §aCheck Tick Per Sec Server\n§f>> §b/pinfo - §aCheck Your Device Imformation");
       }
} 