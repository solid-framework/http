<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Solid\Application;
use Solid\Http\Kernel;
use Solid\Http\Router;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class KernelTest extends TestCase
{
    /**
     * @internal
     * @since 0.1.0
     * @var Kernel
     */
    protected $kernel;

    /**
     * @internal
     * @since 0.1.0
     * @var Application
     */
    protected $applicationMock;

    /**
     * @internal
     * @since 0.1.0
     * @var Router
     */
    protected $routerMock;

    /**
     * @api
     * @since 0.1.0
     * @before
     */
    public function setup()
    {
        $this->applicationMock = $this->createMock('Solid\Application');
        $this->routerMock = $this->createMock('Solid\Http\Router');

        $this->kernel = new Kernel($this->routerMock, $this->applicationMock);
    }

    /**
     * @api
     * @since 0.1.0
     * @test
     */
    public function testImplementationRequirements()
    {
        $this->assertInstanceOf('Solid\Kernel\KernelInterface', $this->kernel);
    }

    /**
     * @api
     * @since 0.1.0
     * @test
     * @expectedException \Solid\Kernel\UnsupportedRequestTypeException
     */
    public function testRequestType()
    {
        $this->kernel->handleRequest(new Fixtures\TestRequest);
    }
}
