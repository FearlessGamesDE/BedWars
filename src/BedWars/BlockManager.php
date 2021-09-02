<?php

namespace BedWars;

use pocketmine\math\Vector3;

class BlockManager
{
	/**
	 * @var bool[]
	 */
	private static $allowed = [];

	/**
	 * @param Vector3 $position
	 * @return bool
	 */
	public static function isAllowedToPlace(Vector3 $position): bool
	{
		$x = $position->getX();
		$y = $position->getY();
		$z = $position->getZ();
		if($x < -150 || $x > 150 || $y < 25 || $y > 75 || $z < -150 || $z > 150){
			return false;
		}
		$ret = self::$allowed[$position->getX() . ":" . $y . ":" . $position->getZ()] ?? true;
		self::$allowed[$position->getX() . ":" . $y . ":" . $position->getZ()] = $ret;
		return $ret;
	}

	/**
	 * @param Vector3 $position
	 * @return bool
	 */
	public static function isAllowedToBreak(Vector3 $position): bool
	{
		return self::$allowed[$position->getX() . ":" . $position->getY() . ":" . $position->getZ()] ?? false;
	}

	/**
	 * @param Vector3 $position
	 */
	public static function deny(Vector3 $position): void
	{
		self::$allowed[$position->getX() . ":" . $position->getY() . ":" . $position->getZ()] = false;
	}
}