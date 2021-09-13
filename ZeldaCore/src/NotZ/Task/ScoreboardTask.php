<?php

namespace NotZ\Task;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use NotZ\utils\Scoreboards;
use NotZ\Core;
use NotZ\utils\math\Time;
use NotZ\Arena\{Sumo, SchedulerSumo};
use pocketmine\Server;

class ScoreboardTask extends Task {

	private $player;

	public function __construct(Core $plugin, $player){
		$this->player = $player;
	}

	public function onRun(int $currentTick) : void {
		if($this->player->getLevel() !== Server::getInstance()->getLevelByName("sumo")) {
	        $this->sb($this->player);
	    } else {
		    $this->sumo($this->player);
		}
		if(!$this->player->isOnline()) {
			Core::getInstance()->getScheduler()->cancelTask($this->getTaskId());
		}
	}
	
	public function sumo(Player $player) : void {
		$ping = $player->getPing();
		$time = Time::calculateTime(Core::getInstance()->gameTime);
		$lines = [
            1 => "§f-----------------§0",
            2=> "§fTime §fLeft§f:§6 $time",
            3=> "§fYour §ePing§f:§6 $ping",
            4=> "§f-----------------"
        ];
        
        Core::$score->new($player, "ObjectiveName", "§b§lZelda §eSumo");
		foreach($lines as $line => $content)
		   Core::$score->setLine($player, $line, $content);
		}

	public function sb(Player $player) : void {
		$ping = $player->getPing();
		$data = Core::getInstance()->getData($player->getName());
		$kills =  $data->getKills();
		$deaths = $data->getDeaths();
		$on = count(Server::getInstance()->getOnlinePlayers());
		$lines = [
		    1 => "§f-----------------§0",
			2 => "§bK: §f$kills §bD: §f$deaths",
		    3 => "§fYour §bPing: §f$ping",
			4 => "§fPlayer §bOnline: §f$on",
			5 => "§f-----------------"
		];

		Core::$score->new($player, "ObjectiveName", "§b§lZelda §fPractice");
		foreach($lines as $line => $content)
			Core::$score->setLine($player, $line, $content);
	}
}