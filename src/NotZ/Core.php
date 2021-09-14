<?php

namespace NotZ;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\Entity;
use pocketmine\scheduler\Task;
use pocketmine\entity\projectile\Arrow;
use pocketmine\{Player, Server};
use pocketmine\utils\{Config, TextFormat};
use pocketmine\command\{CommandSender, Command};
use NotZ\Events\{EventSetup, CpsCounter, EventListener, ChatListener, TapToArmor};
use NotZ\Execute\{SpleefCommand, BowWarsCommand, SumoCommand, OneVsOneCommand, SkyWarsCommand, BuildUHCCommand, RestartCommand, HelpCommand, VersionCommand, PlayerInfoCommand, HologramCommand, CpsCommand, TbanCommand, TcheckCommand, VanishCommand, KickAllCommand, AliasCommand, FlyCommand, AnnounceCommand, SprintCommand, HubCommand, TpsCommand, Commands};
use NotZ\Task\{RestartTask, BroadcastTask, CombatTask, ClearEntitiesTask, TagTask, AutoClickTask, EntityTask, DeviceTask, TimeTask};
use NotZ\Arena\{Arena, ArenaCreator};
use NotZ\Arena\{Sumo, SchedulerSumo};
use NotZ\utils\Entities\{JoinCoreBot, JoinCoreMinigame, JoinCoreFFA, JoinCoreKitPVP};
use NotZ\Entity\{SumoWin, AntiBot, EasyBot, MediumBot, HardBot, Kill, DeathNew};
use NotZ\utils\{JoinArena, PlayerData, Scoreboards, Utils, BotUtils, BanUtils, ItemUtils};
use NotZ\utils\FormAPI\{Form, CustomForm, SimpleForm, ModalForm};
use NotZ\utils\{SkywarsProvider, BowwarsProvider, BuildUhcProvider, SpleefProvider, OneVsOneProvider, SumoProvider};
use NotZ\utils\{BowwarsArenaChooser, SumoArenaChooser, OneVsOneArenaChooser, SkywarsArenaChooser, SpleefArenaChooser, BuildUhcArenaChooser};

class Core extends PluginBase implements Listener {
	
    private $data = [];
    public $playerData = [];
    public $sumoArenaChooser;
    public $settersumo = [];
    public $setupDatasumo = [];
    public $arenasumo = [];
	public $staffList = ["ItsNotkungZ", "FamousNewkyX", "ninezy8562", "NotkungX"]; //Todo Config!
	public $targetPlayer = [];
	public $taggedPlayers = [];
	public $os = [];
	public $Cpscounter = [];
    public $fakeOs = [];
    public $device = [];
    public $controls = [];
    public $kill;
    public $death;
    public $restartsec = 0;
    private $allCtrs = ["Unknown", "Mouse", "Touch", "Controller"];
    private $listOfOs = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows10", "Windows", "EducalVersion","Dedicated", "PlayStation", "Switch", "XboxOne"];
    public static $creator = null;
	public static $arena = null;
	public static $itemtask = null;
	public static $cps = null;
	public static $score = null;
	public static $ban = null;
	public static $utils = null;
	public static $plugin;
	public $settings;
	
	public static function getInstance() {
		return self::$plugin;
	}
	
	public function getPrefixCore() {
		return $this->data["prefix"];
	}
	
	public static function getCreator() : ArenaCreator {
		return Core::$creator;
	}
	
	public static function getScore() : Scoreboards {
		return Core::$score;
	}
	
	public static function getArena() : Arena {
		return Core::$arena;
	}
	
	public static function getUtils() : Utils {
		return Core::$utils;
	}
	
	public static function getItemtask() : ItemUtils {
		return Core::$itemtask;
	}
	
	public static function getBanUtils() : BanUtils {
		return Core::$ban;
	}
	
	public static function getCpsCounter() : CpsCounter {
		return Core::$cps;
	}
	
	public function onLoad() : void {
		$this->data["prefix"] = ("§bZelda §f>>§r ");
		Core::$creator = new ArenaCreator($this);
	    Core::$arena = new Arena($this);
	    Core::$itemtask = new ItemUtils($this);
	    Core::$cps = new CpsCounter($this);
	    Core::$score = new Scoreboards($this);
	    Core::$utils = new Utils($this);
	    Core::$ban = new BanUtils($this);
	    self::$plugin = $this;
    }
	
