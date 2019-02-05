<?php

declare(strict_types=1);

/**
 * Bedwars - JsonStats.php
 * @author Fludixx
 * @license MIT
 */

namespace Fludixx\Bedwars\ranking;

use Fludixx\Bedwars\Bedwars;
use Fludixx\Bedwars\task\LoadStatsTask;
use Fludixx\Bedwars\utils\PlayerId;
use pocketmine\Player;
use pocketmine\utils\Config;

class JsonStats implements StatsInterface {

    protected $registered = [];
    public $stats = [];
    public $config;

    public function __construct()
    {
        @mkdir("/home/bedwars");
        $this->config = new Config("/home/bedwars/stats.json");
        Bedwars::getInstance()->getLogger()->notice("Loading player stats into memory...");
        Bedwars::getInstance()->getServer()->getAsyncPool()->submitTask(new LoadStatsTask($this, LoadStatsTask::JSON, "/home/bedwars/stats.json"));
    }

    public function set(Player $player, string $key, $value)
    {
        $id = new PlayerId($player->getName());
        $this->stats[$id->__toString()][$key] = $value;
    }

    public function get(Player $player, string $key)
    {
        $id = new PlayerId($player->getName());
        return $this->stats[$id->__toString()][$key];
    }

    public function register(Player $player)
    {
        $id = new PlayerId($player->getName());
        $this->stats[$id->__toString()] = [];
    }

    public function isRegistered(Player $player): bool
    {
        $id = new PlayerId($player->getName());
        return isset($this->stats[$id->__toString()]);
    }

    public function getAll(Player $player): array
    {
        $id = new PlayerId($player->getName());
        return $this->stats[$id->__toString()];
    }
}