<?php

namespace BedWars\generator;

use BedWars\team\TeamManager;
use BedWars\utils\TextEntity;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use UnexpectedValueException;

class Generator
{
	public const TYPE_IRON = 0;
	public const TYPE_EMERALD = 1;
	public const TYPE_DIAMONDS = 2;

	/**
	 * @var int
	 */
	private $type;
	/**
	 * @var Position
	 */
	private $position;
	/**
	 * @var int
	 */
	private $nextSpawn;
	/**
	 * @var ItemEntity
	 */
	private $entity;
	/**
	 * @var TextEntity
	 */
	private $next;

	/**
	 * Generator constructor.
	 * @param int $type
	 * @param Position $position
	 */
	public function __construct(int $type, Position $position)
	{
		$this->type = $type;
		$this->position = Position::fromObject($position->add(0.5, 0, 0.5), $position->getLevel());
		(new TextEntity($this->position->add(0, 1.3), $this->getName()))->spawnToAll();
		switch ($this->type) {
			case self::TYPE_EMERALD:
			case self::TYPE_DIAMONDS:
				$this->next = new TextEntity($this->position->add(0, 1), "Next: §e" . $this->nextSpawn . "s");
		}
		GeneratorManager::add($this);
		$this->nextSpawn = max(1, self::getRate($this->type));
	}

	public function tick(): void
	{
		if (--$this->nextSpawn <= 0) {
			$this->nextSpawn = max(1, self::getRate($this->type));
			$count = (int)max(1, 1 / self::getRate($this->type));
			if (isset($this->entity) && !$this->entity->isClosed()) {
				if ($this->entity->getItem()->count + $count <= self::getMax($this->type)) {
					$this->entity->getItem()->count += $count;
				}
				$this->entity->respawnToAll(); //No despawning, update count
			} else {
				$nbt = Entity::createBaseNBT($this->position, new Vector3(0, 0.2, 0), lcg_value() * 360, 0);
				$nbt->setShort("Health", 5);
				$nbt->setShort("PickupDelay", 0);
				$nbt->setShort("Age", -0x8000);
				$itemTag = ItemFactory::get(self::getMaterial($this->type), 0, $count)->nbtSerialize();
				$itemTag->setName("Item");
				$nbt->setTag($itemTag);
				$this->entity = new ItemEntity($this->position->getLevel(), $nbt);
				$this->entity->spawnToAll();
			}
		}

		if (isset($this->next)) {
			$this->next->setNameTag("Next: §e" . $this->nextSpawn . "s");
			$this->next->respawnToAll();
		}
	}

	/**
	 * @param int $type
	 * @return float
	 */
	private static function getRate(int $type)
	{
		switch ($type) {
			case self::TYPE_IRON:
				return 16 / TeamManager::$teamSize;
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
	 * @return int
	 */
	private static function getMaterial(int $type): int
	{
		switch ($type) {
			case self::TYPE_IRON:
				return ItemIds::IRON_INGOT;
			case self::TYPE_EMERALD:
				return ItemIds::EMERALD;
			case self::TYPE_DIAMONDS:
				return ItemIds::DIAMOND;
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