<?php

namespace BedWars\utils;

use pocketmine\item\Item;
use pocketmine\Player;

class Bed extends \pocketmine\block\Bed
{
	public function onActivate(Item $item, Player $player = null) : bool{
		return false;
	}
}