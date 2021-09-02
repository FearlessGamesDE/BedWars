<?php

namespace BedWars\shop\item;

use pocketmine\item\VanillaItems;

class MLGWaterBucket extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaItems::WATER_BUCKET(), VanillaItems::IRON_INGOT()->setCount(8), "MLG Water Bucket", "textures/items/bucket_water");
	}
}