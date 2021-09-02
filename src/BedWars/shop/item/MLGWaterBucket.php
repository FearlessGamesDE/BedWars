<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class MLGWaterBucket extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::BUCKET, ItemIds::FLOWING_WATER), ItemFactory::get(ItemIds::IRON_INGOT, 0, 8), "MLG Water Bucket", "textures/items/bucket_water");
	}
}