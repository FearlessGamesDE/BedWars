<?php

namespace BedWars;

use BedWars\shop\item\ItemManager;
use BedWars\shop\ShopManager;
use BedWars\utils\Bed;
use LobbySystem\server\VirtualServer;
use pocketmine\block\BlockFactory;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase
{
	private static Loader $instance;

	public function onLoad(): void
	{
		BlockFactory::getInstance()->register(new Bed(), true);
	}

	public function onEnable(): void
	{
		self::$instance = $this;
		VirtualServer::register(new BedWars());
		Messages::load($this->getDataFolder());
		Stats::load();
		ItemManager::load();
		BedWars::scanMap();
		ShopManager::load();
		$this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
	}

	/**
	 * @return Loader
	 */
	public static function getInstance(): Loader
	{
		return self::$instance;
	}
}