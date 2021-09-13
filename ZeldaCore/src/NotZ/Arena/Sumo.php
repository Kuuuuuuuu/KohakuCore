<?php

declare(strict_types=1);

namespace NotZ\Arena;

use pocketmine\block\Block;
use pocketmine\event\entity\{EntityDamageEvent, EntityLevelChangeEvent};
use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\event\Listener;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\item\Item;
use pocketmine\event\player\{PlayerDeathEvent, PlayerExhaustEvent, PlayerInteractEvent, PlayerMoveEvent, PlayerQuitEvent, PlayerRespawnEvent};
use pocketmine\level\{Position, Level};
use pocketmine\Player;
use pocketmine\tile\Tile;
use NotZ\utils\math\Vector3;
use NotZ\Core;

class Sumo implements Listener {
   
    const MSG_MESSAGE = 0;
    const MSG_TIP = 1;
    const MSG_POPUP = 2;
    const MSG_TITLE = 3;
    const PHASE_LOBBY = 0;
    const PHASE_GAME = 1;
    const PHASE_RESTART = 2;
    /**Game Const**/
    
    public $plugin;
    public $scheduler;
    public $phase = 0;
    public $data = [];
    public $setup = false;
    public $players = [];
    public $toRespawn = [];
    public $level = null;

    public function __construct(Core $plugin, array $arenaFileData) {
        $this->plugin = $plugin;
        $this->data = $arenaFileData;
        $this->setup = !$this->enable(\false);
        $this->plugin->getScheduler()->scheduleRepeatingTask($this->scheduler = new SchedulerSumo($this), 15);
        if($this->setup) {
            if(empty($this->data)) {
                $this->createBasicData();
            }
        } else {
            $this->loadArena();
        }
    }

