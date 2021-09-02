<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class SpeedPotion extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::POTION, 16), ItemFactory::get(ItemIds::EMERALD), "Speed Potion", "textures/items/potion_bottle_moveSpeed");
	}
}