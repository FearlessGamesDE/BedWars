<?php

namespace BedWars\shop;

use BedWars\Messages;
use BedWars\shop\item\ItemManager;
use BedWars\shop\item\TieredBedWarsItem;
use BedWars\utils\TextEntity;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\math\Vector3;

class Upgrader
{
	/**
	 * @var Vector3
	 */
	private $position;
	/**
	 * @var bool
	 */
	private $armor;
	/**
	 * @var TextEntity
	 */
	private $item;
	/**
	 * @var TextEntity
	 */
	private $cost;

	/**
	 * Shop constructor.
	 * @param Vector3 $position
	 * @param string $type
	 */
	public function __construct(Vector3 $position, string $type)
	{
		$this->position = $position->add(0.5, 0, 0.5);
		$this->armor = $type === "ARMOR";
		(new TextEntity($this->position->add(0, 1.3), $this->armor ? "§eArmor Upgrader" : "§eTool Upgrader"))->spawnToAll();
		$this->item = new TextEntity($this->position->add(0, 1), "");
		$this->cost = new TextEntity($this->position->add(0, 0.7), "");
	}

	public function load(Level $level): void
	{
		$level->setBlock($this->position, BlockFactory::get($this->armor ? BlockIds::ENCHANTING_TABLE : BlockIds::ANVIL));
	}

	/**
	 * @param Player $player
	 */
	public function use(Player $player): void
	{
		if ($this->armor) {
			if (Armor::getTierOf($player->getName()) < count(Armor::getTiers()) - 1) {
				if ($player->getInventory()->contains(Armor::getCost($player->getName()))) {
					$player->getInventory()->removeItem(Armor::getCost($player->getName()));
					$pk = new PlaySoundPacket();
					$pk->soundName = "random.anvil_use";
					$pk->pitch = 1;
					$pk->volume = 1;
					$pk->x = $this->position->getX();
					$pk->y = $this->position->getY();
					$pk->z = $this->position->getZ();
					$player->dataPacket($pk);
					Messages::send($player, "upgraded", ["{item}" => "Armor", "{cost}" => Shop::getCost(Armor::getCost($player->getName()))]);
					Armor::upgrade($player);
					ShopManager::upgraderTick($player);
				} else {
					Messages::send($player, "not-enough");
				}
			} else {
				Messages::send($player, "maxed");
			}
		} else {
			$item = ItemManager::get($player->getInventory()->getItemInHand()->getName());
			if ($item instanceof TieredBedWarsItem) {
				if ($item->getTierOf($player) < count($item->getTiers()) - 1) {
					if ($player->getInventory()->contains($item->getCost($player))) {
						$player->getInventory()->removeItem($item->getCost($player));
						$pk = new PlaySoundPacket();
						$pk->soundName = "random.anvil_use";
						$pk->pitch = 1;
						$pk->volume = 1;
						$pk->x = $this->position->getX();
						$pk->y = $this->position->getY();
						$pk->z = $this->position->getZ();
						$player->dataPacket($pk);
						Messages::send($player, "upgraded", ["{item}" => $item->getName(), "{cost}" => Shop::getCost($item->getCost($player))]);
						$item->upgrade($player);
						ShopManager::upgraderTick($player);
					} else {
						Messages::send($player, "not-enough");
					}
				} else {
					Messages::send($player, "maxed");
				}
			} else {
				Messages::send($player, "not-upgradeable");
			}
		}
	}

	/**
	 * @param Player $player
	 * @param int $slot
	 */
	public function tick(Player $player, int $slot): void
	{
		$this->item->setNameTag("");
		$this->cost->setNameTag("");
		$item = ItemManager::get($player->getInventory()->getItem($slot)->getName());
		if ($this->armor) {
			if (Armor::getTierOf($player->getName()) < count(Armor::getTiers()) - 1) {
				$this->item->setNameTag("Upgrade Armor for");
				$this->cost->setNameTag(Shop::getCost(Armor::getCost($player->getName())));
			} else {
				$this->item->setNameTag("§cMax Level");
			}
		} elseif ($item instanceof TieredBedWarsItem) {
			if ($item->getTierOf($player, $slot) < count($item->getTiers()) - 1) {
				$this->item->setNameTag("Upgrade " . $item->getName() . " for");
				$this->cost->setNameTag(Shop::getCost($item->getCost($player, $slot)));
			} else {
				$this->item->setNameTag("§cMax Level");
			}
		}

		$this->item->update($player);
		$this->cost->update($player);
	}
}