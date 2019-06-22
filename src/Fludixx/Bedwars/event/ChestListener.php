<?php

/**
 * Bedwars - ChestListener.php
 * @author Fludixx
 * @license MIT
 */

namespace Fludixx\Bedwars\event;

use Fludixx\Bedwars\Bedwars;
use Fludixx\Bedwars\utils\Utils;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\Player;

class ChestListener
{

	public function onSelect(Player $player, Item $itemClickedOn, Item $itemClickedWith): bool {
		if($itemClickedOn->getId() == 35) {
			$team = Utils::ColorIntToTeamInt($itemClickedOn->getDamage());
			$teamname = Utils::ColorInt2Color($itemClickedOn->getDamage());
			$arena = Bedwars::$arenas[$player->getLevel()->getFolderName()];
			$maxTeamMembers = $arena->getPlayersProTeam();
			$playersInTeam = 0;
			$playersInOtherTeams = 0;
			foreach($player->getLevel()->getPlayers() as $p) {
				$pteam = Bedwars::$players[$p->getName()]->getTeam();
				if($pteam == $team) {
					$playersInTeam++;
				} else {
					$playersInOtherTeams++;
				}
			}
			if ($playersInTeam >= $maxTeamMembers) {
				$player->sendMessage(Bedwars::PREFIX . "Team $teamname is already full!");
				$join = false;
			} else {
				if ($playersInTeam == 0 && $playersInOtherTeams == 0) {
					$player->sendMessage(Bedwars::PREFIX . "You entered the $teamname Team!");
					$join = true;
                } elseif ($playersInTeam >= 1 && $playersInOtherTeams == 0) {
                    $player->sendMessage(Bedwars::PREFIX . "You can't join the $teamname Team");
                    $join = false;
				} elseif ($playersInTeam =! 0 && $playersInOtherTeams != 0 && $playersInTeam < $maxTeamMembers) {
					$player->sendMessage(Bedwars::PREFIX . "You joined the $teamname Team");
					$join = true;
				}
			}
			if($join) {
				Bedwars::$players[$player->getName()]->setTeam($team);
			}
            $packet = new ContainerClosePacket();
            $packet->windowId = 0;
            $player->dataPacket($packet);
        }
		return TRUE;
	}

}