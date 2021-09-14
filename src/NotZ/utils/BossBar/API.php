<?php

namespace NotZ\utils\BossBar;

use pocketmine\plugin\Plugin;

class BossBarAPI{

    public static function load(Plugin $plugin){
        PacketListener::register($plugin);
    }
}