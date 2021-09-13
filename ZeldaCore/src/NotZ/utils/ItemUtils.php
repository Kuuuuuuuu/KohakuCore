<?php

namespace NotZ\utils;

use pocketmine\item\{Item, Potion};
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\Player;
use NotZ\Core;

class ItemUtils {
	
	public function getItem(Player $player){
		Enchantment::registerEnchantment(new Enchantment(100, "", 0, 0, 0, 1));
        $enchantment = Enchantment::getEnchantment(100);
        $this->enchInst = new EnchantmentInstance($enchantment, 1);
        $item = Item::get(345);
        $item->setCustomName('§r§aPlay §f| §bClick to use');
        $item->addEnchantment($this->enchInst);
        $player->getInventory()->setItem(4, $item, true);
        $item2 = Item::get(347);
        $item2->setCustomName('§r§bSettings §f| §bClick to use');
        $item2->addEnchantment($this->enchInst);
        $player->getInventory()->setItem(8, $item2, true);
	}
}