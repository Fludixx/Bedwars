<?php

declare(strict_types=1);

/**
 * Bedwars - TakeItemListener.php
 * @author Fludixx
 * @license MIT
 */

namespace Fludixx\Bedwars\event;

use Fludixx\Bedwars\Bedwars;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;

/**
 * Class TakeItemListener
 * @package Fludixx\Bedwars\event
 * This class gets the real dropped amount of an item, on big rounds there can be spawned a lot of materials
 * thats why we only spawn 1 and give than more if its collected
 */
class TakeItemListener implements Listener
{

    public function onPlayerTakeItem(InventoryPickupItemEvent $event)
    {
        if ($event->getItem()->getItem()->getCustomName() === "BRICK" or $event->getItem()->getItem()->getCustomName() === "IRON" or
            $event->getItem()->getItem()->getCustomName() === "GOLD") {
            $pos = $event->getItem()->getPosition()->asVector3();
            $pos->y -= 2;
            $tile = $event->getItem()->getLevel()->getTile($pos);
            if ($tile instanceof Sign) {
                $arena = Bedwars::$arenas[$event->getItem()->getLevel()->getFolderName()];
                $pos = $tile->asVector3();
                $id = $pos->x . $pos->y . $pos->z;
                while($arena->drops_count[$id] > 64) {
                    $arena->drops_count[$id] -= 64;
                    $event->getInventory()->addItem(Item::get($event->getItem()->getItem()->getId(), 0, 64));
                }
                $event->getInventory()->addItem(Item::get($event->getItem()->getItem()->getId(), 0, $arena->drops_count[$id]));
                $arena->drops_count[$id] = 0;
                $event->setCancelled(TRUE);
                $event->getItem()->getLevel()->dropItem($event->getItem()->asVector3(), $event->getItem()->getItem(), new Vector3(0, 0, 0));
                $event->getItem()->kill();
            }
        }
    }
}