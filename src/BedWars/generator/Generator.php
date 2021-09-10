<?php

namespace BedWars\generator;

use BedWars\BedWars;
use BedWars\utils\TextEntity;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use UnexpectedValueException;

class Generator
{
	public const TYPE_IRON = 0;
	public const TYPE_EMERALD = 1;
	public const TYPE_DIAMONDS = 2;

	private int $type;
	private Position $position;
	private int $nextSpawn;
	private ItemEntity $entity;
	private ?TextEntity $next;

	/**
	 * Generator constructor.
	 * @param int      $type
	 * @param Position $position
	 */
	public function __construct(int $type, Position $position)
	{
		$this->type = $type;
		$this->position = Position::fromObject($position->add(0.5, 0, 0.5), $position->getWorld());
		(new TextEntity($this->position->add(0, 1.3, 0), $this->getName()))->spawnToAll();
		$this->nextSpawn = max(1, self::getRate($this->type));
		$this->next = match ($this->type) {
			self::TYPE_EMERALD, self::TYPE_DIAMONDS => new TextEntity($this->position->add(0, 1, 0), "Next: §e" . $this->nextSpawn . "s"),
			self::TYPE_IRON => null
		};
		GeneratorManager::add($this);
	}

	public function tick(): void
	{
		if (--$this->nextSpawn <= 0) {
			$this->nextSpawn = max(1, self::getRate($this->type));
			$count = (int) max(1, 1 / self::getRate($this->type));
			if (isset($this->entity) && !$this->entity->isClosed()) {
				if ($this->entity->getItem()->getCount() + $count <= self::getMax($this->type)) {
					$this->entity->getItem()->setCount($this->entity->getItem()->getCount() + $count);
				}
				$this->entity->respawnToAll(); //No despawning, update count
			} else {
				$this->entity = new ItemEntity(Location::fromObject($this->position, $this->position->getWorld(), lcg_value() * 360), self::getMaterial($this->type)->setCount($count));
				$this->entity->setDespawnDelay(ItemEntity::NEVER_DESPAWN);
				$this->entity->setMotion(new Vector3(0, 0.2, 0));
				$this->entity->spawnToAll();
			}
		}

		if ($this->next !== null) {
			$this->next->setNameTag("Next: §e" . $this->nextSpawn . "s");
			$this->next->respawnToAll();
		}
	}

	/**
	 * @param int $type
	 * @return int
	 */
	private static function getRate(int $type): int
	{
		switch ($type) {
			case self::TYPE_IRON:
				return floor(16 / BedWars::getTeamSize());
			case self::TYPE_EMERALD:
				return 90;
			case self::TYPE_DIAMONDS:
				return 30;
		}
		throw new UnexpectedValueException("Invalid Generator type " . $type);
	}

	/**
	 * @param int $type
	 * @return int
	 */
	private static function getMax(int $type): int
	{
		switch ($type) {
			case self::TYPE_IRON:
				return 64;
			case self::TYPE_EMERALD:
				return 4;
			case self::TYPE_DIAMONDS:
				return 8;
		}
		throw new UnexpectedValueException("Invalid Generator type " . $type);
	}

	/**
	 * @param int $type
	 * @return Item
	 */
	private static function getMaterial(int $type): Item
	{
		switch ($type) {
			case self::TYPE_IRON:
				return VanillaItems::IRON_INGOT();
			case self::TYPE_EMERALD:
				return VanillaItems::EMERALD();
			case self::TYPE_DIAMONDS:
				return VanillaItems::DIAMOND();
		}
		throw new UnexpectedValueException("Invalid Generator type " . $type);
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		switch ($this->type) {
			case self::TYPE_IRON:
				return "§l§7Iron Generator§r";
			case self::TYPE_EMERALD:
				return "§l§2Emerald Generator§r";
			case self::TYPE_DIAMONDS:
				return "§l§bDiamond Generator§r";
		}
		throw new UnexpectedValueException("Invalid Generator type " . $this->type);
	}
}