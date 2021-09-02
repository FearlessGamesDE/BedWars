<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;

class GoldenApple extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaItems::GOLDEN_APPLE(), VanillaItems::IRON_INGOT()->setCount(16), "Golden Apple", "textures/items/apple_golden");
	}
}