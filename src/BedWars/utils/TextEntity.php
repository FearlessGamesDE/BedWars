<?php

namespace BedWars\utils;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Snowball;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\Player;
use pocketmine\Server;

class TextEntity extends Snowball
{
	public $width = 0.0;
	public $height = 0.0;

	public function __construct(Vector3 $position, string $text)
	{
		parent::__construct(Server::getInstance()->getDefaultLevel(), self::createBaseNBT($position));
		$this->setImmobile();
		$this->setNameTag($text);
		$this->setNameTagVisible();
		$this->setNameTagAlwaysVisible();
		$this->setScale(0.000001);
	}

	public function update(Player $player): void
	{
		unset($this->hasSpawned[$player->getLoaderId()]);
		$this->spawnTo($player);
	}

	public function move(float $dx, float $dy, float $dz): void
	{
	}

	protected function initEntity(): void
	{
	}

	public function canCollideWith(Entity $entity): bool
	{
		return false;
	}

	public function onNearbyBlockChange(): void
	{
	}

	public function sendData($player, ?array $data = null): void
	{
	}
}