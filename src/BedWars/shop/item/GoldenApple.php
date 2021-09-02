<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class GoldenApple extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::GOLDEN_APPLE), ItemFactory::get(ItemIds::IRON_INGOT, 0, 16), "Golden Apple", "textures/items/apple_golden");
	}
}