<?php

namespace BedWars\shop\item;

use pocketmine\item\VanillaItems;

class Compass extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaItems::COMPASS(), VanillaItems::IRON_INGOT()->setCount(16), "Compass", "textures/items/compass_item");
	}
}