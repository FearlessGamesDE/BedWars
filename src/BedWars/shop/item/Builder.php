<?php

namespace BedWars\shop\item;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class Builder extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaBlocks::BRICKS()->asItem(), VanillaItems::IRON_INGOT()->setCount(8), "Builder", "textures/blocks/brick");
	}
}