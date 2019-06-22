<?php


namespace Fludixx\Bedwars\ranking;


use Fludixx\Bedwars\Bedwars;
use pocketmine\Player;
use pocketmine\utils\Config;

class JsonStats implements StatsInterface
{

    private $config;

    /**
     * JsonStats constructor.
     * Going to rewrite the stats system when i find time
     */
    public function __construct()
    {
        $this->config = new Config(Bedwars::getInstance()->getDataFolder()."/stats.json", Config::JSON);
    }

    /**
     * @param Player $player
     * @param string $key
     * @param        $value
     * @return mixed
     */
    public function set(Player $player, string $key, $value)
    {
        $this->config->setNested($player->getName() . ".$key", $value);
        $this->config->save();
    }

    /**
     * @param Player $player
     * @param string $key
     * @return mixed
     */
    public function get(Player $player, string $key)
    {
        return $this->config->getNested($player->getName() . ".$key");
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function register(Player $player)
    {
        $this->config->set($player->getName(), []);
        $this->config->save();
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function isRegistered(Player $player): bool
    {
        return $this->config->exists($player->getName());
    }

    /**
     * @param Player $player
     * @return array
     */
    public function getAll(Player $player): array
    {
        return $this->config->get($player->getName());
    }
}