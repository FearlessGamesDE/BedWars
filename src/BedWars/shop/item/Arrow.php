<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Arrow extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::ARROW, 0, 16), ItemFactory::get(ItemIds::IRON_INGOT, 0, 32), "Arrow", "textures/items/arrow");
	}
}