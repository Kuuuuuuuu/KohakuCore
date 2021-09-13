<?php

namespace NotZ\Execute;

use NotZ\Core;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\Player;

class HologramCommand extends PluginCommand{

    private $plugin;

    public function __construct(Core $plugin)
    {
        parent::__construct("hologram", $plugin);
        $this->setAliases(["hologram"]);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player)return $sender->sendMessage("§cYou can only do this in game.!");
        if(!$sender->hasPermission("staff.leaderboard"))return $sender->sendMessage("§bZelda §f>> §cYou do not have permission to do this!");
        if(!isset($args[0]))return $sender->sendMessage("/hologram (kill/death/sumo)");
        if(strtolower($args[0]) == "kill"){
        $position = new Position($sender->x, $sender->y+1.5, $sender->z, $sender->level);
        $nbt = Entity::createBaseNBT($position, null, 1.0, 1.0);
        $leaderboard = Entity::createEntity("Kill", $sender->level, $nbt);
        $leaderboard->spawnToAll();
        }
        if(strtolower($args[0]) == "death"){
        $position = new Position($sender->x, $sender->y+1.5, $sender->z, $sender->level);
        $nbt = Entity::createBaseNBT($position, null, 1.0, 1.0);
        $leaderboard = Entity::createEntity("DeathNew", $sender->level, $nbt);
        $leaderboard->spawnToAll();
        }
        if(strtolower($args[0]) == "sumo"){
        $position = new Position($sender->x, $sender->y+1.5, $sender->z, $sender->level);
        $nbt = Entity::createBaseNBT($position, null, 1.0, 1.0);
        $leaderboard = Entity::createEntity("SumoWin", $sender->level, $nbt);
        $leaderboard->spawnToAll();
        }
        return true;
    }
}