	public function onEnable(): void {
		$this->sumoArenaChooser = new SumoArenaChooser($this); 
		$this->SumoProvider = new SumoProvider($this);
		$this->SumoProvider->loadArenas();
		$this->Cpscounter = [];
		$this->saveDefaultConfig();
		$this->registerevent();
		$this->disableCommands();
		$this->scheduleTask();
		$this->registercommands();
        $this->reloadConfig();
		$this->registerentity();
		$this->saveDefaultConfig();
		$this->registerconfig();
	    $this->getLogger()->info("\n\n              ---" . TextFormat::BOLD . TextFormat::AQUA . 'Zelda' . TextFormat::WHITE . ' Network' . "---\n");
		$this->getServer()->getNetwork()->setName("§bZelda §fNetwork");
	}

	public function onDisable(): void {
        $this->taggedPlayers = [];
        $this->SumoProvider->saveArenas();
		$kill = $this->getDataFolder() . "kill.yml";
		$death = $this->getDataFolder() . "death.yml";
		$sumo = $this->getDataFolder() . "sumo.yml";
		$serverlog = "server.log";
		$serverlock = "server.lock";
		$playersfol = "players";
		if($kill != null and $death != null and $serverlog != null and $serverlock != null and $playersfol != null){
		     unlink($kill);
		     unlink($death);
		     unlink($serverlog);
		     unlink($serverlock);
		     unlink($sumo);
		     @rmdir($playersfol);
	     }
		foreach($this->getServer()->getLevels() as $levels){
			foreach($levels->getEntities() as $entity){
				if($entity instanceof ItemEntity or $entity instanceof Arrow){
		            $entity->close();
		           }
	           }
           }
       }

	private function registerentity(){
		Entity::registerEntity(Kill::class, true);
        Entity::registerEntity(DeathNew::class, true);
        Entity::registerEntity(SumoWin::class, true);
        Entity::registerEntity(JoinCoreFFA::class, true);
	}
	
