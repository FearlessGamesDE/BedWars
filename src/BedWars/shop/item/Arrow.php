<?php

namespace BedWars\shop\item;

use pocketmine\item\VanillaItems;

class Arrow extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaItems::ARROW()->setCount(16), VanillaItems::IRON_INGOT()->setCount(32), "Arrow", "textures/items/arrow");
	}
}