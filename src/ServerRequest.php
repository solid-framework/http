<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Solid\Collection\CollectionInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class ServerRequest extends Request implements ServerRequestInterface
{
	/**
	 * @var array
	 */
	protected $uploadedFiles;

	/**
	 * @var array
	 */
	protected $cookies;

    /**
     * @var array
     */
    protected $server;

	/**
	 * @var array|null
	 */
	protected $queryParameters;

	/**
	 * @var array|object|null
	 */
	protected $parsedBody;

	/**
	 * @var bool
	 */
	protected $hasExplicitParsedBody = false;

	/**
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * @var array
	 */
	protected $deserializers = [];

    /**
     * @param string                                $method
     * @param \Psr\Http\Message\UriInterface        $uri
     * @param string                                $protocolVersion
     * @param \Solid\Collection\CollectionInterface $headers
     * @param \Psr\Http\Message\StreamInterface     $body
     * @param array                                 $uploadedFiles
     * @param array                                 $cookies
     * @param array                                 $server
     */
	public function __construct(
		string $method,
		UriInterface $uri,
		string $protocolVersion,
		CollectionInterface $headers,
		StreamInterface $body,
		array $uploadedFiles = [],
		array $cookies = [],
        array $server = []
	) {
		parent::__construct($method, $uri, $protocolVersion, $headers, $body);

		$this->uploadedFiles = $uploadedFiles;
		$this->cookies = $cookies;
		$this->server = $server;
	}

    /**
     * @return array
     */
    public function getServerParams(): array
    {
    	return $this->server;
    }

    /**
     * @return array
     */
    public function getCookieParams(): array
    {
    	return $this->cookies;
    }

    /**
     * @param array $cookies
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
    	$request = clone $this;

    	$request->cookies = $cookies;

    	return $request;
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
    	if (!is_null($this->queryParameters)) {
    		return $this->queryParameters;
	    }

    	parse_str($this->uri->getQuery(), $parameters);

    	return $parameters;
    }

    /**
     * @param array $query
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
    	$request = clone $this;

    	$request->queryParameters = $query;

    	return $request;
    }

    /**
     * @return array
     */
    public function getUploadedFiles(): array
    {
    	return $this->uploadedFiles;
    }

    /**
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return \Psr\Http\Message\ServerRequestInterface
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
    	$request = clone $this;

    	$request->uploadedFiles = $uploadedFiles;

    	return $request;
    }

    /**
     * @return array|object|null
     */
    public function getParsedBody()
    {
    	if ($this->hasExplicitParsedBody) {
    		return $this->parsedBody;
	    }

    	if ($this->shouldReturnDefaultBody()) {
    		return $_POST;
	    }

	    $contentTypes = $this->getHeader('Content-Type');

    	/** @var \Solid\Http\DeserializerInterface $deserializer */
	    foreach ($this->deserializers as $deserializer) {
	    	$intersectingContentTypes = array_intersect($contentTypes, $deserializer->getContentTypes());

    		if (count($intersectingContentTypes) > 0) {
    			return $deserializer->deserialize($this->body);
		    }
	    }

	    return null;
    }

    /**
     * @param array|object|null $data
     * @return \Psr\Http\Message\ServerRequestInterface
     * @throws \InvalidArgumentException
     */
    public function withParsedBody($data): ServerRequestInterface
    {
    	if (!is_array($data) && !is_object($data) && !is_null($data)) {
    		throw new InvalidArgumentException('Unsupported parsed body type');
	    }

	    $request = clone $this;

    	$request->parsedBody = $data;
    	$request->hasExplicitParsedBody = true;

    	return $request;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
    	return $this->attributes;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
    	return $this->attributes[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function withAttribute($name, $value): ServerRequestInterface
    {
    	$request = clone $this;

    	$request->attributes[$name] = $value;

    	return $request;
    }

    /**
     * @param string $name
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function withoutAttribute($name): ServerRequestInterface
    {
    	$request = clone $this;

    	unset($request->attributes[$name]);

    	return $request;
    }

	/**
	 * @param \Solid\Http\DeserializerInterface $deserializer
	 * @param bool                              $prepend
	 */
	public function addDeserializer(DeserializerInterface $deserializer, bool $prepend = false): void
	{
	    call_user_func_array($prepend ? 'array_unshift' : 'array_push', [&$this->deserializers, $deserializer]);
	}

	/**
	 * @return bool
	 */
	protected function shouldReturnDefaultBody(): bool
	{
		$intersectingContentTypes = array_intersect(
			$this->getHeader('Content-Type'),
			[
				'application/x-www-form-urlencoded',
				'multipart/form-data'
			]
		);

		return $this->method === 'POST' && count($intersectingContentTypes) > 0;
	}
}
