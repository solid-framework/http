<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests\Fixtures;

use Solid\Kernel\ResourceResponseInterface;

/**
 * @package Solid\Http\Tests\Fixtures
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class ResourceStringResponse implements ResourceResponseInterface
{
    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $content;

    /**
     * @api
     * @since 0.1.0
     * @param string $content The content to store.
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function __toString(): string
    {
        return $this->content;
    }
}
