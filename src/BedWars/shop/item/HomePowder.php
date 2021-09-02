<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class HomePowder extends BedWarsItem
{
	public function __construct()
	{
		$powder = ItemFactory::get(ItemIds::GLOWSTONE_DUST);
		$powder->setCustomName("Home Powder");
		parent::__construct($powder, ItemFactory::get(ItemIds::IRON_INGOT, 0, 32), "Home Powder", "textures/items/glowstone_dust");
	}
}