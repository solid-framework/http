<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use InvalidArgumentException;
use Solid\Kernel\ResponseInterface as KernelResponseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class Response implements KernelResponseInterface, ResponseInterface
{
    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $protocolVersion;

    /**
     * @internal
     * @since 0.1.0
     * @var int
     */
    protected $statusCode;

    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $reasonPhrase;

    /**
     * @internal
     * @since 0.1.0
     * @var HeaderContainer
     */
    protected $headers;

    /**
     * @internal
     * @since 0.1.0
     * @var StreamInterface
     */
    protected $body;

    /**
     * @api
     * @since 0.1.0
     * @var array
     */
    public static $statusCodes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unassigned',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

    /**
     * @api
     * @since 0.1.0
     * @param string|null          $protocolVersion The HTTP protocol version to use.
     * @param int|null             $statusCode      The HTTP status code to use.
     * @param string|null          $reasonPhrase    The HTTP reasonphrase to use.
     * @param HeaderContainer|null $headers         The HTTP headers to use.
     * @param StreamInterface|null $body            The body to use.
     */
    public function __construct(
        $protocolVersion = null,
        $statusCode = null,
        $reasonPhrase = null,
        HeaderContainer $headers = null,
        StreamInterface $body = null
    ) {
        $statusCode = (int) ($statusCode ?? 200);
        if (!array_key_exists($statusCode, self::$statusCodes)) {
            throw new InvalidArgumentException("The given status code: {$statusCode} is not supported");
        }

        $this->protocolVersion = (string) ($protocolVersion ?? '1.1');
        $this->statusCode = $statusCode;
        $this->reasonPhrase = (string) ($reasonPhrase ?? self::$statusCodes[$this->statusCode] ?? '');
        $this->headers = $headers ?? new HeaderContainer;
        $this->body = $body ?? new StringStream;

        if (!$this->headers->has('Content-Length')) {
            $this->headers->set('Content-Length', $this->body->getSize());
        }
    }

    /**
     * @internal
     * @since 0.1.0
     */
    protected function __clone()
    {
        $this->headers = clone $this->headers;
        $this->body = clone $this->body;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function __toString(): string
    {
        return <<<RESPONSE
HTTP/{$this->getProtocolVersion()} {$this->getStatusCode()} {$this->getReasonPhrase()}
{$this->headers}

{$this->getBody()}
RESPONSE;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $version
     * @return Response
     */
    public function withProtocolVersion($version): self
    {
        $newResponse = clone $this;
        $newResponse->protocolVersion = $version;

        return $newResponse;
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers->get();
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return $this->headers->has($name);
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name
     * @return array
     */
    public function getHeader($name): array
    {
        return $this->headers->get($name);
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name
     * @return string
     */
    public function getHeaderLine($name): string
    {
        return implode(',', $this->headers->get($name));
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name
     * @param string|array $value
     * @return Response
     * @throws InvalidArgumentException
     */
    public function withHeader($name, $value): self
    {
        $newResponse = clone $this;
        $newResponse->headers->set($name, $value);

        return $newResponse;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name
     * @param string|array $value
     * @return Response
     * @throws InvalidArgumentException
     */
    public function withAddedHeader($name, $value): self
    {
        $newResponse = clone $this;
        $newResponse->headers->add($name, $value);

        return $newResponse;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name
     * @return Response
     */
    public function withoutHeader($name): self
    {
        $newResponse = clone $this;
        $newResponse->headers->remove($name);

        return $newResponse;
    }

    /**
     * @api
     * @since 0.1.0
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @api
     * @since 0.1.0
     * @param StreamInterface $body
     * @return Response
     * @throws InvalidArgumentException
     */
    public function withBody(StreamInterface $body): self
    {
        $newResponse = clone $this;
        $newResponse->body = $body;

        return $newResponse;
    }

    /**
     * @api
     * @since 0.1.0
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @api
     * @since 0.1.0
     * @param int $code
     * @param string|null $reasonPhrase
     * @return Response
     * @throws InvalidArgumentException
     */
    public function withStatus($code, $reasonPhrase = null): self
    {
        if (!array_key_exists($code, self::$statusCodes)) {
            throw new InvalidArgumentException("The given status code: {$code} is not supported");
        }

        $newResponse = clone $this;
        $newResponse->statusCode = $code;
        $newResponse->reasonPhrase = $reasonPhrase ?? self::$statusCodes[$code];

        return $newResponse;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
