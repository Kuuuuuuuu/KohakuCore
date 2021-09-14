<?php

namespace NotZ\Arena;

use pocketmine\Player;
use pocketmine\utils\{Config, TextFormat as Color};

use NotZ\Core;

class ArenaCreator {
	
	private $creator;
	
	public function __construct(Core $creator){
		$this->creator = $creator;
		
	}
	
	public function getCreator(){
		return $this->creator;
		
	}

	public function getFistArena(){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		return $data->get("Fist");
	}
	
	public function getFistSpawn(){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		return $data->get("Fist-Spawn");
	}
	
	public function getResistanceArena(){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		return $data->get("Resistance");
	}
	
	public function getResistanceSpawn(){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		return $data->get("Resistance-Spawn");
	}

	public function setFistArena(Player $player, string $world){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		$data->set("Fist", $world);
		$data->save();
		$player->sendMessage($this->getCreator()->getPrefixCore() . Color::GREEN . "Fist Arena: " . Color::YELLOW . $world . Color::GREEN . " saved successfully");
		$player->sendMessage($this->getCreator()->getPrefixCore() . Color::GOLD . "use /core spawn fist - to select the spawn");
	}
	
	public function setFistSpawn(Player $player){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		$x = $player->getX();
		$y = $player->getY();
		$z = $player->getZ();
		$xyz = array($x, $y, $z);
		$data->set("Fist-Spawn", $xyz);
		$data->save();
		$player->sendMessage($this->getCreator()->getPrefixCore() . Color::GREEN . "Fist spawn saved successfully");
	}
	
	public function removeFist(Player $player){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		$data->remove("Fist");
		$data->remove("Fist-Spawn");
		$data->save();
		$player->sendMessage($this->getCreator()->getPrefixCore() . Color::RED . "Fist removed arena");
	}
	
	public function setResistanceArena(Player $player, string $world){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		$data->set("Resistance", $world);
		$data->save();
		$player->sendMessage($this->getCreator()->getPrefixCore() . Color::GREEN . "Resistance Arena: " . Color::YELLOW . $world . Color::GREEN . " saved successfully");
		$player->sendMessage($this->getCreator()->getPrefixCore() . Color::GOLD . "use /core spawn Resistance - to select the spawn");
	}
	
	public function setResistanceSpawn(Player $player){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		$x = $player->getX();
		$y = $player->getY();
		$z = $player->getZ();
		$xyz = array($x, $y, $z);
		$data->set("Resistance-Spawn", $xyz);
		$data->save();
		$player->sendMessage($this->getCreator()->getPrefixCore() . Color::GREEN . "Resistance spawn saved successfully");
	}
	
	public function removeResistance(Player $player){
		$data = new Config ($this->getCreator()->getDataFolder()."data/arenas.yml", Config::YAML);
		$data->remove("Resistance");
		$data->remove("Resistance-Spawn");
		$data->save();
		$player->sendMessage($this->getCreator()->getPrefixCore() . Color::RED . "Resistance removed arena");
	}
}

?>