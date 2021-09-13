<?php

declare(strict_types=1);

namespace NotZ\Arena;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\ClickSound;
use pocketmine\scheduler\Task;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\item\Item;
use pocketmine\tile\Sign;
use NotZ\utils\math\Time;
use NotZ\utils\math\Vector3;
use NotZ\Core;

class SchedulerSumo extends Task {

    protected $plugin;
    public $restartData = [];

    public function __construct(Sumo $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick) {
        if($this->plugin->setup) return;
          switch ($this->plugin->phase) {
            case Sumo::PHASE_LOBBY:
                if(count($this->plugin->players) >= 2) {
                    $this->plugin->broadcastMessage("§b" . Time::calculateTime(Core::getInstance()->startTime) . "", Sumo::MSG_POPUP);
                    Core::getInstance()->startTime--;
                    if(Core::getInstance()->startTime == 0) {
                        $this->plugin->startGame();
                        foreach ($this->plugin->players as $player) {
                            $this->plugin->level->addSound(new AnvilUseSound($player->asVector3()));
                        }
                    } else {
                        foreach ($this->plugin->players as $player) {
                            $this->plugin->level->addSound(new ClickSound($player->asVector3()));
                        }
                    }
                } else {
                	$this->plugin->broadcastMessage("§bPlease Wait", Sumo::MSG_POPUP);
                }
                break;
            case Sumo::PHASE_GAME:
                if($this->plugin->checkEnd()) $this->plugin->startRestart();
                Core::getInstance()->gameTime--;
                break;
            case Sumo::PHASE_RESTART:
                Core::getInstance()->restartTime = 0;
                switch (Core::getInstance()->restartTime) {
                    case 0:
                        foreach ($this->plugin->players as $player) {
                            $player->teleport($this->plugin->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
                            $player->getInventory()->clearAll();
                            $player->getArmorInventory()->clearAll();
                            $player->getCursorInventory()->clearAll();
                            $player->setFood(20);
                            $player->setHealth(20);
                            Core::$itemtask->getItem($player);
                            $player->setGamemode($this->plugin->plugin->getServer()->getDefaultGamemode());
                        }
                        $this->plugin->loadArena(true);
                        $this->reloadTimer();
                        break;
                }
             break;
        }
}

    public function reloadTimer() {
        Core::getInstance()->startTime = 3;
        Core::getInstance()->gameTime = 10 * 60;
        Core::getInstance()->restartTime = 1;
    }
}
