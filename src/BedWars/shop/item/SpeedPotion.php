<?php

namespace BedWars\shop\item;

use pocketmine\item\VanillaItems;

class SpeedPotion extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaItems::STRONG_SWIFTNESS_POTION(), VanillaItems::EMERALD(), "Speed Potion", "textures/items/potion_bottle_moveSpeed");
	}
}