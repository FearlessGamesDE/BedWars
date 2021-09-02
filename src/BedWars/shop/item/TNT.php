<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class TNT extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::TNT), ItemFactory::get(ItemIds::IRON_INGOT, 0, 16), "TNT", "textures/blocks/tnt_side");
	}
}