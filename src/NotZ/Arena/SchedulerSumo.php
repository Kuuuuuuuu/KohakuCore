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
                    $this->plugin->broadcastMessage("§b" . Time::calculateTime($this->startTime) . "", Sumo::MSG_POPUP);
                    $this->startTime--;
                    if($this->startTime == 0) {
                        $this->plugin->startGame();
                        foreach ($this->plugin->players as $player) {
                            $this->plugin->level->addSound(new AnvilUseSound($player->asVector3()));
                        }
                    }
                    else {
                        foreach ($this->plugin->players as $player) {
                            $this->plugin->level->addSound(new ClickSound($player->asVector3()));
                        }
                    }
                }
                else {
                    $this->plugin->broadcastMessage("§bWaiting for second player!", Sumo::MSG_POPUP);
                    $this->startTime = 3;
                }
                break;
            case Sumo::PHASE_GAME:
                $this->plugin->broadcastMessage("§b" . Time::calculateTime($this->gameTime) . "", Sumo::MSG_POPUP);
                if($this->plugin->checkEnd()) $this->plugin->startRestart();
                $this->gameTime--;
                break;
            case Sumo::PHASE_RESTART:
                $this->restartTime--;

                switch ($this->restartTime) {
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
        $this->startTime = 3;
        $this->gameTime = 10 * 60;
        $this->restartTime = 1;
    }
}
