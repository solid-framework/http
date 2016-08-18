<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests\Fixtures;

use Solid\Kernel\Request as KernelRequest;

/**
 * @package Solid\Http\Tests\Fixtures
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class PostKernelRequest extends KernelRequest
{
    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getGlobalParameters(): array
    {
        return [];
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getServerParameters(): array
    {
        return [
            'REQUEST_METHOD' => 'post',
            'REQUEST_SCHEME' => 'https',
            'REQUEST_URI' => '/',
            'HTTP_HOST' => 'test.example.com',
            'SERVER_PORT' => 80
        ];
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getGetParameters(): array
    {
        return [];
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getPostParameters(): array
    {
        return [
            'parameter1' => 'value1',
            'parameter2' => 'value2'
        ];
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getFileParameters(): array
    {
        return [];
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getCookieParameters(): array
    {
        return [];
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getSessionParameters(): array
    {
        return [];
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getRequestParameters(): array
    {
        return [
            'parameter1' => 'value1',
            'parameter2' => 'value2'
        ];
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getEnvParameters(): array
    {
        return [];
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getHeaderParameters(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Content-Length' => strlen('parameter1=value1&parameter2=value2'),
            'Accept' => 'text/plain'
        ];
    }
}
