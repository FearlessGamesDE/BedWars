<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Ladder extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::LADDER, 0, 8), ItemFactory::get(ItemIds::IRON_INGOT, 0, 4), "Ladder", "textures/blocks/ladder");
	}
}