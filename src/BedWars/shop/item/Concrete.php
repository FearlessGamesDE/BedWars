<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Concrete extends TeamItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::IRON_INGOT), array_map(static function ($team) {
			return ItemFactory::get(ItemIds::CONCRETE, $team, 8);
		}, range(0, 15)), "Concrete");
	}
}