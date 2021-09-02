<?php

namespace BedWars;

use BedWars\shop\item\ItemManager;
use BedWars\shop\ShopManager;
use BedWars\team\TeamManager;
use BedWars\utils\Bed;
use BedWars\utils\NumberGenerator;
use pocketmine\block\BlockFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class Loader extends PluginBase
{
	private static Loader $instance;

	public function onLoad(): void
	{
		BlockFactory::registerBlock(new Bed(), true);
	}

	/** @noinspection PhpUnusedParameterInspection */
	public function onEnable(): void
	{
		self::$instance = $this;
		$level = Server::getInstance()->getDefaultLevel();
		if ($level instanceof Level) {
			$level->setTime(Level::TIME_NOON);
			$level->stopTime();
		}
		self::$id = str_replace(PHP_EOL, "", file("/id.txt")[0]);
		$data = explode("_", self::getId());
		TeamManager::$teamCount = NumberGenerator::toInt($data[count($data) - 2]);
		TeamManager::$teamSize = NumberGenerator::toInt($data[count($data) - 1]);
		Messages::load($this->getDataFolder());
		Stats::load();
		ItemManager::load();
		self::scanMap();
		ShopManager::load();
		$this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
		$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick): void {
			$this->start();
		}), 20 * 12); //2 seconds to load world (10 for Queue Buffer)
	}
}