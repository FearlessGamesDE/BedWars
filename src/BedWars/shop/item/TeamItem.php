<?php

namespace BedWars\shop\item;

use BedWars\player\PlayerManager;
use BedWars\team\Team;
use pocketmine\item\Item;
use pocketmine\player\Player;

abstract class TeamItem extends BedWarsItem
{
	/**
	 * @var Item[]
	 */
	private array $teams;

	/**
	 * TeamItem constructor.
	 * @param Item   $cost
	 * @param Item[] $teams
	 * @param string $name
	 */
	public function __construct(Item $cost, array $teams, string $name)
	{
		$this->teams = $teams;
		parent::__construct($teams[0], $cost, $name);
	}

	/**
	 * @return Item[]
	 */
	public function getTeams(): array
	{
		return $this->teams;
	}

	/**
	 * @param Team $team
	 * @return Item
	 */
	public function getForTeam(Team $team): Item
	{
		return $this->teams[$team->getColor()];
	}

	/**
	 * @param Player $player
	 * @return Item
	 */
	public function getItem(Player $player): Item
	{
		return $this->getForTeam(PlayerManager::get($player->getName())->getTeam());
	}
}