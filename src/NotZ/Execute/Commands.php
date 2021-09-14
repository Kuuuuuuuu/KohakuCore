<?php

namespace NotZ\Execute;

use pocketmine\Server;
use pocketmine\command\{PluginCommand, CommandSender};
use pocketmine\utils\TextFormat as Color;
use NotZ\utils\Entities\{CoreManager, JoinCoreFFA, JoinCoreMinigame, JoinCoreKitPVP, JoinCoreBot};
use NotZ\utils\Permissions;
use NotZ\Core;

class Commands extends PluginCommand{
	
	private $command;
	
	public function __construct(Core $command){
		parent::__construct("core", $command);
		$this->setDescription("ZeldaCore info");
		$this->setUsage("use /core help");
		$this->command = $command;
	}
	
	public function getCommand(){
		return $this->command;
	}
	
	public function execute(CommandSender $sender, string $label, array $args): bool {
		if(!isset($args[0])){
			$sender->sendMessage(Color::BOLD . Color::WHITE . ">> " . Color::RESET . Color::RED."use /core help");
			return false;
		}
		
		switch ($args[0]){
			case "help":
			if(!$sender->hasPermission(Permissions::CORE_ADMIN)){
				$sender->sendMessage(Color::BOLD.">> " . Color::RESET.Color::RED."You don't have enough permissions to use this command");
				return false;
			}
			
			$sender->sendMessage(Color::BOLD.Color::GREEN. Core::getInstance()->getPrefixCore());
			$sender->sendMessage(Color::GREEN."/".$label.Color::AQUA." make <mode> <world>" . Color::AQUA . " - create new Arena for FFA");
			$sender->sendMessage(Color::GREEN."/".$label.Color::AQUA." spawn <mode>" . Color::AQUA . " - set spawn Arena for FFA");
			$sender->sendMessage(Color::GREEN."/".$label.Color::AQUA." remove <mode>" . Color::AQUA . " - delete Arena for FFA");
			$sender->sendMessage(Color::GREEN."Modes: ".Color::AQUA."fist, Resistance");
			$sender->sendMessage(Color::GREEN."/".$label.Color::AQUA." set-npcffa" . Color::AQUA . " - set Slapper Join for FFA");
			$sender->sendMessage(Color::GREEN."/".$label.Color::AQUA." remove-npc" . Color::AQUA . " - killed Slappers");
			
			break;
			case "make":
			case "create":
			if(!$sender->hasPermission(Permissions::CORE_ADMIN)){
				$sender->sendMessage(Color::BOLD.">> " . Color::RESET.Color::RED."You don't have enough permissions to use this command");
				return false;
			}
			
			if(!isset($args[1])){
				$sender->sendMessage($this->getCommand()->getPrefixCore() . Color::RED."use /core make <mode> <world>");
				$sender->sendMessage(Color::GREEN."Modes: ".Color::AQUA."fist, Resistance");
				return false;
			}
			
			if(!isset($args[2])){
				$sender->sendMessage($this->getCommand()->getPrefixCore() . Color::RED."use /core make <mode> <world>");
				$sender->sendMessage(Color::GREEN."Modes: ".Color::AQUA."fist, Resistance");
				return false;
			}
			switch ($args[1]){
				case "Resistance":
				if(!file_exists(Server::getInstance()->getDataPath(). "worlds/" . $args[2])) {
					$sender->sendMessage($this->getCommand()->getPrefixCore() . Color::BOLD.Color::WHITE.">> ".Color::RESET.Color::RED."World " .$args[2] . " not found");
				} else {
					 Server::getInstance()->loadLevel($args[2]);
					 $sender->teleport(Server::getInstance()->getLevelByName($args[2])->getSafeSpawn());
					 Core::getCreator()->setResistanceArena($sender, $args[2]);
				}
				
				break;
				case "fist":
				if(!file_exists(Server::getInstance()->getDataPath(). "worlds/" . $args[2])) {
					$sender->sendMessage(Color::RED."World " .$args[2] . " not found");
				} else {
					 Server::getInstance()->loadLevel($args[2]);
					 $sender->teleport(Server::getInstance()->getLevelByName($args[2])->getSafeSpawn());
					 Core::getCreator()->setFistArena($sender, $args[2]);
				}
				
				break;
				default:
				
				$sender->sendMessage($this->getCommand()->getPrefixCore() . Color::RED."use /core make <mode> <world>");
				$sender->sendMessage(Color::GREEN."Modes: ".Color::AQUA."fist, Resistance");
				
				break;
			}
			break;
			case "spawn":
			if(!$sender->hasPermission(Permissions::CORE_ADMIN)){
				$sender->sendMessage(Color::BOLD.">> " . Color::RESET.Color::RED."You don't have enough permissions to use this command");
				return false;
			}
			
			if(!isset($args[1])){
				$sender->sendMessage($this->getCommand()->getPrefixCore() . Color::RED."use /core spawn <mode>");
				$sender->sendMessage(Color::GREEN."Modes: ".Color::AQUA."fist, Resistance");
				return false;
			}
			switch ($args[1]){
				case "Resistance":
				$arena = Core::getCreator()->getResistanceArena();
				if($arena != null){
					Core::getCreator()->setResistanceSpawn($sender);
				} else {
				    $sender->sendMessage($this->getCommand()->getPrefixCore() . Color::RED."The Resistance world has not registered");
				    $sender->sendMessage(Color::RED."use /core make <mode>");
				}
				
				break;
				case "fist":
				$arena = Core::getCreator()->getFistArena();
				if($arena != null){
					Core::getCreator()->setFistSpawn($sender);
				} else {
				    $sender->sendMessage($this->getCommand()->getPrefixCore() . Color::RED."The Fist world has not registered");
				    $sender->sendMessage(Color::RED."use /core make <mode>");
				}
				
				break;
				default:
				$sender->sendMessage($this->getCommand()->getPrefixCore() . Color::RED."use /core spawn <mode>");
				$sender->sendMessage(Color::GREEN."Modes: ".Color::AQUA."fist, Resistance");
				break;
			}
			break;
			case "remove":
			if(!$sender->hasPermission(Permissions::CORE_ADMIN)){
				$sender->sendMessage(Color::BOLD.">> " . Color::RESET.Color::RED."You don't have enough permissions to use this command");
				return false;
			}
			if(!isset($args[1])){
				$sender->sendMessage($this->getCommand()->getPrefixCore() . Color::RED."use /core remove <mode>");
				$sender->sendMessage(Color::GREEN."Modes: ".Color::AQUA."fist, Resistance");
				return false;
			}
			switch ($args[1]){
				case "fist":
				Core::getCreator()->removeFist($sender);
				break;
				case "Resistance":
				Core::getCreator()->removeResistance($sender);
				break;
				default:
				$sender->sendMessage($this->getCommand()->getPrefixCore() . Color::RED."use /core remove <mode>");
				$sender->sendMessage(Color::GREEN."Modes: ".Color::AQUA."fist, Resistance");
				break;
			}
			break;
			case "set-npcffa":
			if(!$sender->hasPermission(Permissions::CORE_ADMIN)){
				$sender->sendMessage(Color::BOLD."» " . Color::RESET.Color::RED."You don't have enough permissions to use this command");
				return false;
			}
			$slapper = new CoreManager();
		    $slapper->setJoinEntityFFA($sender->getPlayer());
		    $sender->sendMessage(Color::BOLD . Color::WHITE . "» " . Color::RESET . Color::GREEN . "Join Slapper Spawned");
			break;
			case "remove-npc":
			if(!$sender->hasPermission(Permissions::CORE_ADMIN)){
				$sender->sendMessage(Color::BOLD."» " . Color::RESET.Color::RED."You don't have enough permissions to use this command");
				return false;
			}
			
			$npc = Server::getInstance()->getDefaultLevel()->getEntities();
				foreach ($npc as $entity){
					if($entity instanceof JoinCoreFFA){
						$sender->sendMessage(Color::BOLD . Color::WHITE . "» " . Color::RESET . Color::RED . "Entitie Join Killed");
						$entity->close();
					}
				}
			break;
			default:
			$sender->sendMessage(Core::getInstance()->getPrefixCore() . "§e plugin made by: ItsNotkungZ");
			break;
		}
		return true;
	}
}

?>
