<?php

namespace BedWars\shop;

use BedWars\Messages;
use BedWars\shop\item\BedWarsItem;
use BedWars\shop\item\ItemManager;
use BedWars\shop\item\PermanentBedWarsItem;
use BedWars\utils\TextEntity;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\math\Vector3;

class Utility
{
	/**
	 * @var BedWarsItem[]
	 */
	private static $items = [];
	/**
	 * @var Vector3
	 */
	private $position;

	public static function addItem(BedWarsItem $item): void
	{
		ItemManager::register($item);
		self::$items[] = $item;
	}

	/**
	 * Utility constructor.
	 * @param Vector3 $position
	 */
	public function __construct(Vector3 $position)
	{
		$this->position = $position->add(0.5, 0, 0.5);
		(new TextEntity($this->position->add(0, 1), "§eUtility Shop"))->spawnToAll();
	}

	public function load(Level $level): void
	{
		$level->setBlock($this->position, BlockFactory::get(BlockIds::STONECUTTER));
	}

	/**
	 * @param Player $player
	 */
	public function use(Player $player): void
	{
		$form = new SimpleForm(function (Player $player, ?int $data = null) {
			if ($data !== null) {
				$item = self::$items[$data];
				if ($player->getInventory()->contains($item->getBaseCost())) {
					$player->getInventory()->removeItem($item->getBaseCost());
					$prev = $player->getInventory()->getItem($player->getInventory()->getHeldItemIndex());
					if (ItemManager::get($prev->getName()) instanceof PermanentBedWarsItem) {
						foreach ($player->getInventory()->addItem($item->getItem($player)) as $i) {
							$player->getLevel()->dropItem($player, $i);
						}
					} else {
						$player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $item->getItem($player));
						foreach ($player->getInventory()->addItem($prev) as $i) {
							$player->getLevel()->dropItem($player, $i);
						}
					}
					$pk = new PlaySoundPacket();
					$pk->soundName = "note.bell";
					$pk->pitch = 1;
					$pk->volume = 100;
					$pk->x = $player->getX();
					$pk->y = $player->getY();
					$pk->z = $player->getZ();
					$player->dataPacket($pk);
					Messages::send($player, "bought", ["{item}" => $item->getItem($player)->getName(), "{cost}" => Shop::getCost($item->getBaseCost())]);
				} else {
					Messages::send($player, "not-enough");
				}
				$this->use($player);
			}
		});
		$form->setTitle("§eUtility Shop");
		$form->setContent(Messages::translate("utility"));
		foreach (self::$items as $item) {
			if ($player->getInventory()->contains($item->getBaseCost())) {
				$form->addButton($item->getRawItem()->getCount() . " " . $item->getName() . "\n" . Shop::getCost($item->getBaseCost()), 0, $item->getImage());
			} else {
				$form->addButton("§c" . $item->getRawItem()->getCount() . " " . $item->getName() . "\n" . Shop::getCost($item->getBaseCost()), 0, $item->getImage());
			}
		}
		$player->sendForm($form);
	}
}