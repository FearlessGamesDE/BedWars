<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class EnderPearl extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::ENDER_PEARL), ItemFactory::get(ItemIds::EMERALD, 0, 2), "Ender Pearl", "textures/items/ender_pearl");
	}
}