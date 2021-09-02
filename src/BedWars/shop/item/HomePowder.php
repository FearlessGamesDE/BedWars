<?php

namespace BedWars\shop\item;

use pocketmine\item\VanillaItems;

class HomePowder extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaItems::GLOWSTONE_DUST()->setCustomName("Home Powder"), VanillaItems::IRON_INGOT()->setCount(32), "Home Powder", "textures/items/glowstone_dust");
	}
}