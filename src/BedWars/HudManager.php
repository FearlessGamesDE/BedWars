<?php

namespace BedWars;

use alemiz\sga\StarGateAtlantis;
use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use BedWars\team\TeamManager;
use BedWars\utils\TeamColor;
use BedWars\libs\jojoe77777\FormAPI\SimpleForm;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;

class HudManager
{
	public static function send(Player $player): void
	{
		$player->getInventory()->setContents([
			0 => VanillaItems::COMPASS()->setCustomName("§6Teleporter"),
			7 => VanillaItems::PAPER()->setCustomName("§bNext Game"),
			8 => VanillaItems::RED_BED()->setCustomName("§cLeave Game")
		]);
	}

	public static function handle(Player $player): bool
	{
		if (($p = PlayerManager::get($player->getName()))->isPlayer() && $p->getStatus() === BedWarsPlayer::SPECTATOR) {
			switch ($player->getInventory()->getItemInHand()->getId()) {
				case ItemIds::COMPASS:
					$players = [];
					foreach (TeamManager::getTeams() as $team) {
						foreach ($team->getPlayers() as $p) {
							if ($p->getStatus() !== BedWarsPlayer::SPECTATOR) {
								$players[] = $p;
							}
						}
					}
					if ($players === []) {
						return true;
					}
					$form = new SimpleForm(static function (Player $player, ?int $data = null) use ($players) {
						if ($data !== null) {
							$p = $players[$data] ?? "";
							if ($p->isPlayer() && ($pl = Server::getInstance()->getPlayerExact($p->getName())) instanceof Player) {
								$player->teleport($pl->getPosition());
							}
						}
					});
					$form->setTitle("§6Teleporter");
					foreach ($players as $p) {
						$form->addButton(TeamColor::getChatFormat($p->getTeam()) . $p->getName());
					}
					$player->sendForm($form);
					break;
				case ItemIds::PAPER:
					StarGateAtlantis::getInstance()->transferPlayer($player, "lobby");
					if ($player->getNameTag() !== "undefined") {
						$player->setNameTag("undefined");
						Server::getInstance()->dispatchCommand($player, "play " . BedWars::getGamemodeId());
					}
					break;
				case ItemIds::BED:
					StarGateAtlantis::getInstance()->transferPlayer($player, "lobby");
					break;
				default:
					return false;
			}
			return true;
		}
		return false;
	}
}