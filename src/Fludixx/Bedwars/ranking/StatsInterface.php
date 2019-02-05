<?php

declare(strict_types=1);

/**
 * Bedwars - StatsInterface.php
 * @author Fludixx
 * @license MIT
 */

namespace Fludixx\Bedwars\ranking;

use pocketmine\Player;

interface StatsInterface {

    /**
     * @param Player $player
     * @param string $key
     * @param        $value
     * @return mixed
     */
    public function set(Player $player, string $key, $value);

    /**
     * @param Player $player
     * @param string $key
     * @return mixed
     */
    public function get(Player $player, string $key);

    /**
     * @param Player $player
     * @return mixed
     */
    public function register(Player $player);

    /**
     * @param Player $player
     * @return bool
     */
    public function isRegistered(Player $player) : bool;

    /**
     * @param Player $player
     * @return array
     */
    public function getAll(Player $player) : array;

}