	public function registerconfig() {
		$this->cfg = $this->getConfig();
		$this->kill = new Config($this->getDataFolder(). "kill.yml", Config::YAML);
        $this->death = new Config($this->getDataFolder(). "death.yml", Config::YAML);
        $this->sumo = new Config($this->getDataFolder(). "sumo.yml", Config::YAML);
		$this->db = new \SQLite3($this->getDataFolder() . "Ban.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS banPlayers(player TEXT PRIMARY KEY, banTime INT, reason TEXT, staff TEXT);");
		$this->message = (new Config($this->getDataFolder() . "bantext.yml", Config::YAML, array(
			"BroadcastBanMessage" => "§f––––––––––––––––––––––––\n§ePlayer §f: §c{player}\n§eHas banned: §c{day}§eD §f| §c{hour}§eH §f| §c{minute}§eM\n§eReason: §c{reason}\n§f––––––––––––––––––––––––§f",
			"KickBanMessage" => "§bGuardian\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
			"LoginBanMessage" => "§bGuardian\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
			"BanMyself" => "§cYou can't ban yourself",
			"BanModeOn" => "§aBan mode on",
			"BanModeOff" => "§cBan mode off",
			"NoBanPlayers" => "§aNo ban players",
			"UnBanPlayer" => "§b{player} §ahas been unban",
			"AutoUnBanPlayer" => "§a{player} Has Auto Unban Already!",
			"BanListTitle" => "§bZelda §eBanSystem",
			"BanListContent" => "§c§lChoose player",
			"PlayerListTitle" => "§bZelda §eBanSystem",
			"PlayerListContent" => "§c§lChoose Player",
			"InfoUIContent" => "§bInformation: \nDay: §a{day} \n§bHour: §a{hour} \n§bMinute: §a{minute} \n§bSecond: §a{second} \n§bReason: §a{reason}",
			"InfoUIUnBanButton" => "§aUnban",
		)))->getAll();
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder() . "data/"); 
		@mkdir($this->getDataFolder() . "players/");
	}

    private function registerevent(){
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
    
	private function registercommands(){
		$this->getServer()->getCommandMap()->register("core", new Commands($this));
		$this->getServer()->getCommandMap()->register("hub", new HubCommand($this));
		$this->getServer()->getCommandMap()->register("hologram", new HologramCommand($this));
		$this->getServer()->getCommandMap()->register("tps", new TpsCommand($this));
		$this->getServer()->getCommandMap()->register("fly", new FlyCommand($this));
		$this->getServer()->getCommandMap()->register("announce", new AnnounceCommand($this));
		$this->getServer()->getCommandMap()->register("kickall", new KickAllCommand($this));
		$this->getServer()->getCommandMap()->register("tban", new TbanCommand($this));
		$this->getServer()->getCommandMap()->register("tcheck", new TcheckCommand($this));
		$this->getServer()->getCommandMap()->register("cps", new CpsCommand($this));
		$this->getServer()->getCommandMap()->register("pinfo", new PlayerInfoCommand($this));
		$this->getServer()->getCommandMap()->register('version', new VersionCommand($this));
		$this->getServer()->getCommandMap()->register('help', new HelpCommand($this));
		$this->getServer()->getCommandMap()->register('restart', new RestartCommand($this));
		$this->getServer()->getCommandMap()->register('sumo', new SumoCommand($this));
		$this->getServer()->getCommandMap()->register('1vs1', new OneVsOneCommand($this));
	}
	
	private function scheduleTask(){
		$this->getScheduler()->scheduleRepeatingTask(new AutoClickTask($this), 15);
        $this->getScheduler()->scheduleRepeatingTask(new TimeTask($this), 70);
        $this->getScheduler()->scheduleRepeatingTask(new CombatTask($this), 20);
	    $this->getScheduler()->scheduleDelayedRepeatingTask(new BroadcastTask($this), 200, 11000);
		$this->getScheduler()->scheduleRepeatingTask(new TagTask($this), 10);
		$this->getScheduler()->scheduleRepeatingTask(new EntityTask($this), 120);
	    $this->getScheduler()->scheduleRepeatingTask(new RestartTask($this), 20);
	}

	private function disableCommands(){
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("me"));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("defaultgamemode"));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("difficulty"));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("spawnpoint"));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("setworldspawn"));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("title"));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("seed"));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("particle"));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("version"));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("help"));
	}
	
	public function joinToRandomArenasumo(Player $player) {
        $arena = $this->sumoArenaChooser->getRandomArena();
        if(!is_null($arena)) {
            $arena->joinToArena($player);
            return;
        }
        $player->sendMessage("§bZelda §f>>§e All the arenas are full!");
    }

   public function addKill(Player $player) {
        $data = $this->getData($player->getName());
        $data->addKill();
    }
    
    public function handleStreak(Player $player, Player $v) {
        $killer = $this->getData($player->getName());
        $loser = $this->getData($v->getName());
        $oldStreak = $loser->getStreak();
        if($oldStreak >= 5) {
            $v->sendMessage(Core::getInstance()->getPrefixCore() . "§r§aYour " . $oldStreak . " killstreak was ended by " . $player->getName() . "!");
            $player->sendMessage(Core::getInstance()->getPrefixCore() . "§r§aYou have ended " . $v->getName() . "'s " . $oldStreak . " killstreak!");
        }
        $newStreak = $killer->getStreak();
        if(is_int($newStreak / 5)) {
            $this->getServer()->broadcastMessage(Core::getInstance()->getPrefixCore() . "§r§a" . $player->getName() . " is on a " . $newStreak . " killstreak!");
        }
    }

    public function addDeath(Player $player) {
        $this->getData($player->getName())->addDeath();
        return;
    }

    public function getData($name) {
        return new PlayerData($this, $name);
    }
    
   public function isInCpsCounter($player): bool {
        return isset($this->Cpscounter[$player]) ? true : false;
    }

    public function cpscounter($player) {
        if (isset($this->Cpscounter[$player])) {
            unset($this->Cpscounter[$player]);
        } else {
            $this->Cpscounter[$player] = $player;
        }
    }
	
	public function getPlayerOs(Player $player) : ? string{
        if(!isset($this->os[$player->getName()]) or $this->os[$player->getName()]==null){
			return null;
		}
		return $this->listOfOs[$this->os[$player->getName()]];
	}

    public function getPlayerDevice(Player $player): ? string{
		if(!isset($this->device[$player->getName()]) or $this->device[$player->getName()]==null){
			return null;
		}
		return $this->device[$player->getName()];
	}
	
   public function getPlayerControls(Player $player): ? string{
		if(!isset($this->controls[$player->getName()]) or $this->controls[$player->getName()]==null){
			return null;
		}
		return $this->allCtrs[$this->controls[$player->getName()]];
	}

    public function setPlayerOs(Player $player, string $os){
        $this->os[strtolower($player->getName())] = $os;
    }
    
    public function getFakeOs(Player $player) : ? string{
        return $this->fakeOs[strtolower($player->getName())] ?? null;
    }

    public function setFakeOs(Player $player, string $fakeOs) : bool{
        if($fakeOs == "") return false;
        $this->fakeOs[strtolower($player->getName())] = $fakeOs;
        return true;
    }
    
	public function setTagged($player, $value = true, int $time = 10) {
		if($player instanceof Player) $player = $player->getName();
		if($value) {
			$this->taggedPlayers[$player] = $time;
		} else {
			unset($this->taggedPlayers[$player]);
		}
	}

	public function isTagged($player) {
		if($player instanceof Player) $player = $player->getName();
		return isset($this->taggedPlayers[$player]);
	}
}
