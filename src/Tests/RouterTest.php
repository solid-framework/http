<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use DirectoryIterator;
use Solid\Http\Router;
use Solid\Http\Request;
use Solid\Kernel\Request as KernelRequest;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @coversDefaultClass Solid\Http\Router
 */
class RouterTest extends TestCase
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
     * @var \Solid\Config\Config
     */
    protected $configMock;

    /**
     * @internal
     * @since 0.1.0
     * @var Request
     */
    protected $emptyRequest;

    /**
     * @api
     * @before
     * @since 0.1.0
     * @return void
     */
    public function setup()
    {
        $this->containerMock = $this->createMock('Solid\Container\Container');
        $this->configMock = $this->createMock('Solid\Config\Config');
        $this->emptyRequest = Request::fromKernelRequest(new KernelRequest([], [], [], [], [], [], [], [], [], []));
    }

    /**
     * NOTE: This test will run before the "testMethodResolutionFail" test as
     *       that test depends on this test. This is necessary as we need to
     *       ensure that the router behaves properly when no controller is
     *       found and that it can locate a default controller. So the default
     *       controller is only available to "testMethodResolutionFail". Also
     *       because the there is a test depending on this test we cannot
     *       expect an exception through annotations but must handle them.
     * @api
     * @test
     * @covers ::__construct
     * @covers ::routeRequest
     * @covers ::findController
     * @covers ::validateController
     * @since 0.1.0
     * @return void
     */
    public function testControllerResolutionFail()
    {
        $router = new Router($this->containerMock, $this->configMock);

        try {
            $router->routeRequest($this->emptyRequest);
        } catch (\Exception $exception) {
            $this->assertInstanceOf(
                'Solid\Kernel\ControllerNotFoundException',
                $exception,
                'Should throw exception if no controller was found'
            );
        }
    }

    /**
     * @api
     * @test
     * @covers ::routeRequest
     * @covers ::findController
     * @covers ::validateController
     * @covers ::findControllerMethod
     * @covers ::validateControllerMethod
     * @since 0.1.0
     * @return void
     */
    public function testMethodResolution()
    {
        $this->containerMock->method('resolve')->will($this->returnCallback(function ($abstract, ...$parameters) {
            switch ($abstract) {
                case 'Solid\Config\ConfigSection':
                    return $this->configMock;
                case 'controller':
                    return new \Solid\App\Controllers\TestController;
            }
        }));

        $this->containerMock->expects($this->once())
            ->method('bind')
            ->with('Solid\App\Controllers\TestController', null, true);

        $this->containerMock->expects($this->once())
            ->method('alias')
            ->with('Solid\App\Controllers\TestController', 'controller');

        $router = new Router($this->containerMock, $this->configMock);
        $response = $router->routeRequest($this->emptyRequest->withRequestTarget('/test'));

        $this->assertSame('TestController::allIndex', (string) $response, 'Should return the correct response');
    }

    /**
     * @api
     * @test
     * @covers ::findControllerMethod
     * @covers ::validateControllerMethod
     * @since 0.1.0
     * @return void
     */
    public function testMethodResolutionParameters()
    {
        $this->containerMock->method('resolve')->will($this->returnCallback(function ($abstract, ...$parameters) {
            switch ($abstract) {
                case 'Solid\Config\ConfigSection':
                    return $this->configMock;
                case 'controller':
                    return new \Solid\App\Controllers\TestController;
            }
        }));

        $this->containerMock->expects($this->once())
            ->method('bind')
            ->with('Solid\App\Controllers\TestController', null, true);

        $this->containerMock->expects($this->once())
            ->method('alias')
            ->with('Solid\App\Controllers\TestController', 'controller');

        $router = new Router($this->containerMock, $this->configMock);
        $response = $router->routeRequest($this->emptyRequest->withRequestTarget('/test/parameters/one/two'));

        $this->assertSame(
            'TestController::allParameters(string one, string two)',
            (string) $response,
            'Should return the correct response'
        );
    }

    /**
     * @api
     * @test
     * @covers ::findController
     * @covers ::findControllerMethod
     * @covers ::validateControllerMethod
     * @expectedException Solid\Kernel\ControllerMethodNotFoundException
     * @depends testControllerResolutionFail
     * @since 0.1.0
     * @return void
     */
    public function testMethodResolutionFail()
    {
        // include the hidden default controller
        require_once __DIR__ . '/Fixtures/Controllers/HomeController.hidden.php';

        $this->containerMock->method('resolve')->will($this->returnCallback(function ($abstract, ...$parameters) {
            switch ($abstract) {
                case 'Solid\Config\ConfigSection':
                    return $this->configMock;
            }
        }));

        $router = new Router($this->containerMock, $this->configMock);
        $router->routeRequest($this->emptyRequest->withRequestTarget('/no/method'));
    }

    /**
     * @api
     * @test
     * @covers ::diffPathArray
     * @covers ::getValidPathArray
     * @covers ::getFirstSpecialChar
     * @covers ::findControllerMethod
     * @covers ::validateControllerMethod
     * @expectedException Solid\Kernel\ControllerMethodNotFoundException
     * @since 0.1.0
     * @return void
     */
    public function testMethodResolutionParameterFail()
    {
        $this->containerMock->method('resolve')->will($this->returnCallback(function ($abstract, ...$parameters) {
            switch ($abstract) {
                case 'Solid\Config\ConfigSection':
                    return $this->configMock;
            }
        }));

        $router = new Router($this->containerMock, $this->configMock);
        $router->routeRequest($this->emptyRequest->withRequestTarget('/test/no/parameters/invalid/parameters'));
    }

    /**
     * @api
     * @test
     * @covers ::diffPathArray
     * @covers ::getValidPathArray
     * @covers ::getFirstSpecialChar
     * @covers ::findControllerMethod
     * @covers ::validateControllerMethod
     * @covers ::validateParameters
     * @since 0.1.0
     * @return void
     */
    public function testMethodResolutionParametersValidation()
    {
        $this->containerMock->method('resolve')->will($this->returnCallback(function ($abstract, ...$parameters) {
            switch ($abstract) {
                case 'Solid\Config\ConfigSection':
                    return $this->configMock;
                case 'controller':
                    return new \Solid\App\Controllers\TestController;
            }
        }));
        $this->configMock->method('get')->will($this->returnCallback(function ($key) {
            switch ($key) {
                case 'http.routing.parameterValidation':
                    return true;
            }
        }));

        $this->containerMock->expects($this->once())
            ->method('bind')
            ->with('Solid\App\Controllers\TestController', null, true);

        $this->containerMock->expects($this->once())
            ->method('alias')
            ->with('Solid\App\Controllers\TestController', 'controller');

        $router = new Router($this->containerMock, $this->configMock);
        $response = $router->routeRequest(
            $this->emptyRequest->withRequestTarget('/test/parameters/validation/24/martin@solid-framework.com')
        );

        $this->assertSame(
            'TestController::allParametersValidation(integer 24, string martin@solid-framework.com)',
            (string) $response,
            'Should return the correct response'
        );
    }

    /**
     * @api
     * @test
     * @covers ::findControllerMethod
     * @covers ::validateControllerMethod
     * @covers ::validateParameters
     * @expectedException Solid\Kernel\InvalidUserInputException
     * @since 0.1.0
     * @return void
     */
    public function testMethodResolutionParametersValidationFail()
    {
        $this->containerMock->method('resolve')->will($this->returnCallback(function ($abstract, ...$parameters) {
            switch ($abstract) {
                case 'Solid\Config\ConfigSection':
                    return $this->configMock;
                case 'controller':
                    return new \Solid\App\Controllers\TestController;
            }
        }));
        $this->configMock->method('get')->will($this->returnCallback(function ($key) {
            switch ($key) {
                case 'http.routing.parameterValidation':
                    return true;
            }
        }));
        $this->configMock->method('has')->will($this->returnValue(true));

        $router = new Router($this->containerMock, $this->configMock);
        $response = $router->routeRequest(
            $this->emptyRequest->withRequestTarget('/test/parameters/validation/sk8/martin@solid-framework.com')
        );
    }

    /**
     * @api
     * @test
     * @covers ::findControllerMethod
     * @since 0.1.0
     * @return void
     */
    public function testMethodPrefixResolution()
    {
        $this->containerMock->method('resolve')->will($this->returnCallback(function ($abstract, ...$parameters) {
            switch ($abstract) {
                case 'Solid\Config\ConfigSection':
                    return $this->configMock;
                case 'controller':
                    return new \Solid\App\Controllers\TestController;
            }
        }));
        $this->configMock->method('get')->will($this->returnCallback(function ($key, $default) {
            switch ($key) {
                case 'http.routing.prefixMap.get':
                case 'http.routing.prefixMap.post':
                    return $default;
                case 'http.routing.prefixMap.put':
                    return 'update';
            }
        }));

        $router = new Router($this->containerMock, $this->configMock);

        $this->assertSame(
            'TestController::getUser',
            (string) $router->routeRequest($this->emptyRequest->withRequestTarget('/test/user')->withMethod('GET')),
            'Should return the correct response'
        );

        $this->assertSame(
            'TestController::postUser',
            (string) $router->routeRequest($this->emptyRequest->withRequestTarget('/test/user')->withMethod('POST')),
            'Should return the correct response'
        );

        $this->assertSame(
            'TestController::updateUser',
            (string) $router->routeRequest($this->emptyRequest->withRequestTarget('/test/user')->withMethod('PUT')),
            'Should return the correct response'
        );
    }
}
