<?php

namespace BedWars\shop\item;

use pocketmine\item\VanillaItems;

class EnderPearl extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaItems::ENDER_PEARL(), VanillaItems::EMERALD()->setCount(2), "Ender Pearl", "textures/items/ender_pearl");
	}
}