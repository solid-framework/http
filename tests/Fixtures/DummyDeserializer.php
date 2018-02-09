<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests\Fixtures;

use Psr\Http\Message\StreamInterface;
use Solid\Http\DeserializerInterface;

/**
 * @package Solid\Http\Tests\Fixtures
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class DummyDeserializer implements DeserializerInterface
{
	/**
	 * @var array
	 */
	protected $contentTypes;

	/**
	 * @var array|object|null
	 */
	protected $parsedBody;

	/**
	 * @param array             $contentTypes
	 * @param array|object|null $parsedBody
	 */
	public function __construct(array $contentTypes, $parsedBody)
	{
		$this->contentTypes = $contentTypes;
		$this->parsedBody = $parsedBody;
	}

	/**
	 * @return array
	 */
	public function getContentTypes(): array
	{
		return $this->contentTypes;
	}

	/**
	 * @param \Psr\Http\Message\StreamInterface $body
	 * @return array|object|null
	 */
	public function deserialize(StreamInterface $body)
	{
		return $this->parsedBody;
	}
}
