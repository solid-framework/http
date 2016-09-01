<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Exception;
use Solid\Container\Container;
use Psr\Http\Message\RequestInterface;
use Solid\Kernel\KernelInterface;
use Solid\Kernel\RequestInterface as KernelRequestInterface;
use Solid\Kernel\ResponseInterface as KernelResponseInterface;
use Solid\Kernel\ResourceNotFoundException;
use Solid\Kernel\InvalidUserInputException;
use Solid\Kernel\UnsupportedRequestTypeException;
use Solid\Kernel\UnsupportedResponseTypeException;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class Kernel implements KernelInterface
{
    /**
     * An instance of the Http router
     *
     * @internal
     * @since 0.1.0
     * @var Router
     */
    protected $router;

    /**
     * The application DI container
     *
     * @internal
     * @since 0.1.0
     * @var Container
     */
    protected $container;

    /**
     * @api
     * @since 0.1.0
     * @param Router    $router    An instance of the Http router.
     * @param Container $container The application DI container.
     */
    public function __construct(Router $router, Container $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    /**
     * @api
     * @since 0.1.0
     * @param KernelRequestInterface $request The request to handle.
     * @return KernelResponseInterface
     * @throws UnsupportedRequestTypeException
     */
    public function handleRequest(KernelRequestInterface $request): KernelResponseInterface
    {
        // only handle PSR-7 compliant HTTP requests
        if (!$request instanceof RequestInterface) {
            throw new UnsupportedRequestTypeException;
        }

        // bind kernel specific request
        $this->container->instance('request', $request);

        $response = $this->container->resolve('Solid\Http\Response');

        try {
            $resourceResponse = $this->router->routeRequest($request);

            return $response
                ->withStatus(200)
                ->withBody(new StringStream($resourceResponse));
        } catch (ResourceNotFoundException $exception) {
            $statusCode = 404;
            $message = $exception->getMessage();

            if (strlen($message) === 0) {
                $message = Response::STATUS_CODES[$statusCode];
            }

            return $response
                ->withStatus($statusCode)
                ->withBody(new StringStream($message));
        } catch (InvalidUserInputException $exception) {
            $statusCode = 400;
            $message = $exception->getMessage();

            if (strlen($message) === 0) {
                $message = Response::STATUS_CODES[$statusCode];
            }

            return $response
                ->withStatus($statusCode)
                ->withBody(new StringStream($message));
        } catch (Exception $exception) {
            $statusCode = 500;
            $message = $exception->getMessage();

            if (strlen($message) === 0) {
                $message = Response::STATUS_CODES[$statusCode];
            }

            return $response
                ->withStatus(500)
                ->withBody(new StringStream($message));
        }
    }

    /**
     * @api
     * @since 0.1.0
     * @param KernelResponseInterface $response The response to dispatch.
     * @return void
     * @throws UnsupportedResponseTypeException
     */
    public function dispatchResponse(KernelResponseInterface $response)
    {
        if (!$response instanceof Response) {
            throw new UnsupportedResponseTypeException;
        }

        // set initial response header
        header(
            "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()} {$response->getReasonPhrase()}",
            true,
            $response->getStatusCode()
        );

        header('Content-Length: ' . $response->getBody()->getSize());

        // set registered response headers
        foreach ($response->getHeaders() as $header => $value) {
            header($header . ': ' . implode(',', $value));
        }

        // send the response
        echo $response->getBody()->getContents();
    }
}
