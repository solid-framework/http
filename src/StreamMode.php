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
	const R_TEXT = 'rt';
	const R_BINARY = 'rb';

	const R_PLUS = 'r+';
	const R_PLUS_TEXT = 'r+t';
	const R_PLUS_BINARY = 'r+b';

	const W = 'w';
	const W_TEXT = 'wt';
	const W_BINARY = 'wb';

	const W_PLUS = 'w+';
	const W_PLUS_TEXT = 'w+t';
	const W_PLUS_BINARY = 'w+b';

	const A = 'a';
	const A_TEXT = 'at';
	const A_BINARY = 'ab';

	const A_PLUS = 'a+';
	const A_PLUS_TEXT = 'a+t';
	const A_PLUS_BINARY = 'a+b';

	const X = 'x';
	const X_TEXT = 'xt';
	const X_BINARY = 'xb';

	const X_PLUS = 'x+';
	const X_PLUS_TEXT = 'x+t';
	const X_PLUS_BINARY = 'x+b';

	const C = 'c';
	const C_TEXT = 'ct';
	const C_BINARY = 'cb';

	const C_PLUS = 'c+';
	const C_PLUS_TEXT = 'c+t';
	const C_PLUS_BINARY = 'c+b';

	const E = 'e';
	const E_TEXT = 'et';
	const E_BINARY = 'eb';

	/**
	 * @return array
	 */
	public static function readable(): array
	{
		return [
			self::R,
			self::R_TEXT,
			self::R_BINARY,

			self::R_PLUS,
			self::R_PLUS_TEXT,
			self::R_PLUS_BINARY,

			self::W_PLUS,
			self::W_PLUS_TEXT,
			self::W_PLUS_BINARY,

			self::A_PLUS,
			self::A_PLUS_TEXT,
			self::A_PLUS_BINARY,

			self::X_PLUS,
			self::X_PLUS_TEXT,
			self::X_PLUS_BINARY,

			self::C_PLUS,
			self::C_PLUS_TEXT,
			self::C_PLUS_BINARY
		];
	}

	/**
	 * @return array
	 */
	public static function writable(): array
	{
		return [
			self::R_PLUS,
			self::R_PLUS_TEXT,
			self::R_PLUS_BINARY,

			self::W,
			self::W_TEXT,
			self::W_BINARY,

			self::W_PLUS,
			self::W_PLUS_TEXT,
			self::W_PLUS_BINARY,

			self::A,
			self::A_TEXT,
			self::A_BINARY,

			self::A_PLUS,
			self::A_PLUS_TEXT,
			self::A_PLUS_BINARY,

			self::X,
			self::X_TEXT,
			self::X_BINARY,

			self::X_PLUS,
			self::X_PLUS_TEXT,
			self::X_PLUS_BINARY,

			self::C,
			self::C_TEXT,
			self::C_BINARY,

			self::C_PLUS,
			self::C_PLUS_TEXT,
			self::C_PLUS_BINARY
		];
	}
}
