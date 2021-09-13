<?php

namespace NotZ\Events;

use pocketmine\{Server, Player};
use pocketmine\command\{Command, CommandSender};
use pocketmine\event\Listener;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\event\server\{DataPacketSendEvent, DataPacketReceiveEvent, QueryRegenerateEvent};
use pocketmine\network\mcpe\protocol\{LevelSoundEventPacket, InventoryTransactionPacket, ContainerClosePacket, LoginPacket};
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\item\{EnderPearl, Item, ItemIds};
use _64FF00\PurePerms\event\PPGroupChangedEvent;
use pocketmine\level\sound\GhastShootSound;
use pocketmine\nbt\tag\{ShortTag, ListTag, FloatTag, DoubleTag, CompoundTag};
use pocketmine\network\mcpe\protocol\{PlaySoundPacket, AddActorPacket};
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\entity\{EffectInstance, Entity, Effect};
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use NotZ\Entity\{AntiBot, EasyBot, MediumBot, HardBot};
use pocketmine\math\Vector3;
use NotZ\utils\Entities\{JoinCoreFFA, JoinCoreMinigame, JoinCoreKitPVP, JoinCoreBot};
use pocketmine\event\block\{BlockBurnEvent, BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\entity\{ProjectileLaunchEvent, EntityDamageEvent, EntityDamageByEntityEvent, EntityLevelChangeEvent};
use pocketmine\event\player\{PlayerPreLoginEvent, PlayerCommandPreprocessEvent, PlayerChatEvent, PlayerMoveEvent, PlayerQuitEvent, PlayerJoinEvent, PlayerLoginEvent, PlayerDeathEvent, PlayerInteractEvent, PlayerRespawnEvent, PlayerDropItemEvent, PlayerExhaustEvent, PlayerItemHeldEvent};
use pocketmine\utils\{TextFormat as Color, Config};
use NotZ\Task\{ScoreboardTask};
use NotZ\utils\BossBar\BossBar;
use NotZ\Sumo\{Sumo};
use NotZ\OneVsOne\{OneVsOne};
use NotZ\utils\ArenaChooser\{BowwarsArenaChooser, SumoArenaChooser, OneVsOneArenaChooser, SkywarsArenaChooser, SpleefArenaChooser, BuildUhcArenaChooser};
use NotZ\utils\Provider\{SkywarsProvider, BowwarsProvider, BuildUhcProvider, SpleefProvider, OneVsOneProvider, SumoProvider};
use NotZ\Core;
use NotZ\utils\FormAPI\SimpleForm;
use NotZ\Arena\Arena;

class EventListener implements Listener {
    public $listener;
    protected $bannedCommands = [];
    private $pearlcd;
    private $cancel_send = true;
    
    public function __construct(Core $listener)  {
        $listener = $this->listener;
        $this->bannedCommands = Core::getInstance()->getConfig()->get('banned-commands', []);
    }
    
    public function getListener() {
        return $this->listener;
    }
    
    /**
   * @param PlayerInteractEvent $event
   * @priority LOW
   * @ignoreCancelled true
   */

   public function onProjectile(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($item->getCustomName() == '§r§bSettings §f| §bClick to use' && $item->getId() == 347) {
		    $player->getLevel()->addSound(new GhastShootSound($player));
		    Core::$arena->SettingsForm($player);
		}
		if ($item->getCustomName() == '§r§aPlay §f| §bClick to use' && $item->getId() == 345) {
		    $player->getLevel()->addSound(new GhastShootSound($player));
			Core::$arena->getForm($player);
		}
    }
    
    /**
    * @param PlayerCommandPreprocessEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */

	public function CommandUsed(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		if(Core::getInstance()->isTagged($player)) {
			$message = $event->getMessage();
			if(strpos($message, "/") === 0) {
				$args = array_map("stripslashes", str_getcsv(substr($message, 1), " "));
				$label = "";
				$target = Server::getInstance()->getCommandMap()->matchCommand($label, $args);
				if($target instanceof Command and in_array(strtolower($label), $this->bannedCommands)) {
					$event->setCancelled();
					$player->sendMessage(Core::getInstance()->getPrefixCore() . "§6You cannot execute this command whilst in combat!");
				}
			}
		}
	}
    
    /**
    * @param PlayerChatEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */
    
    public function onChatSumo(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        if(!isset(Core::getInstance()->settersumo[$player->getName()])) {
            return;
        }
        $event->setCancelled(\true);
        $args = explode(" ", $event->getMessage());
        $arena = Core::getInstance()->settersumo[$player->getName()];
        switch ($args[0]) {
            case "help":
                $player->sendMessage("§aSumo setup help (1/1):\n".
                "§7help : Displays list of available setup commands\n" .
                "§7level : Set arena level\n".
                "§7setspawn : Set arena spawns\n".
                "§7joinsign : Set arena joinsign\n".
                "§7enable : Enable the arena");
                break;
            case "level":
                if(!isset($args[1])) {
                    $player->sendMessage("§cUsage: §7level <levelName>");
                    break;
                }
                if(!Server::getInstance()->isLevelGenerated($args[1])) {
                    $player->sendMessage("§cLevel $args[1] does not found!");
                    break;
                }
                $player->sendMessage("§aArena level updated to $args[1]!");
                $arena->data["level"] = $args[1];
                break;
            case "setspawn":
                if(!isset($args[1])) {
                    $player->sendMessage("§cUsage: §7setspawn <int: spawn>");
                    break;
                }
                if(!is_numeric($args[1])) {
                    $player->sendMessage("§cType number!");
                    break;
                }
                if((int)$args[1] > $arena->data["slots"]) {
                    $player->sendMessage("§cThere are only {$arena->data["slots"]} slots!");
                    break;
                }
                $arena->data["spawns"]["spawn-{$args[1]}"] = (new Vector3($player->getX(), $player->getY(), $player->getZ()))->__toString();
                $player->sendMessage("§bZelda §f>> Spawn $args[1] set to X: " . (string)round($player->getX()) . " Y: " . (string)round($player->getY()) . " Z: " . (string)round($player->getZ()));
                break;
            case "enable":
                if(!$arena->setup) {
                    $player->sendMessage("§6Arena is already enabled!");
                    break;
                }
                if(!$arena->enable()) {
                    $player->sendMessage("§cCould not load arena, there are missing information!");
                    break;
                }
                $player->sendMessage("§aArena enabled!");
                break;
            case "done":
                $player->sendMessage("§aYou are successfully leaved setup mode!");
                unset(Core::getInstance()->settersumo[$player->getName()]);
                if(isset(Core::getInstance()->setupDatasumo[$player->getName()])) {
                    unset(Core::getInstance()->setupDatasumo[$player->getName()]);
                }
                break;
            default:
                $player->sendMessage("§6You are in setup mode.\n".
                    "§7- use §lhelp §r§7to display available commands\n"  .
                    "§7- or §ldone §r§7to leave setup mode");
                break;
        }
    }
	
	/**
    * @param EntityDamageEvent $event
    * @priority LOW
    * @ignoreCancelled true
 */
	
	public function onDamage(EntityDamageEvent $event) {
		$entity = $event->getEntity();
	     if ($entity instanceof Player) {
	        $player = $entity;
	         if ($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getFistArena()) || $player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getResistanceArena())) {
                  if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                       $event->setCancelled(true);
                }
            }
        }
    if($event instanceof EntityDamageByEntityEvent and $entity instanceof Player){
        $damager = $event->getDamager();
        if($damager instanceof Player){
			$dis = $damager->distance($entity);
			$name = $damager->getName();
			if(!$damager->getInventory()->getItemInHand()->getId() == Item::BOW) {
				if(!$damager->getInventory()->getItemInHand()->getId() == Item::SNOWBALL) {
					if(!$damager->getInventory()->getItemInHand()->getId() == Item::EGG) {
			            if($dis > 6.2){
				            $message = ("§bGuardian §f>> " . "§c" . $name . " §eHas " . $dis . " §cDistance" . "§f(§a" . $damager->getPing($name) . " §ePing §f/ §6". Core::getInstance()->getPlayerControls($damager) . "§f)");
                            Server::getInstance()->broadcastMessage($message);
                            $damager->kill();
                        }
                     }
                  }
               }
			}
	    }
		if($event instanceof EntityDamageByEntityEvent) {
			$victim = $event->getEntity();
			$attacker = $event->getDamager();
			if($victim instanceof Player and $attacker instanceof Player) {
				foreach([$victim, $attacker] as $p) {
					if(!Core::getInstance()->isTagged($p)) {
						$p->sendMessage(Core::getInstance()->getPrefixCore() . "§eYou are now in combat");
					}
					Core::getInstance()->setTagged($p, true, 10);
				}
			}
		}
	}
	
	/**
    * @param DataPacketReceiveEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */

    public function PacketReceived(DataPacketReceiveEvent $event) {
        $player = $event->getPlayer();
        $packet = $event->getPacket();
        if ($packet instanceof LevelSoundEventPacket and $packet->sound == LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE or $packet instanceof InventoryTransactionPacket and $packet->trData instanceof UseItemOnEntityTransactionData) {
            Core::$cps->addClick($player);
            if (!isset(Core::getInstance()->Cpscounter[$player->getName()])) {
                 $player->sendTip("§bCPS: §f". Core::$cps->getClicks($player));
               }
          }
          if($event->getPacket() instanceof ContainerClosePacket){
			$this->cancel_send = false;
			$event->getPlayer()->sendDataPacket($event->getPacket(), false, true);
			$this->cancel_send = true;
		 }
         if ($packet instanceof LoginPacket)  {
         	if($packet->clientData["CurrentInputMode"]!==null and $packet->clientData["DeviceOS"]!==null and $packet->clientData["DeviceModel"]!==null){
			     Core::getInstance()->controls[$packet->username ?? "Unknown"]=$packet->clientData["CurrentInputMode"];
		         Core::getInstance()->os[$packet->username ?? "Unknown"]=$packet->clientData["DeviceOS"];
		         Core::getInstance()->device[$packet->username ?? "Unknown"]=$packet->clientData["DeviceModel"];
             }
             $osf= "Normal";
             $players = $event->getPacket()->username;
             Core::getInstance()->fakeOs[strtolower($players)] = $osf;
             $deviceOS = (int)$packet->clientData["DeviceOS"];
             $deviceModel = (string)$packet->clientData["DeviceModel"];
             if ($deviceOS !== 1)  {
                  return;
             }
            $name = explode(" ", $deviceModel);
             if (!isset($name[0])) {
                  return;
              }
             $check = $name[0];
             $check = strtoupper($check);
              if ($check !== $name[0]) {
                  $players = $event->getPacket()->username;
                  Server::getInstance()->broadcastMessage(Core::getInstance()->getPrefixCore() . "§e" . $players . " §cUsing §aToolbox. Please Avoid that Player!");
                  $oss = "Toolbox";
                  $players = $event->getPacket()->username;
                  Core::getInstance()->fakeOs[strtolower($players)] = $oss;
                  }
            }
       }
       
    /**
    * @param PlayerPreLoginEvent $event
    * @priority LOW
    * @ignoreCancelled true
 */
      
   public function onPlayerLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$banplayer = $player->getName();
		foreach(Server::getInstance()->getOnlinePlayers() as $p){
			if($p !== $player and strtolower($player->getName()) === strtolower($p->getName())){
					$event->setCancelled(true);
					$player->kick("§bGuardian §f>> §cYou Already Logged in", false);
			 }
		}
		$banInfo = Core::getInstance()->db->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);
		if (!empty($array)) {
			$banTime = $array['banTime'];
			$reason = $array['reason'];
			$staff = $array['staff'];
			$now = time();
			if($banTime > $now){
				$remainingTime = $banTime - $now;
				$day = floor($remainingTime / 86400);
				$hourSeconds = $remainingTime % 86400;
				$hour = floor($hourSeconds / 3600);
				$minuteSec = $hourSeconds % 3600;
				$minute = floor($minuteSec / 60);
				$remainingSec = $minuteSec % 60;
				$second = ceil($remainingSec);
				$player->kick(str_replace(["{day}", "{hour}", "{minute}", "{second}", "{reason}", "{staff}"], [$day, $hour, $minute, $second, $reason, $staff], Core::getInstance()->message["LoginBanMessage"]), false);
			} else {
				Core::getInstance()->db->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
			}
		}
	}
	
	/**
    * @param DataPacketSendEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */
	
    public function onDataPacketSend(DataPacketSendEvent $event) : void{
		if($this->cancel_send && $event->getPacket() instanceof ContainerClosePacket){
			$event->setCancelled();
		}
	}
	
	/**
    * @param PlayerJoinEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */

    public function PlayerJoin(PlayerJoinEvent $event) {
    	    $bar = new BossBar;
            $player = $event->getPlayer();
            $name = $player->getName();
            Core::$cps->initPlayerClickData($player);
            $event->setJoinMessage("§f[§a+§f]§e " . $name);
            $player->sendMessage(Core::getInstance()->getPrefixCore() . "§eLoading Player Data");
            Core::getinstance()->getScheduler()->scheduleRepeatingTask(new ScoreboardTask(Core::getInstance(), $player), 60);
            $player->setGamemode(Player::ADVENTURE);
            $player->getInventory()->clearAll();
            Core::$utils->Lightning($player);
            $player->removeAllEffects();
            $player->getArmorInventory()->clearAll();
            $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
            Core::$itemtask->getItem($player);
            $bar->setTitle("§f» §bZelda §fNetwork §f«");
            $bar->setSubtitle("§f");
            $bar->setPercentage(100);
            $bar->addPlayer($player);
            $Chat = Server::getInstance()->getPluginManager()->getPlugin("PureChat");
            if($Chat->getsuffix($player) === null) {
            	$Chat->setsuffix("§aUnknown", $player);
             }
         }
     
     /**
     * @param PlayerQuitEvent $event
     * @priority LOW
     * @ignoreCancelled true
     */

    public function PlayerQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        Core::$cps->removePlayerClickData($player);
        $event->setQuitMessage("§f[§c-§f]§e " . $name);
        $player->setGamemode(Player::ADVENTURE);
        Core::$utils->Lightning($player);
        $player->removeAllEffects();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
        if(Core::getInstance()->isTagged($player)) {
    	    $player->kill();
          }
   }
   
   /**
    * @param PlayerMoveEvent $event
    * @priority LOW
    * @ignoreCancelled true
    */
   
   public function onMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        if ($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getFistArena()) || $player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getResistanceArena())) {
              if ($event->getPlayer()->getY() < 20) {
               	$player->kill();
             }
        }
   }
   
   /**
    * @param BlockBreakEvent $ev
    * @priority LOW
    * @ignoreCancelled true
    */
		
    public function onBreak(BlockBreakEvent $ev)  {
        $player = $ev->getPlayer();
        if(!$player->isOp()){
             if ($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getFistArena()) || $player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getResistanceArena())) {
                  $ev->setCancelled(true);
            }
        }
    }
    
    /**
    * @param BlockPlaceEvent $ev
    * @priority LOW
    * @ignoreCancelled true
    */
    
    public function onPlace(BlockPlaceEvent $ev) {
        $player = $ev->getPlayer();
         if(!$player->isOp()){
             if ($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getFistArena()) || $player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getResistanceArena())) {
                  $ev->setCancelled(true);
            }
        }
    }
    
    /**
    * @param PlayerDropItemEvent $ev
    * @priority LOW
    * @ignoreCancelled true
    */
    
    public function onDrop(PlayerDropItemEvent $ev)
    {
        $player = $ev->getPlayer();
         if ($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getFistArena()) || $player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getResistanceArena())) {
            $ev->setCancelled(true);
        }
    }
    
    /**
    * @param EntityLevelChangeEvent $ev
    * @priority LOW
    * @ignoreCancelled true
    */
            
    public function onChange(EntityLevelChangeEvent $ev) {
        $player = $ev->getEntity();
        if ($player instanceof Player) {
        	if(!$player->isOp()){
                if ($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getFistArena()) || $player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getResistanceArena())) {
                     $player->setMaxHealth(20);
                     $player->setHealth(20);
                     $player->setGamemode(Player::ADVENTURE);
                  }
             }
        }
   }
   
   /**
    * @param PlayerDeathEvent $ev
    * @priority LOW
    * @ignoreCancelled true
    */

    public function onDeath(PlayerDeathEvent $ev)   {
        $player = $ev->getPlayer();
        $name = $player->getName();
        $ev->setDeathMessage("");
        if ($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getFistArena()) || $player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getResistanceArena())) {
            $ev->setDrops([]);
            $ev->getPlayer()->setGamemode(Player::ADVENTURE);
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->removeAllEffects();
            Core::getInstance()->setTagged($name, false);
            Core::$utils->Lightning($player);
            Core::getInstance()->addDeath($player);
            Core::getInstance()->death->set($name, Core::getInstance()->death->get($name)+1);
            $cause = $ev->getEntity()->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
                $damager = $cause->getDamager();
                if ($damager instanceof Player) {
                	$dname = $damager->getName();
                	Core::getInstance()->addKill($damager);
                    Core::getInstance()->kill->set($dname, Core::getInstance()->kill->get($dname)+1);
                    Core::getInstance()->handleStreak($damager, $player);
                    foreach ($damager->getLevel()->getPlayers() as $players) {
                        $players->sendMessage(Core::getInstance()->getPrefixCore() . Color::RED . $player->getName() . Color::GRAY . " was killed by " . Color::GREEN . $damager->getName());
                    }
                    $damager->setHealth($damager->getMaxHealth());
                    Core::getInstance()->setTagged($dname, false);
                }
            }
        }
    }
    
    /**
    * @param PlayerRespawnEvent $ev
    * @priority LOW
    * @ignoreCancelled true
    */
    
    public function onRespawn(PlayerRespawnEvent $ev)
    {
        $player = $ev->getPlayer();
        $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
        $player->setGamemode(Player::ADVENTURE);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->removeAllEffects();
        Core::$itemtask->getItem($player);
        $player->setScale(1);
        $player->setMaxHealth(20);
        $player->setHealth(20);
    }
    
    /**
    * @param EntityDamageByEntityEvent $ev
    * @priority LOW
    * @ignoreCancelled true
    */
    
   public function onFunction(EntityDamageByEntityEvent $ev){
		$npc = $ev->getEntity();
		$player = $ev->getDamager();
		if($npc instanceof JoinCoreFFA && $player instanceof Player){
			$ev->setCancelled(true);
			Core::getArena()->getForm($player);
		}
	}
}


?>