    public function joinToArena(Player $player) {
        if(!$this->data["enabled"]) {
            $player->sendMessage("§bZelda §f>>§e The game is in configurating!");
            return;
        }
        if(count($this->players) >= $this->data["slots"]) {
            $player->sendMessage("§bZelda §f>> §eThe game is full!");
            return;
        }
        if($this->inGame($player)) {
            $player->sendMessage("§bZelda §f>>§e You are already in the queue/game!");
            return;
        }
        $selected = false;
        for($lS = 1; $lS <= $this->data["slots"]; $lS++) {
            if(!$selected) {
                if(!isset($this->players[$index = "spawn-{$lS}"])) {
                	$player->teleport(Position::fromObject(Vector3::fromString($this->data["spawns"][$index]), $this->level));
                    $this->players[$index] = $player;
                    $selected = true;
                }
            }
        }
        $this->broadcastMessage("§bZelda §f>> §r§ePlayer {$player->getName()} entered the game! §7[".count($this->players)."/{$this->data["slots"]}]");
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), (99999999*20), (3), (false)));
        $player->setGamemode($player::ADVENTURE);
        $player->setHealth(20);
        $player->setFood(20);
    }

    public function disconnectPlayer(Player $player, string $quitMsg = "", bool $death = \false) {
        switch ($this->phase) {
            case Sumo::PHASE_LOBBY:
                $index = "";
                foreach ($this->players as $i => $p) {
                    if($p->getId() == $player->getId()) {
                        $index = $i;
                    }
                }
                if($index != "") {
                    unset($this->players[$index]);
                }
                break;
            default:
                unset($this->players[$player->getName()]);
                break;
        }
        $player->removeAllEffects();
        $player->setGamemode($this->plugin->getServer()->getDefaultGamemode());
        $player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        Core::$itemtask->getItem($player);
        $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
        if(!$death) {
            $this->broadcastMessage("§bZelda §f>> §r§ePlayer {$player->getName()} Left the game. §7[".count($this->players)."/{$this->data["slots"]}]");
        }

        if($quitMsg != "") {
            $player->sendMessage("§bZelda §f>> §r§e$quitMsg");
        }
    }

    public function startGame() {
        $players = [];
        foreach ($this->players as $player) {
            $players[$player->getName()] = $player;
        }
        $this->players = $players;
        $this->phase = 1;
    }

    public function startRestart() {
        $player = null;
        foreach ($this->players as $p) {
            $player = $p;
        }
        if($player === null || (!$player instanceof Player) || (!$player->isOnline())) {
            $this->phase = self::PHASE_RESTART;
            return;
        }
        $this->plugin->getServer()->broadcastMessage("§bZelda §f>> §r§ePlayer {$player->getName()} won the Sumo!");
        Core::getInstance()->sumo->set($player->getName(), Core::getInstance()->sumo->get($player->getName())+1);
        $this->phase = self::PHASE_RESTART;
    }

    public function inGame(Player $player): bool {
        switch ($this->phase) {
            case self::PHASE_LOBBY:
                $inGame = false;
                foreach ($this->players as $players) {
                    if($players->getId() == $player->getId()) {
                        $inGame = true;
                    }
                }
                return $inGame;
            default:
                return isset($this->players[$player->getName()]);
        }
    }

    public function broadcastMessage(string $message, int $id = 0, string $subMessage = "") {
        foreach ($this->players as $player) {
            switch ($id) {
                case self::MSG_MESSAGE:
                    $player->sendMessage($message);
                    break;
                case self::MSG_TIP:
                    $player->sendTip($message);
                    break;
                case self::MSG_POPUP:
                    $player->sendPopup($message);
                    break;
                case self::MSG_TITLE:
                    $player->addTitle($message, $subMessage);
                    break;
            }
        }
    }

    public function checkEnd(): bool {
        return count($this->players) <= 1;
    }
    
    /**
    * @param PlayerMoveEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */

    public function onMove(PlayerMoveEvent $event) {
        if($this->phase != self::PHASE_LOBBY) return;
        $player = $event->getPlayer();
        if($this->inGame($player)) {
       	  if($player->getPosition()->getY() < 20) {
                  $player->setHealth(20);
                  $player->setFood(20);
                  $player->removeAllEffects();
                 $this->disconnectPlayer($player, "", true);
              }
            $index = null;
            foreach ($this->players as $i => $p) {
                if($p->getId() == $player->getId()) {
                    $index = $i;
                }
            }
            if($event->getPlayer()->asVector3()->distance(Vector3::fromString($this->data["spawns"][$index])) > 1) {
                $player->teleport(Vector3::fromString($this->data["spawns"][$index]));
            }
        }
    }
    
    /**
    * @param PlayerDeathEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */

    public function onDeath(PlayerDeathEvent $event) {
        $player = $event->getPlayer();
        if(!$this->inGame($player)) return;
        $this->disconnectPlayer($event->getPlayer());
        $event->setDeathMessage("");
        $event->setDrops([]);
    }
    
    /**
    * @param PlayerQuitEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */
    
    public function onQuit(PlayerQuitEvent $event) {
        if($this->inGame($event->getPlayer())) {
            $this->disconnectPlayer($event->getPlayer());
        }
    }
    
    /**
    * @param EntityLevelChangeEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */

    public function onLevelChange(EntityLevelChangeEvent $event) {
        $player = $event->getEntity();
        if(!$player instanceof Player) return;
        if($this->inGame($player)) {
            $this->disconnectPlayer($player, "You quit the game.");
        }
    }

    public function loadArena(bool $restart = false) {
        if(!$this->data["enabled"]) {
            $this->plugin->getLogger()->error("Can not load arena: Arena is not enabled!");
            return;
        }
        if(!$restart) {
            $this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
            if(!$this->plugin->getServer()->isLevelLoaded($this->data["level"])) {
                $this->plugin->getServer()->loadLevel($this->data["level"]);
            }
            $this->level = $this->plugin->getServer()->getLevelByName($this->data["level"]);
        } else {
            $this->scheduler->reloadTimer();
        }
        if(!$this->level instanceof Level) $this->level = $this->plugin->getServer()->getLevelByName($this->data["level"]);
        $this->phase = static::PHASE_LOBBY;
        $this->players = [];
    }

    public function enable(bool $loadArena = true): bool {
        if(empty($this->data)) {
            return false;
        }
        if($this->data["level"] == null) {
            return false;
        }
        if(!$this->plugin->getServer()->isLevelGenerated($this->data["level"])) {
            return false;
        }
        else {
            if(!$this->plugin->getServer()->isLevelLoaded($this->data["level"]))
                $this->plugin->getServer()->loadLevel($this->data["level"]);
            $this->level = $this->plugin->getServer()->getLevelByName($this->data["level"]);
        }
        if(!is_int($this->data["slots"])) {
            return false;
        }
        if(!is_array($this->data["spawns"])) {
            return false;
        }
        if(count($this->data["spawns"]) != $this->data["slots"]) {
            return false;
        }
        $this->data["enabled"] = true;
        $this->setup = false;
        if($loadArena) $this->loadArena();
        return true;
    }

    private function createBasicData() {
        $this->data = [
            "level" => null,
            "slots" => 2,
            "spawns" => [],
            "enabled" => false
        ];
    }

    public function __destruct() {
        unset($this->scheduler);
    }
}
