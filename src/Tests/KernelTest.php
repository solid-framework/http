<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Solid\Http\Kernel;
use Solid\Http\Request;
use Solid\Http\Response;
use Solid\Http\StringStream;
use Solid\Kernel\Request as KernelRequest;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @coversDefaultClass Solid\Http\Kernel
 */
class KernelTest extends TestCase
{
    /**
     * @internal
     * @since 0.1.0
     * @var \Solid\Container\Container
     */
    protected $containerMock;

    /**
     * @internal
     * @since 0.1.0
     * @var \Solid\Http\Router
     */
    protected $routerMock;

    /**
     * @api
     * @before
     * @since 0.1.0
     * @return void
     */
    public function setup()
    {
        $this->containerMock = $this->createMock('Solid\Container\Container');
        $this->routerMock = $this->createMock('Solid\Http\Router');
        $this->emptyRequest = Request::fromKernelRequest(new KernelRequest([], [], [], [], [], [], [], [], [], []));
        $this->emptyResponse = new Response;
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @since 0.1.0
     * @return void
     */
    public function testImplementationRequirements()
    {
        $kernel = new Kernel($this->routerMock, $this->containerMock);

        $this->assertInstanceOf(
            'Solid\Kernel\KernelInterface',
            $kernel,
            'Should implement the kernel interface'
        );
    }

    /**
     * @api
     * @test
     * @covers ::handleRequest
     * @expectedException Solid\Kernel\UnsupportedRequestTypeException
     * @since 0.1.0
     * @return void
     */
    public function testInvalidRequestType()
    {
        $kernel = new Kernel($this->routerMock, $this->containerMock);
        $kernel->handleRequest($this->createMock('Solid\Kernel\RequestInterface'));
    }

    /**
     * @api
     * @test
     * @covers ::handleRequest
     * @since 0.1.0
     * @return void
     */
    public function testRequestBinding()
    {
        $this->containerMock->method('resolve')->will($this->returnValue(new Response));
        $this->containerMock->expects($this->once())
            ->method('instance')
            ->with('request', $this->emptyRequest);

        $kernel = new Kernel($this->routerMock, $this->containerMock);
        $response = $kernel->handleRequest($this->emptyRequest);
    }

    /**
     * @api
     * @test
     * @covers ::handleRequest
     * @since 0.1.0
     * @return void
     */
    public function test200Response()
    {
        $this->containerMock->method('resolve')->will($this->returnValue(new Response));
        $this->routerMock->method('routeRequest')->will($this->returnCallback(function () {
            $testController = new \Solid\App\Controllers\TestController;

            return $testController->allIndex();
        }));
        $request = $this->emptyRequest->withRequestTarget('/test');
        $kernel = new Kernel($this->routerMock, $this->containerMock);
        $response = $kernel->handleRequest($request);

        $this->assertInstanceOf(
            'Psr\Http\Message\ResponseInterface',
            $response,
            'Should return a PSR-7 compliant HTTP response object'
        );
        $this->assertSame(200, $response->getStatusCode(), 'Should return correct status code');
        $this->assertSame(
            'TestController::allIndex',
            (string) $response->getBody(),
            'Should return a response with the correct body'
        );
    }

    /**
     * @api
     * @test
     * @covers ::handleRequest
     * @since 0.1.0
     * @return void
     */
    public function test404Response()
    {
        $this->containerMock->method('resolve')->will($this->returnValue(new Response));
        $this->routerMock->method('routeRequest')->will(
            $this->throwException(new \Solid\Kernel\ControllerNotFoundException)
        );
        $request = $this->emptyRequest->withRequestTarget('/no/path');
        $kernel = new Kernel($this->routerMock, $this->containerMock);
        $response = $kernel->handleRequest($request);

        $this->assertSame(404, $response->getStatusCode(), 'Should return correct status code');
        $this->assertSame(
            'Not Found',
            (string) $response->getBody(),
            'Should return a response with the correct body'
        );
    }

    /**
     * @api
     * @test
     * @covers ::handleRequest
     * @since 0.1.0
     * @return void
     */
    public function test500Response()
    {
        $this->containerMock->method('resolve')->will($this->returnValue(new Response));
        $this->routerMock->method('routeRequest')->will(
            $this->throwException(new \Exception)
        );
        $request = $this->emptyRequest->withRequestTarget('/no/path');
        $kernel = new Kernel($this->routerMock, $this->containerMock);
        $response = $kernel->handleRequest($request);

        $this->assertSame(500, $response->getStatusCode(), 'Should return correct status code');
        $this->assertSame(
            'Internal Server Error',
            (string) $response->getBody(),
            'Should return a response with the correct body'
        );
    }

    /**
     * @api
     * @test
     * @covers ::handleRequest
     * @since 0.1.0
     * @return void
     */
    public function testExceptionMessage()
    {
        $this->containerMock->method('resolve')->will($this->returnValue(new Response));
        $this->routerMock->method('routeRequest')->will(
            $this->throwException(new \Exception('Exception message'))
        );
        $request = $this->emptyRequest->withRequestTarget('/no/path');
        $kernel = new Kernel($this->routerMock, $this->containerMock);
        $response = $kernel->handleRequest($request);

        $this->assertSame(
            'Exception message',
            (string) $response->getBody(),
            'Should return a response with the correct body'
        );
    }

    /**
     * @api
     * @test
     * @covers ::dispatchResponse
     * @expectedException Solid\Kernel\UnsupportedResponseTypeException
     * @since 0.1.0
     * @return void
     */
    public function testInvalidResponseType()
    {
        $kernel = new Kernel($this->routerMock, $this->containerMock);
        $kernel->dispatchResponse($this->createMock('Solid\Kernel\ResponseInterface'));
    }

    /**
     * @api
     * @test
     * @covers ::dispatchResponse
     * @since 0.1.0
     * @return void
     */
    public function testResponseBody()
    {
        $kernel = new Kernel($this->routerMock, $this->containerMock);

        set_error_handler(function () {});
        ob_start();
        $kernel->dispatchResponse($this->emptyResponse->withBody(new StringStream('response body')));
        $output = ob_get_clean();
        restore_error_handler();

        $this->assertSame('response body', $output, 'Should respond with the correct body');
    }
}
