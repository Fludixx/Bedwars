<?php

/**
 * Bedwars - SignTask.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\task;

use Fludixx\Bedwars\Arena;
use Fludixx\Bedwars\Bedwars;
use pocketmine\scheduler\Task;
use pocketmine\tile\Sign;

class SignTask extends Task {

    /**
     * @param int $currentTick
     * This functions refreshes all the signs without this task running no sign will get updated what would break the Server
     */
	public function onRun(int $currentTick)
	{
		$level = Bedwars::getInstance()->getServer()->getDefaultLevel();
		foreach ($level->getTiles() as $tile) {
			if($tile instanceof Sign and $tile->getLine(0) === Bedwars::NAME) {
				$levelname = $tile->getLine(1);
				try {
                    $arena = Bedwars::$arenas[$levelname];
                    $players = count($arena->getPlayers());
                    if ($players < ((int)$arena->getPlayersProTeam() * (int)$arena->getTeams())) {
                        $state = $arena->getState() === Arena::STATE_OPEN ? Bedwars::JOIN : Bedwars::RUNNING;
                    } else {
                        $state = Bedwars::FULL;
                    }
                    if($arena->getState() === Arena::STATE_INUSE) {
                        $state = Bedwars::RUNNING;
                    }
                    $tile->setLine(3, $state);
                    $tile->setLine(2, "§a$players §7/ §c" . ((int)$arena->getPlayersProTeam() * (int)$arena->getTeams()));
                } catch (\ErrorException $ex) {
				    $tile->setText("Invalid Sign", "", "", "");
                }
			}
		}
	}

}