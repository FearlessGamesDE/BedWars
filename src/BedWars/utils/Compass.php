<?php

namespace BedWars\utils;

use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Server;

class Compass
{
	public static function update(): void
	{
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			$bedWarsPlayer = PlayerManager::get($player->getName());
			$nearest = null;
			foreach (Server::getInstance()->getOnlinePlayers() as $p) {
				if (($pl = PlayerManager::get($p->getName()))->getStatus() === BedWarsPlayer::ALIVE && $pl->getTeam() !== $bedWarsPlayer->getTeam()) {
					if ($nearest === null || $nearest->getPosition()->distance($player->getPosition()) > $p->getPosition()->distance($player->getPosition())) {
						$nearest = $p;
					}
				}
			}
			$pk = new SetSpawnPositionPacket();
			$pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
			if ($nearest === null) {
				$pk->x = $pk->x2 = 0;
				$pk->y = $pk->y2 = 100;
				$pk->z = $pk->z2 = 0;
				$pk->dimension = DimensionIds::NETHER;
			}else{
				$pk->x = $pk->x2 = $nearest->getPosition()->getFloorX();
				$pk->y = $pk->y2 = 100;
				$pk->z = $pk->z2 = $nearest->getPosition()->getFloorZ();
				$pk->dimension = DimensionIds::OVERWORLD;
			}
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}
}