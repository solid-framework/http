<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Interop\Http\Factory\UriFactoryInterface;
use Solid\Http\UriFactory;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass Solid\Http\UriFactory
 */
class UriFactoryTest extends TestCase
{
    /**
     * @var \Solid\Http\UriFactory
     */
    protected $uriFactory;

    /**
     * @before
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->uriFactory = new UriFactory();
    }

    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrUriFactoryInterface(): void
    {
        $this->assertContains(UriFactoryInterface::class, class_implements(UriFactory::class));
    }

    /**
     * @test
     * @covers ::createUri
     */
    public function shouldReturnEmptyUri(): void
    {
        $uri = $this->uriFactory->createUri();

        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', $uri->getFragment());
    }

    /**
     * @test
     * @covers ::createUri
     */
    public function shouldReturnFullUri(): void
    {
        $uri = $this->uriFactory->createUri(
            'http://username:password@www.solid-framework.com:8080/path?key=value#fragment'
        );

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('username:password@www.solid-framework.com:8080', $uri->getAuthority());
        $this->assertSame('username:password', $uri->getUserInfo());
        $this->assertSame('www.solid-framework.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame('key=value', $uri->getQuery());
        $this->assertSame('fragment', $uri->getFragment());
    }
}
