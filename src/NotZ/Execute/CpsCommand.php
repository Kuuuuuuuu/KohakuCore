<?php

namespace NotZ\Execute;

use NotZ\Core;
use pocketmine\command\{CommandSender, PluginCommand};
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\Player;

class CpsCommand extends PluginCommand {

    private $plugin;

    public function __construct(Core $plugin) {
        parent::__construct("cps", $plugin);
        $this->setDescription("CpsCounter On/Off");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!isset($args[0])) {
                $n = $sender->getName();
                if (Core::getInstance()->isInCpscounter($n)) {
                    Core::getInstance()->cpscounter($n);
                    $sender->sendMessage(Core::getInstance()->getPrefixCore() . "§6CpsCounter §aEnable!");
                } else {
                    Core::getInstance()->cpscounter($n);
                    $sender->sendMessage(Core::getInstance()->getPrefixCore() . "§6CpsCounter §cDisable!");
                }
            } else {
                if (Server::getInstance()->getPlayer($args[0]) !== null) {
                    $target = Server::getInstance()->getPlayer($args[0])->getName();
                    if (Core::getInstance()->isInCpsCounter($target)) {
                        Core::getInstance()->cpscounter($target);
                        $sender->sendMessage(Core::getInstance()->getPrefixCore() . "§aCpsCounter enabled for $target");
                    } else {
                        Core::getInstance()->cpscounter($target);
                        $sender->sendMessage(Core::getInstance()->getPrefixCore() . "§cCpsCounter disabled for $target");
                    }
                } else $sender->sendMessage("§cUser: §f$args[0] §cis not currently online");
            }
        return true;
    }
}