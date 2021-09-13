<?php

namespace NotZ\utils;

use NotZ\utils\FormAPI\{Form, CustomForm, SimpleForm, ModalForm};
use NotZ\Core;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\{Config, TextFormat};

class BanUtils {
	
      public function openPlayerListUI($player){
		$form = new SimpleForm(function (Player $player, $data = null){
			$target = $data;
			if($target === null){
				return true;
			}
			Core::getInstance()->targetPlayer[$player->getName()] = $target;
			$this->openTbanUI($player);
		});
		$form->setTitle(Core::getInstance()->message["PlayerListTitle"]);
		$form->setContent(Core::getInstance()->message["PlayerListContent"]);
		foreach(Server::getInstance()->getOnlinePlayers() as $online){
			$form->addButton($online->getName(), -1, "", $online->getName());
		}
		$form->sendToPlayer($player);
		return $form;
	}
	
	public function openTbanUI($player){
		$form = new CustomForm(function (Player $player, array $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
			if(isset(Core::getInstance()->targetPlayer[$player->getName()])){
				if(Core::getInstance()->targetPlayer[$player->getName()] == $player->getName()){
					$player->sendMessage(Core::getInstance()->message["BanMyself"]);
					return true;
				}
				$now = time();
				$day = ($data[1] * 86400);
				$hour = ($data[2] * 3600);
				if($data[3] > 1){
					$min = ($data[3] * 60);
				} else {
					$min = 60;
				}
				$banTime = $now + $day + $hour + $min;
				$banInfo = Core::getInstance()->db->prepare("INSERT OR REPLACE INTO banPlayers (player, banTime, reason, staff) VALUES (:player, :banTime, :reason, :staff);");
				$banInfo->bindValue(":player", Core::getInstance()->targetPlayer[$player->getName()]);
				$banInfo->bindValue(":banTime", $banTime);
				$banInfo->bindValue(":reason", $data[4]);
				$banInfo->bindValue(":staff", $player->getName());
				$banInfo->execute();
				$target = Server::getInstance()->getPlayerExact(Core::getInstance()->targetPlayer[$player->getName()]);
				if($target instanceof Player){
					$target->kick(str_replace(["{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$data[1], $data[2], $data[3], $data[4], $player->getName()], Core::getInstance()->message["KickBanMessage"]), false);
				}
				Server::getInstance()->broadcastMessage(str_replace(["{player}", "{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [Core::getInstance()->targetPlayer[$player->getName()], $data[1], $data[2], $data[3], $data[4], $player->getName()], Core::getInstance()->message["BroadcastBanMessage"]));
				unset(Core::getInstance()->targetPlayer[$player->getName()]);

			}
		});
		$list[] = Core::getInstance()->targetPlayer[$player->getName()];
		$form->setTitle("§bZelda §eBanSystem");
		$form->addDropdown("\nTarget", $list);
		$form->addSlider("Day/s", 0, 30, 1);
		$form->addSlider("Hour/s", 0, 24, 1);
		$form->addSlider("Minute/s", 0, 60, 1);
		$form->addInput("Reason");
		$form->sendToPlayer($player);
		return $form;
	}

	public function openTcheckUI($player){
		$form = new SimpleForm(function (Player $player, $data = null){
			if($data === null){
				return true;
			}
			Core::getInstance()->targetPlayer[$player->getName()] = $data;
			$this->openInfoUI($player);
		});
		$banInfo = Core::getInstance()->db->query("SELECT * FROM banPlayers;");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);	
		if (empty($array)) {
			$player->sendMessage(Core::getInstance()->message["NoBanPlayers"]);
			return true;
		}
		$form->setTitle(Core::getInstance()->message["BanListTitle"]);
		$form->setContent(Core::getInstance()->message["BanListContent"]);
		$banInfo = Core::getInstance()->db->query("SELECT * FROM banPlayers;");
		$i = -1;
		while ($resultArr = $banInfo->fetchArray(SQLITE3_ASSOC)) {
			$j = $i + 1;
			$banPlayer = $resultArr['player'];
			$form->addButton(TextFormat::BOLD . "$banPlayer", -1, "", $banPlayer);
			$i = $i + 1;
		}
		$form->sendToPlayer($player);
		return $form;
	}
	
	public function openInfoUI($player){
		$form = new SimpleForm(function (Player $player, int $data = null){
		$result = $data;
		if($result === null){
			return true;
		}
			switch($result){
				case 0:
					$banplayer = Core::getInstance()->targetPlayer[$player->getName()];
					$banInfo = Core::getInstance()->db->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
					$array = $banInfo->fetchArray(SQLITE3_ASSOC);
					if (!empty($array)) {
						Core::getInstance()->db->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
						$player->sendMessage(str_replace(["{player}"], [$banplayer], Core::getInstance()->message["UnBanPlayer"]));
					}
					unset(Core::getInstance()->targetPlayer[$player->getName()]);
				break;
			}
		});
		$banPlayer = Core::getInstance()->targetPlayer[$player->getName()];
		$banInfo = Core::getInstance()->db->query("SELECT * FROM banPlayers WHERE player = '$banPlayer';");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);
		if (!empty($array)) {
			$banTime = $array['banTime'];
			$reason = $array['reason'];
			$staff = $array['staff'];
			$now = time();
			if($banTime < $now){
				$banplayer = Core::getInstance()->targetPlayer[$player->getName()];
				$banInfo = Core::getInstance()->db->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
				$array = $banInfo->fetchArray(SQLITE3_ASSOC);
				if (!empty($array)) {
					Core::getInstance()->db->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
					$player->sendMessage(str_replace(["{player}"], [$banplayer], Core::getInstance()->message["AutoUnBanPlayer"]));
				}
				unset(Core::getInstance()->targetPlayer[$player->getName()]);
				return true;
			}
			$remainingTime = $banTime - $now;
			$day = floor($remainingTime / 86400);
			$hourSeconds = $remainingTime % 86400;
			$hour = floor($hourSeconds / 3600);
			$minuteSec = $hourSeconds % 3600;
			$minute = floor($minuteSec / 60);
			$remainingSec = $minuteSec % 60;
			$second = ceil($remainingSec);
		}
		$form->setTitle(TextFormat::BOLD . $banPlayer);
		$form->setContent(str_replace(["{day}", "{hour}", "{minute}", "{second}", "{reason}", "{staff}"], [$day, $hour, $minute, $second, $reason, $staff], Core::getInstance()->message["InfoUIContent"]));
		$form->addButton(Core::getInstance()->message["InfoUIUnBanButton"]);
		$form->sendToPlayer($player);
		return $form;
	}
}