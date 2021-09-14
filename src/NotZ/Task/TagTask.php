<?php

namespace NotZ\Task;

use pocketmine\scheduler\Task;
use NotZ\Core;
use pocketmine\Player;
use pocketmine\Server;

class TagTask extends Task {

	public function onRun(int $tick){
		foreach(Server::getInstance()->getOnlinePlayers() as $players){
		    $player = $players->getPlayer();
            $name = $players->getName();
            $ping = $players->getPing($name);
            $hp = round($players->getHealth(), 1);
            $max_hp = $players->getMaxHealth();
            $line = "\n";
            $tag = "§f[ §b{os} §f| §b{fakeos} §f| §b{device} §f]";
		    $tag = str_replace("&", "§", $tag);
            $tag = str_replace("{name}", $name, $tag);
            $tag = str_replace("{ping}", $ping, $tag);
            $tag = str_replace("{hp}", $hp, $tag);
            $tag = str_replace("{max_hp}", $hp, $tag);
            $tag = str_replace("{line}", $line, $tag);
            $tag = str_replace('{cps}', Core::$cps->getClicks($player), $tag);
		    $device = Core::getInstance()->getPlayerControls($player);
		    $os = Core::getInstance()->getPlayerOs($player);
		    $fakeos = Core::getInstance()->getFakeOs($player);
            $tag = str_replace('{device}', $device, $tag);
		    $tag = str_replace('{os}', $os, $tag);
		    $tag = str_replace('{fakeos}', $fakeos, $tag);
		    #------------PVP------------#
            $tagpvp = "§f[ §a{hp}§a HP§f | §a{ping}§b ms §f| §aCPS §f: §b{cps} §f]";
		    $tagpvp = str_replace("&", "§", $tagpvp);
            $tagpvp = str_replace("{name}", $name, $tagpvp);
            $tagpvp = str_replace("{ping}", $ping, $tagpvp);
            $tagpvp = str_replace("{hp}", $hp, $tagpvp);
            $tagpvp = str_replace("{max_hp}", $hp, $tagpvp);
            $tagpvp = str_replace("{line}", $line, $tagpvp);
            $tagpvp = str_replace('{cps}', Core::$cps->getClicks($players), $tagpvp);
            $tagpvp = str_replace('{device}', $device, $tagpvp);
		    $tagpvp = str_replace('{os}', $os, $tagpvp);
		    $tagpvp = str_replace('{fakeos}', $fakeos, $tagpvp);
		    if(!Core::getInstance()->isTagged($players)) {
		       $players->setScoreTag($tag);
		    }
		    if(Core::getInstance()->isTagged($players)) {
			   $players->setScoreTag($tagpvp);
		    }
		}
	}
}