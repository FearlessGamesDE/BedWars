<?php

namespace BedWars;

use alemiz\sga\StarGateAtlantis;
use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use BedWars\team\TeamManager;
use BedWars\utils\TeamColor;
use jojoe77777\FormAPI\SimpleForm;
use LobbySystem\packets\server\PlayPacket;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use pocketmine\Server;

class HudManager
{
	public static function send(Player $player): void
	{
		$compass = ItemFactory::get(ItemIds::COMPASS);
		$compass->setCustomName("§6Teleporter");
		$paper = ItemFactory::get(ItemIds::PAPER);
		$paper->setCustomName("§bNext Game");
		$bed = ItemFactory::get(ItemIds::BED);
		$bed->setCustomName("§cLeave Game");
		$player->getInventory()->setContents([
			0 => $compass,
			7 => $paper,
			8 => $bed
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
					if($players === []){
						return true;
					}
					$form = new SimpleForm(static function (Player $player, ?int $data = null) use ($players) {
						if ($data !== null) {
							$p = $players[$data] ?? "";
							if($p->isPlayer()){
								if (($pl = Server::getInstance()->getPlayerExact($p->getName())) instanceof Player) {
									$player->teleport($pl);
								}
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
					if($player->getNameTag() !== "undefined"){
						$player->setNameTag("undefined");
						Server::getInstance()->dispatchCommand($player, "play " . BedWars::getId());
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