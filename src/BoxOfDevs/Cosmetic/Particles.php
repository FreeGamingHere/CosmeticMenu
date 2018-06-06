<?php

namespace BoxOfDevs\Cosmetic;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task as PluginTask;

class Particles extends PluginTask {
    public function __construct(Main $plugin) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }
    public function onRun($tick) {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            $level = $player->getLevel();
            if (in_array($name, $this->plugin->water)) {
                $particle = new \pocketmine\level\particle\WaterParticle(new Vector3($player->x, $player->y + 2.5, $player->z), 5);
                $level->addParticle($particle);
            } elseif (in_array($name, $this->plugin->fire)) {
                $particle = new \pocketmine\level\particle\EntityFlameParticle(new Vector3($player->x, $player->y + 2.5, $player->z));
                $level->addParticle($particle);
            } elseif (in_array($name, $this->plugin->heart)) {
                $particle = new \pocketmine\level\particle\HeartParticle(new Vector3($player->x, $player->y + 2.5, $player->z), 5);
                $level->addParticle($particle);
            } elseif (in_array($name, $this->plugin->smoke)) {
                $particle = new \pocketmine\level\particle\HugeExplodeParticle(new Vector3($player->x, $player->y + 2.5, $player->z));
                $level->addParticle($particle);
            }
        }
    }
}
