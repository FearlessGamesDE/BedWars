<?php

namespace BedWars;

use BedWars\player\PlayerManager;
use BedWars\team\TeamManager;
use BedWars\utils\TeamColor;
//use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use pocketmine\Server;

class ScoreboardHandler
{
	public static function update(): void
	{
		$teams = [];
		$own = [];
		foreach (TeamManager::getTeams() as $team) {
			if ($team->hasBed()) {
				$own[$team->getColor()] = "§l" . TeamColor::getChatFormat($team) . TeamColor::getName($team) . " §r- " . $team->getAlivePlayers();
				$teams[$team->getColor()] = TeamColor::getChatFormat($team) . TeamColor::getName($team) . " §7- " . $team->getAlivePlayers();
			} elseif ($team->getAlivePlayers() <= 0) {
				$own[$team->getColor()] = "§l§8" . TeamColor::getName($team) . " §r§8- " . $team->getAlivePlayers();
				$teams[$team->getColor()] = "§8" . TeamColor::getName($team) . " §8- " . $team->getAlivePlayers();
			} else {
				$own[$team->getColor()] = "§l" . TeamColor::getName($team) . " §r- " . $team->getAlivePlayers();
				$teams[$team->getColor()] = TeamColor::getName($team) . " §7- " . $team->getAlivePlayers();
			}
		}
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			$p = PlayerManager::get($player->getName());
			self::changeEntry($player, 15, "§7" . date("d.m.Y H:i"));
			self::setEntry($player, 14, "");
			foreach ($teams as $i => $team) {
				self::changeEntry($player, 13 - array_flip(array_keys($teams))[$i], ($p->getTeam() === $i ? $own[$i] : $team));
			}
			self::setEntry($player, 5, "");
			self::changeEntry($player, 4, "Kills: " . (Stats::$killCounter[$player->getName()] ?? 0));
			self::setEntry($player, 3, "");
			self::setEntry($player, 2, "§8Bedwars " . TeamManager::$teamCount . "x" . TeamManager::$teamSize);
			self::setEntry($player, 1, "§dFearless§fGames§7.de");
		}
	}

	/**
	 * @param Player $player
	 * @param int $score
	 * @param string $msg
	 */
	public static function changeEntry(Player $player, int $score, string $msg): void
	{
		self::removeEntry($player, $score);
		self::setEntry($player, $score, $msg);
	}

	/**
	 * @param Player $player
	 * @param int $score
	 * @param string $msg
	 */
	public static function setEntry(Player $player, int $score, string $msg): void
	{
		$entry = new ScorePacketEntry();
		$entry->objectiveName = "BedWars";
		$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
		$entry->customName = $msg . str_repeat("\0", $score);
		$entry->score = $score;
		$entry->scoreboardId = $score;

		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_CHANGE;
		$pk->entries = [$entry];
		$player->sendDataPacket($pk);
	}

	/**
	 * @param Player $player
	 * @param int $score
	 */
	public static function removeEntry(Player $player, int $score): void
	{
		$entry = new ScorePacketEntry();
		$entry->objectiveName = "BedWars";
		$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
		$entry->customName = "";
		$entry->score = $score;
		$entry->scoreboardId = $score;

		$pk = new SetScorePacket();
		$pk->entries = [$entry];
		$pk->type = SetScorePacket::TYPE_REMOVE;
		$player->sendDataPacket($pk);
	}

	/**
	 * @param Player $player
	 * @param string $title
	 */
	public static function create(Player $player, string $title): void
	{
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = "sidebar";
		$pk->objectiveName = "BedWars";
		$pk->displayName = $title;
		$pk->criteriaName = "dummy"; //Do not track anything
		$pk->sortOrder = 1;
		$player->sendDataPacket($pk);
	}

//	/**
//	 * @param Player $player
//	 * @deprecated
//	 */
//	public static function remove(Player $player): void
//	{
//		$pk = new RemoveObjectivePacket();
//		$pk->objectiveName = "BedWars";
//		$player->sendDataPacket($pk);
//	}
}