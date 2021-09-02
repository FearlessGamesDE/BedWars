<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class JumpBoostPotion extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::POTION, 11), ItemFactory::get(ItemIds::EMERALD), "Jump Boost Potion", "textures/items/potion_bottle_jump");
	}
}