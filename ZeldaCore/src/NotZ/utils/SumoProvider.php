<?php

declare(strict_types=1);

namespace NotZ\utils;

use pocketmine\level\Level;
use pocketmine\utils\Config;
use NotZ\Core;
use NotZ\Arena\Sumo;

/**
 * Class YamlDataProvider
 * @package onevsone\provider
 */
class SumoProvider {

    /** @var OneVsOne $plugin */
    private $plugin;

    /** @var array $config */
    public $config;

    /**Hmm**/
    public function __construct(Core $plugin) {
        $this->plugin = $plugin;
        $this->init();
    }

    public function init() {
        if(!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        if(!is_dir($this->getDataFolder() . "arenasumo")) {
            @mkdir($this->getDataFolder() . "arenasumo");
        }
        if(!is_dir($this->getDataFolder() . "savesumo")) {
            @mkdir($this->getDataFolder() . "savesumo");
        }
    }

    public function loadArenas() {
        foreach (glob($this->getDataFolder() . "arenasumo" . DIRECTORY_SEPARATOR . "*.yml") as $arenaFile) {
            $config = new Config($arenaFile, Config::YAML);
            $this->plugin->arenasumo[basename($arenaFile, ".yml")] = new Sumo($this->plugin, $config->getAll(\false));
        }
    }

    public function saveArenas() {
        foreach ($this->plugin->arenasumo as $fileName => $arena) {
            $config = new Config($this->getDataFolder() . "arenasumo" . DIRECTORY_SEPARATOR . $fileName . ".yml", Config::YAML);
            $config->setAll($arena->data);
            $config->save();
        }
    }

    /**
     * @return string $dataFolder
     */
    private function getDataFolder(): string {
        return $this->plugin->getDataFolder();
    }
}
