<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Solid\Collection\CollectionInterface;

/**
 * @package Solid\Http
 * @author  Martin Pettersson <martin@solid-framework.com>
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @var \Solid\Http\Status
     */
    protected $status;

    /**
     * @var string
     */
    protected $reasonPhrase;

    /**
     * @param string                                $protocolVersion
     * @param int                                   $statusCode
     * @param \Solid\Collection\CollectionInterface $headers
     * @param \Psr\Http\Message\StreamInterface     $body
     * @throws \InvalidArgumentException
     */
    public function __construct($protocolVersion, int $statusCode, CollectionInterface $headers, StreamInterface $body)
    {
        parent::__construct($protocolVersion, $headers, $body);

        $this->status = new Status($statusCode);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status->getValue();
    }

    /**
     * @param int    $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $response = clone $this;

        $response->status = new Status($code);
        $response->reasonPhrase = strlen($reasonPhrase) > 0 ? $reasonPhrase : null;

        return $response;
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase ?? (string)$this->status;
    }
}