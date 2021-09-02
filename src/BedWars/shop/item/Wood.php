<?php

namespace BedWars\shop\item;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class Wood extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaBlocks::OAK_PLANKS()->asItem()->setCount(8), VanillaItems::IRON_INGOT()->setCount(2), "Wood");
	}
}