<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Compass extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::COMPASS), ItemFactory::get(ItemIds::IRON_INGOT, 0, 16), "Compass", "textures/items/compass_item");
	}
}