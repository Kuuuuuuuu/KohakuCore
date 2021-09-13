<?php

namespace NotZ\Execute;

use pocketmine\Player;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\item\Item;
use pocketmine\command\{CommandSender, PluginCommand};


use NotZ\Core;

class HubCommand extends PluginCommand
{
	
	private $plugin;
	
	public function __construct(Core $plugin)
	{
		parent::__construct("hub", $plugin);
		$this->plugin = $plugin;
		$this->setDescription("Back to Spawn");
	}
	
	public function execute(CommandSender $d, $label, array $args)
	{
		Enchantment::registerEnchantment(new Enchantment(100, "", 0, 0, 0, 1));
        $enchantment = Enchantment::getEnchantment(100);
        $this->enchInst = new EnchantmentInstance($enchantment, 1);
		if($d instanceof Player){
			$d->getInventory()->clearAll();
		    $d->getArmorInventory()->clearAll();
		    $d->removeAllEffects();
		    $d->setGamemode(2);
		    $d->setScale(1);
		    $d->setAllowFlight(false);
		    $d->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
		    $d->sendMessage(Core::getInstance()->getPrefixCore() . "§bWelcome to Spawn§e ".$d->getName());
			Core::$itemtask->getItem($d);
		}
	}
}

