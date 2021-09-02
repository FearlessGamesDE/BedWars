<?php

namespace BedWars\shop\item;

use pocketmine\block\BlockIds;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Builder extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(BlockIds::BRICK_BLOCK), ItemFactory::get(ItemIds::IRON_INGOT, 0, 8), "Builder", "textures/blocks/brick");
	}
}