<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Wood extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::WOODEN_PLANKS, 0, 8), ItemFactory::get(ItemIds::IRON_INGOT, 0, 2), "Wood");
	}
}