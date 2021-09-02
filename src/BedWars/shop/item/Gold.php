<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Gold extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::GOLD_BLOCK, 0, 8), ItemFactory::get(ItemIds::IRON_INGOT, 0, 6), "Gold");
	}
}