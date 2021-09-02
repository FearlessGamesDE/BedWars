<?php

namespace BedWars\utils;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Snowball;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;

class TextEntity extends Snowball
{
	public function __construct(Vector3 $position, string $text)
	{
		parent::__construct(Location::fromObject($position, Server::getInstance()->getWorldManager()->getDefaultWorld()), null);
		$this->setImmobile();
		$this->setNameTag($text);
		$this->setNameTagVisible();
		$this->setNameTagAlwaysVisible();
		$this->setScale(0.000001);
	}

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(0, 0);
	}

	public function update(Player $player): void
	{
		unset($this->hasSpawned[spl_object_id($player)]);
		$this->spawnTo($player);
	}

	public function move(float $dx, float $dy, float $dz): void
	{
	}

	protected function initEntity(CompoundTag $nbt): void
	{
	}

	public function canCollideWith(Entity $entity): bool
	{
		return false;
	}

	public function onNearbyBlockChange(): void
	{
	}
}