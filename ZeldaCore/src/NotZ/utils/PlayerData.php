<?php

namespace NotZ\utils;

use NotZ\Core;

class PlayerData {

    private $plugin;
    private $player = null;
    private $kills = 0;
    private $killStreak = 0;
    private $kdr = 0;
    private $deaths = 0;
    private $data = null;

    public function __construct(Core $plugin, $player){
        $this->plugin = $plugin;
        $this->player = $player;
        $path = $this->getPath();
        if(is_file($path)) {
            $data = yaml_parse_file($path);
            $this->data = $data;
            $this->kills = $data["kills"];
            if(isset($data["killstreak"])) {
                $this->killStreak = $data["killstreak"];
            } else {
                $this->killStreak = 0;
            }
            if(isset($data["kdr"])) {
                $this->kdr = $data["kdr"];
            } else {
                $this->kdr = 0;
            }
            $this->deaths = $data["deaths"];
        } else {
            return;
        }
    }

    public function getStats() {
        return $this->data;
    }

    public function getName() {
        return $this->player;
    }

    public function getKills() {
        return $this->kills;
    }

    public function getStreak() {
        return $this->killStreak;
    }

    public function getDeaths() {
        return $this->deaths;
    }

    public function getKdr() {
        if ($this->deaths > 0){
            return $this->kills / $this->deaths;
        } else {
            return 1;
        }
    }
     
    public function addKill() {
        $this->kills++;
        $this->killStreak++;
        $this->save();
    }

    public function addDeath() {
        $this->deaths++;
        $this->killStreak = 0;
        $this->save();
    }

    public function getPath() {
        return $this->plugin->getDataFolder() . "players/" . strtolower($this->player) . ".yml";
    }

    public function save() {
        yaml_emit_file($this->getPath(), ["name" => $this->player, "kills" => $this->kills, "killstreak" => $this->killStreak, "kdr" => $this->getKdr(), "deaths" => $this->deaths]);
    }

}
