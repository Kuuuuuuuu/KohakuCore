<?php


declare(strict_types=1);

namespace NotZ\utils;

use NotZ\Core;
use NotZ\Arena\Sumo;

class SumoArenaChooser {

    public $plugin;

    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
    }

    public function getRandomArena(): ? Sumo {
        $availableArenas = [];
        foreach ($this->plugin->arenasumo as $index => $arena) {
            $availableArenas[$index] = $arena;
        }
        foreach ($availableArenas as $index => $arena) {
            if($arena->phase !== 0 || $arena->setup) {
                unset($availableArenas[$index]);
            }
        }
        $arenasByPlayers = [];
        foreach ($availableArenas as $index => $arena) {
            $arenasByPlayers[$index] = count($arena->players);
        }
        arsort($arenasByPlayers);
        $top = -1;
        $availableArenas = [];
        foreach ($arenasByPlayers as $index => $players) {
            if($top == -1) {
                $top = $players;
                $availableArenas[] = $index;
            } else {
                if($top == $players) {
                    $availableArenas[] = $index;
                }
            }
        }
        if(empty($availableArenas)) {
            return null;
        }
        return $this->plugin->arenasumo[$availableArenas[array_rand($availableArenas, 1)]];
    }
}