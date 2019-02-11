<?php

/**
 * Bedwars - BlockEventListener.php
 * @author  Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\event;

use Fludixx\Bedwars\Bedwars;
use Fludixx\Bedwars\utils\Utils;
use pocketmine\block\SignPost;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\EntityFlameParticle;
use pocketmine\tile\Bed;
use pocketmine\tile\Sign;

class BlockEventListener implements Listener
{

    public function placeBlock(BlockPlaceEvent $event)
    {
        $player = Bedwars::$players[$event->getPlayer()->getName()];
        $pos = $player->getPos();
        if ($pos < 0 and !($pos < -9)) {
            $event->setCancelled(TRUE);
            $levelname = $player->getPlayer()->getLevel()->getFolderName();
            $spawnid = abs($pos);
            $player->getPlayer()->getInventory()->setItem(0, Item::get(35, Utils::teamIntToColorInt($spawnid + 1)));
            $arenadata = Bedwars::$provider->getArena($levelname);
            $arenadata['spawns']["$spawnid"]['x'] = $event->getBlock()->getX();
            $arenadata['spawns']["$spawnid"]['y'] = $event->getBlock()->getY();
            $arenadata['spawns']["$spawnid"]['z'] = $event->getBlock()->getZ();
            Bedwars::$provider->addArena($levelname, $arenadata);
            $player->sendMsg("Du hast den Spawn von " . Utils::teamIntToColorInt($spawnid) . " gesetzt! (Nächstes: " . Utils::ColorInt2Color(Utils::teamIntToColorInt
                ($spawnid + 1)) . ")");
            $player->setPos($pos - 1);
        } else if ($pos === 0) {
            $event->setCancelled(!$player->canBuild());
        } else {
            if (!in_array($event->getBlock()->getId(), Bedwars::BLOCKS))
                $event->setCancelled(TRUE);
            $pos = $event->getBlock()->asVector3();
            $pos->y -= 2;
            $tile = $event->getBlock()->getLevel()->getTile($pos);
            if ($tile instanceof Sign) {
                $player->sendMsg("Du kannst auf keine Generatoren blöcke platzieren!");
                $event->setCancelled(TRUE);
            }
        }
    }

    public function blockBreak(BlockBreakEvent $event)
    {
        $player = Bedwars::$players[$event->getPlayer()->getName()];
        $pos = $player->getPos();
        if ($pos === 0) {
            $event->setCancelled(!$player->canBuild());
        } else if ($pos < 0) {
            $event->setCancelled(TRUE);
            $event->getBlock()->getLevel()->addParticle(new DestroyBlockParticle($event->getBlock()->asVector3(), $event->getBlock()));
            if ($pos === -11 and $event->getBlock() instanceof SignPost) {
                $sign = $event->getBlock()->getLevel()->getTile($event->getBlock()->asVector3());
                if ($sign instanceof Sign) {
                    $event->setCancelled(TRUE);
                    $sign->setText(Bedwars::NAME,
                        $player->getKnocker(),
                        "§a? §7/ §c" . (Bedwars::$arenas[$player->getKnocker()]->getPlayersProTeam() *
                            Bedwars::$arenas[$player->getKnocker()]->getTeams()), "???");
                    $player->setPos(0);
                }
            }
        } else if ($pos > 0) {
            $tile = $event->getBlock()->getLevel()->getTile($event->getBlock()->asVector3());
            if ($tile instanceof Bed) {
                $color = $tile->getColor();
                $team = Utils::ColorIntToTeamInt($color);
                if ($team === $pos) {
                    $event->setCancelled(TRUE);
                    $player->sendMsg("Du kannst nicht dein eigenes Bett abbauen!");
                    $player->setVaule("ttbb", $player->getVaule("ttbb") + 1);
                    if ((int)$player->getVaule("ttbb") > 5) {
                        $player->sendMsg("JUNGE! DU KANNST ES NICHT!!!!11");
                        $player->setVaule("ttbb", 0);
                    }
                } else {
                    Bedwars::$statsSystem->set($player->getPlayer(), 'beds', (int)Bedwars::$statsSystem->get($player->getPlayer(), 'beds') + 1);

                    Bedwars::$arenas[$player->getPlayer()->getLevel()->getFolderName()]->destroyBed($team);
                    $event->setDrops([]);
                    $tile->getLevel()->addParticle(new EntityFlameParticle($tile->asVector3()));
                    $tile->getLevel()->addParticle(new DustParticle($tile->asVector3(), 255, 255, 255));
                }
            } else if (!in_array($event->getBlock()->getId(), Bedwars::BLOCKS))
                $event->setCancelled(TRUE);
        }
    }

}
