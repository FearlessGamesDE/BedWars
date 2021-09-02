<?php

namespace BedWars\shop\item;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class TNT extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaBlocks::TNT()->asItem(), VanillaItems::IRON_INGOT()->setCount(16), "TNT", "textures/blocks/tnt_side");
	}
}