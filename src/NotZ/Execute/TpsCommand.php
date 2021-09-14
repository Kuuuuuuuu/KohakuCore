<?php

namespace NotZ\Execute;

use NotZ\Core;
use pocketmine\command\{CommandSender, PluginCommand};
use pocketmine\utils\TextFormat;
use pocketmine\Server;

class TpsCommand extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("tps", $plugin);
        $this->setDescription("View Server TPS");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        $tpsColor = TextFormat::GREEN;
        $server = Server::getInstance();
        if ($server->getTicksPerSecond() < 17) {
            $tpsColor = TextFormat::YELLOW;
        } elseif ($server->getTicksPerSecond() < 12) {
            $tpsColor = TextFormat::RED;
        }

        $sender->sendMessage(Core::getInstance()->getPrefixCore() . "§eServer Performance");
        $sender->sendMessage("\n");
        $sender->sendMessage("§l§a» §r§fCurrent TPS: {$tpsColor}{$server->getTicksPerSecond()} ({$server->getTickUsage()}%)");
        $sender->sendMessage("§l§a» §r§fAverage TPS: {$tpsColor}{$server->getTicksPerSecondAverage()} ({$server->getTickUsageAverage()}%)");
        return true;
    }
}