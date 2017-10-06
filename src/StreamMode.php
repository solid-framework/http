<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Solid\Collection\Enum;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class StreamMode extends Enum
{
	const R = 'r';
	const R_PLUS = 'r+';
	const W = 'w';
	const W_PLUS = 'w+';
	const A = 'a';
	const A_PLUS = 'a+';
	const X = 'x';
	const X_PLUS = 'x+';
	const C = 'c';
	const C_PLUS = 'c+';
	const E = 'e';

	/**
	 * @return array
	 */
	public static function readable(): array
	{
		return [
			self::R,
			self::R_PLUS,
			self::W_PLUS,
			self::A_PLUS,
			self::X_PLUS,
			self::C_PLUS
		];
	}

	/**
	 * @return array
	 */
	public static function writable(): array
	{
		return [
			self::R_PLUS,
			self::W,
			self::W_PLUS,
			self::A,
			self::A_PLUS,
			self::X,
			self::X_PLUS,
			self::C,
			self::C_PLUS
		];
	}
}
