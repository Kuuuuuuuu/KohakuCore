<?php

namespace NotZ\Arena;

use pocketmine\command\{Command, CommandSender};
use pocketmine\{Player, Server};
use pocketmine\utils\TextFormat as Color;
use pocketmine\math\Vector3;
use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\item\{Item, Potion};
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use NotZ\utils\FormAPI\{CustomForm, SimpleForm};
use NotZ\Core;
use NotZ\Game;
use NotZ\OneVsOne\OneVsOne;
use NotZ\Sumo\Sumo;
use NotZ\Skywars\Skywars;
use NotZ\Bowwars\Bowwars;
use NotZ\BuildUhc\BuildUhc;

class Arena {
	
	private $arena;
	
	public function __construct(Core $arena){
		$this->arena = $arena;
		
	}
	
	public function getArena(){
		return $this->arena;
	}

	public function getMode(Player $player) {
		if($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getFistArena())){
		    return "Fist";
		} else if($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getResistanceArena())){
			return "Resistance";
		}
	}

	public function getPlayers(string $arena){
		if(!Server::getInstance()->getLevelByName($arena)){
			return Color::DARK_RED . "Unknown World";
		} else {
			return count(Server::getInstance()->getLevelByName($arena)->getPlayers());
		}
	}
	
	public function playSound(Player $player, string $soundName, int $pitch = 1, int $volumen = 20){
		$pk = new PlaySoundPacket();
		$pk->soundName = $soundName;
		$pk->volume = $volumen;
		$pk->pitch = $pitch;
		$pk->x = $player->x;
		$pk->y = $player->y;
		$pk->z = $player->z;
		$player->dataPacket($pk);
		return;
	}
	
	public function onJoinFist(Player $player){
		$world = Core::getCreator()->getFistArena();
		$x = Core::getCreator()->getFistSpawn();
		if($world != null){
			Server::getInstance()->loadLevel($world);
			$player->setGamemode(Player::ADVENTURE);
			$player->getInventory()->clearAll();
			$player->getArmorInventory()->clearAll();
			$player->removeAllEffects();
			$player->setAllowFlight(false);
            $player->setFlying(false);
            $player->setMaxHealth(20);
            $player->setHealth(20);
            $player->setFood(20);
            $player->setScale(1);
            self::getKitFist($player);
			$player->teleport(Server::getInstance()->getLevelByName($world)->getSafeSpawn());
			$player->teleport(new Vector3($x[0], $x[1]+0.6, $x[2]));
			self::playSound($player, 'jump.slime');

		} else {
			$player->sendMessage(Core::getInstance()->getPrefixCore() . Color::RED . "Arena not available");
		}
	}
	
	public function onJoinResistance(Player $player){
		$world = Core::getCreator()->getResistanceArena();
		$x = Core::getCreator()->getResistanceSpawn();
		if($world != null){
			Server::getInstance()->loadLevel($world);
			$player->setGamemode(Player::ADVENTURE);
			$player->getInventory()->clearAll();
			$player->getArmorInventory()->clearAll();
			$player->removeAllEffects();
			$player->setAllowFlight(false);
            $player->setFlying(false);
            $player->setMaxHealth(20);
            $player->setHealth(20);
            $player->setFood(20);
            $player->setScale(1);
            self::getKitResistance($player);
			$player->teleport(Server::getInstance()->getLevelByName($world)->getSafeSpawn());
			$player->teleport(new Vector3($x[0], $x[1]+0.6, $x[2]));
			self::playSound($player, 'jump.slime');
		} else {
			$player->sendMessage(Core::getInstance()->getPrefixCore() . Color::RED . "Arena not available");
		}
	}
	
  public function NickForm($player){
     if(!$player->hasPermission("Zelda.nick")){
		$player->sendMessage("§bZelda §f>>§r §eYou don't have enough permissions to use this command");
		return false;
	}
      $form = new SimpleForm(function (Player $player, int $data = null) {
      $result = $data;
      if($result === null){
        return true;
      }
      switch($result){
        case 0:
          $this->CustomNickForm($player);    
          break;        
        case 1:     
          $this->ResetNick($player);         
          break;
          
      }
    });
    $name = "§eNow Your Name is: §a" . $player->getDisplayName();
    $form->setTitle("§bZelda §eNick");
    $form->setContent($name);
    $form->addButton("§a§lChange Name\n§r§8Tap to continue", 0, "textures/ui/confirm");
    $form->addButton("§c§lReset Name\n§r§8Tap to reset", 0, "textures/ui/trash");
    $form->sendToPlayer($player);
    return $form;
  }
  
  public function CustomNickForm($player){
      $form = new CustomForm(function (Player $player, array $data = null){
      $result = $data;
      if($result === null){
        return true;
      }
      if($result != null){
         $player->setDisplayName($data[0]);
         $player->setNameTag($data[0]);
         $player->sendMessage(Core::getInstance()->getPrefixCore() . "§6Your nickname is now §c" . $data[0]);
         return true;
         
		}
     });
    $form->setTitle("§bZelda §eNick");
    $form->addInput("§eEnter New Name Here!");
    $form->sendToPlayer($player);
    return $form;
  }
  
  private function ResetNick(Player $player){
  	$player->setDisplayName($player->getName());
  	$player->setNameTag($player->getName());
  	$player->sendMessage(Core::getInstance()->getPrefixCore() . "§eYour nickname has been resetted!");
  	return true;
  }
  
    public function SettingsForm($player) {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
  case 0:
  $this->NickForm($player);
  break;
  case 1:
  $command = "cps";
  Server::getInstance()->dispatchcommand($player, $command);
  break;
  case 2:
  $this->CognomenForm($player);
  break;
  case 3:
  $command = "cape";
  Server::getInstance()->dispatchcommand($player, $command);
  break;
  case 4:
  $command = "report";
  Server::getInstance()->dispatchcommand($player, $command);
  break;
  
            }
        });
        $form->setTitle("§bZelda §eSettings");
        $form->addButton("§6Change §eName", 0, "textures/items/chainmail_chestplate");
        $form->addButton("§6Cps §eSwitch", 0, "textures/items/iron_chestplate");
        $form->addButton("§bCognomen §eSettings", 0, "textures/items/diamond_chestplate");
        $form->addButton("§bCape §eSettings", 0, "textures/items/diamond");
        $form->addButton("§cReport §ePlayers", 0, "textures/items/snowball");
        $form->sendToPlayer($player);
        return true;
    }
    
    public function CognomenForm($player){
		$form = new CustomForm(function(Player $player, $data){
			$result = $data[0];
			if($result === null){
				return true;
			}
			if($player instanceof Player){
				$pp = Server::getInstance()->getPluginManager()->getPlugin("PureChat");
				$pp->setSuffix($data[0], $player);
				$player->sendMessage(Core::getInstance()->getPrefixCore() . "Set Your Gang to $data[0] Complete!");
			}
		});
		$form->setTitle("§bZelda §eGang System");
		$form->addInput("Enter You Gang Name");
		$form->addLabel("§f§lใส่ขื่อแก๊งค์ลงไป เพื่อ เข้าแก๊งค์ หาก พบคนแอบอ้างแก๊งค์นั้นๆ ให้บอกแอดมิน");
		$form->sendToPlayer($player);
	}
   
	public function getForm(Player $player){
		$form = new SimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
		
			switch ($result){
				case 0:
				self::onJoinFist($player);
				break;
				case 1:
				self::onJoinResistance($player);
				break;
				case 2:
				Core::getInstance()->joinToRandomArenasumo($player);
				break;
			}
		});

		$fist = "§eCurrently Playing§0:§b " . self::getPlayers(Core::getCreator()->getFistArena());
		$resis = "§eCurrently Playing§0:§b " . self::getPlayers(Core::getCreator()->getResistanceArena());
		$sumo = "§eCurrently Playing§0:§b ". self::getPlayers("sumo");
		$form->setTitle("§bZelda §ePractice Core");
		$form->addButton("§6Fist\n" . $fist, 0, "textures/items/beef_cooked.png");
		$form->addButton("§6Resistance\n" . $resis, 0, "textures/items/snowball.png");
		$form->addButton("§6Sumo\n" . $sumo, 0, "textures/items/beef_cooked.png");
		$form->sendToPlayer($player);
		return $form;
		
	}
	
	public function getKitFist(Player $player){
        $inventory = $player->getInventory();
        $inventory->addItem(Item::get(Item::STEAK, 0, 16));
        $inventory->sendContents($player);
	}
	
	public function getKitResistance(Player $player){
        $inventory = $player->getInventory();
        $inventory->addItem(Item::get(Item::STEAK, 0, 16));
        $inventory->sendContents($player);
        $eff = new EffectInstance(Effect::getEffect(Effect::RESISTANCE) , 4 * 999999, 255, true);
        $player->addEffect($eff); 
	}
	
	public function getReKit(Player $player){
		if($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getFistArena())){
		
		} else if($player->getLevel() == Server::getInstance()->getLevelByName(Core::getCreator()->getResistanceArena())){
		}
	}
}
