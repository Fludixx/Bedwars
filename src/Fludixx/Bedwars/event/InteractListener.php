<?php

/**
 * Bedwars - InteractListener.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\event;

use Fludixx\Bedwars\Bedwars;
use Fludixx\Bedwars\utils\Utils;
use muqsit\invmenu\InvMenu;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\tile\Sign;

class InteractListener implements Listener {

	public function onInteract(PlayerInteractEvent $event) {
		$mplayer = Bedwars::$players[$event->getPlayer()->getName()];
		$tile = $event->getBlock()->getLevel()->getTile($event->getBlock()->asVector3());
		if($mplayer->getPos() === 0 and $tile instanceof Sign and $tile->getLine(0) === Bedwars::NAME) {
			if($tile->getLine(3) === Bedwars::JOIN) {
				$mplayer->sendMsg("Teleportiere...");
				$arena = Bedwars::$arenas[$tile->getLine(1)];
				if(count($arena->getPlayers()) < ($arena->getTeams() * $arena->getPlayersProTeam())) {
                    rndTeam:
                    $randomteam = mt_rand(1, $arena->getTeams());
                    $tc = 0;
                    foreach ($arena->getPlayers() as $p) {
                        if (Bedwars::$players[$p->getName()]->getTeam() === $randomteam)
                            $tc++;
                    }
                    if ($tc >= $arena->getPlayersProTeam()) goto rndTeam;
                    $mplayer->setTeam($randomteam);
                    $mplayer->saveTeleport($arena->getLevel()->getSafeSpawn());
                    $inv = $mplayer->getPlayer()->getInventory();
                    $inv->clearAll();
                    $inv->setItem(8, Item::get(Item::CHEST)->setCustomName("§eTeams"));
                    $inv->setItem(7, Item::get(Item::SLIME_BALL)->setCustomName("§cLeave"));
                    $inv->setItem(0, Item::get(Item::REDSTONE)->setCustomName("§6Goldvote"));
                    $arena->broadcast("{$mplayer->getName()} ist beigetreten!");
                    return;
                }
			}
			$mplayer->sendMsg("Du kannst dieser Runde nicht beitreten!");
		}
		switch ($event->getItem()->getCustomName()) {
			case "§eTeams":
				$menu = InvMenu::create(InvMenu::TYPE_CHEST);
				$menu->readonly();
				$menu->setName("Wähle dein Team aus!");
				$minv = $menu->getInventory();
				$levelname = $mplayer->getPlayer()->getLevel()->getFolderName();
				$arena = Bedwars::$arenas[$levelname];
				$teams = [
					0 => [],	1 => [],
					2 => [],	3 => [],
					4 => [],	5 => [],
					6 => [],	7 => [],
					8 => []
				];
				foreach ($arena->getLevel()->getPlayers() as $player) {
					$mplayer = Bedwars::$players[$player->getName()];
					$teams[$mplayer->getTeam()][] = $mplayer->getName();
				}
				foreach ($arena->getBeds() as $team => $bed) {
					$playerss = "";
					foreach ($teams[$team] as $name) {
						$playerss .= "\n§7 - §f$name";
					}
					$minv->addItem(Item::get(Item::WOOL, Utils::teamIntToColorInt($team), count($teams[$team])+1)->setCustomName
					(Utils::ColorInt2Color(Utils::teamIntToColorInt($team))."§7 - §f".count($teams[$team]).$playerss));
				}
				$menu->send($event->getPlayer());
				$menu->setListener([new ChestListener(), "onSelect"]);
				break;
            case "§6Goldvote":
                $form = new ModalFormRequestPacket();
                $form->formId = 156;
                $form->formData = json_encode([
                    'title' => "Soll mit Gold gespielt werden?",
                    'type' => "form",
                    'content' => "",
                    'buttons' => [
                        0 => ['text' => "§aMit Gold!"],
                        1 => ['text' => "§cOhne Gold!"]
                    ]
                ]);
                $event->getPlayer()->sendDataPacket($form);
                break;
            case "§cLeave":
                Bedwars::getInstance()->getServer()->dispatchCommand($event->getPlayer(), "leave");
                break;
		}
	}

	public function ondataPacketRecive(DataPacketReceiveEvent $event) {
	    $p = $event->getPacket();
	    if($p instanceof ModalFormResponsePacket and $p->formId === 156) {
	        $action = json_decode($p->formData);
	        $mplayer = Bedwars::$players[$event->getPlayer()->getName()];
            if(!is_null($action)) {
                if ($action === 1) {
                    $mplayer->setForGold(FALSE);
                    $mplayer->sendMsg("Du hast §cFÜR KEIN§f Gold gestimmt!");
                } else {
                    $mplayer->setForGold(TRUE);
                    $mplayer->sendMsg("Du hast §aFÜR§f Gold gestimmt!");
                }
            }
        }
    }

}
