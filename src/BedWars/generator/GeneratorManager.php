<?php

namespace BedWars\generator;

class GeneratorManager
{
	/**
	 * @var Generator[]
	 */
	private static $generators = [];

	/**
	 * @param Generator $generator
	 */
	public static function add(Generator $generator): void
	{
		self::$generators[] = $generator;
	}

	public static function tick(): void
	{
		foreach (self::$generators as $generator){
			$generator->tick();
		}
	}
}