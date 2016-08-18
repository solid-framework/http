<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class HeaderContainer
{
    /**
     * @internal
     * @since 0.1.0
     * @var array
     */
    protected $headers;

    /**
     * @api
     * @since 0.1.0
     * @param array|null $headers An array of header parameters.
     */
    public function __construct(array $headers = [])
    {
        $this->headers = [];

        foreach ($headers as $name => $value) {
            $this->add($name, $value);
        }
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function __toString(): string
    {
        $headerString = '';

        foreach ($this->headers as $name => $value) {
            $headerString .= $name . ': ' . implode(',', $value) . "\n";
        }

        return rtrim($headerString, "\n");
    }

    /**
     * Returns all registered values for the given case-insensitive header name or all
     * headers if no name was given
     *
     * @api
     * @since 0.1.0
     * @param string|null $name The name of the header key to retrieve the values from.
     * @return array
     */
    public function get(string $name = null): array
    {
        if (is_null($name)) {
            return $this->headers;
        }

        return strlen($key = $this->getHeaderKey($name)) > 0 ? $this->headers[$key] : [];
    }

    /**
     * Sets the given header replacing it if it already exists
     *
     * @api
     * @since 0.1.0
     * @param string       $name  The name of the header key.
     * @param string|array $value The header value(s).
     * @return void
     */
    public function set(string $name, $value)
    {
        if (strlen($key = $this->getHeaderKey($name)) > 0) {
            unset($this->headers[$key]);
        }

        $this->add($name, $value);
    }

    /**
     * Adds the given header value to the given header or creates it if it does not exist
     *
     * @api
     * @since 0.1.0
     * @param string       $name  The name of the header key.
     * @param string|array $value The header value(s).
     * @return void
     */
    public function add(string $name, $value)
    {
        if (strlen($key = $this->getHeaderKey($name)) === 0) {
            $key = $name;
            $this->headers[$key] = [];
        }

        foreach ((array) $value as $v) {
            $this->headers[$key][] = $v;
        }
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name The name of the header key to remove.
     * @return void
     */
    public function remove(string $name)
    {
        if (strlen($key = $this->getHeaderKey($name)) > 0) {
            unset($this->headers[$key]);
        }
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return strlen($this->getHeaderKey($name)) > 0;
    }

    /**
     * @internal
     * @since 0.1.0
     * @param string $name The name of the header key to retrieve.
     * @return string
     */
    protected function getHeaderKey(string $name): string
    {
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === strtolower($name)) {
                return $key;
            }
        }

        return '';
    }
}
