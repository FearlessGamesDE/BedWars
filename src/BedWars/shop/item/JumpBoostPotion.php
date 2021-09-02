<?php

namespace BedWars\shop\item;

use pocketmine\item\VanillaItems;

class JumpBoostPotion extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaItems::STRONG_LEAPING_POTION(), VanillaItems::EMERALD(), "Jump Boost Potion", "textures/items/potion_bottle_jump");
	}
}