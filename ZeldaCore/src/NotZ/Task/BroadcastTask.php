<?php

declare(strict_types=1);

namespace NotZ\Task;

use pocketmine\scheduler\Task;
use NotZ\Core;

class BroadcastTask extends Task{
	
	public function __construct(Core $plugin){
		$this->plugin=$plugin;
		$this->line=-1;
	}
	public function onRun(int $tick):void{
		$cast=[
		$this->plugin->getPrefixCore()."§eCheck out our Update at Omlet Arcade. notkungz1 !",
		$this->plugin->getPrefixCore()."§eติดตามข่าวสารเซิฟได้ที่ Omlet Arcade. notkungz1"
		];
		$this->line++;
		$msg=$cast[$this->line];
		foreach($this->plugin->getServer()->getOnlinePlayers() as $online){
			$online->sendMessage($msg);
		}
		if($this->line===count($cast) - 1) $this->line = -1;
	}
}