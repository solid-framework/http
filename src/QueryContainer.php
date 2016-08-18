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
class QueryContainer
{
    /**
     * @internal
     * @since 0.1.0
     * @var array
     */
    protected $parameters;

    /**
     * @api
     * @since 0.1.0
     * @param string|null $query A formated query string.
     */
    public function __construct(string $query = null)
    {
        $this->parameters = [];

        if (!is_null($query)) {
            parse_str($query, $this->parameters);
        }
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $parameter The parameter key.
     * @param string $value     The parameter value.
     * @return void
     */
    public function set(string $parameter, string $value)
    {
        $this->parameters[$parameter] = $value;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $parameter The parameter key.
     * @param mixed  $default   A default value.
     * @return mixed
     */
    public function get(string $parameter, $default = null)
    {
        return $this->parameters[$parameter] ?? $default;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $parameter The parameter key.
     * @return bool
     */
    public function has(string $parameter): bool
    {
        return array_key_exists($parameter, $this->parameters);
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function asArray(): array
    {
        return $this->parameters;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function __toString(): string
    {
        return http_build_query($this->parameters, null, '&', PHP_QUERY_RFC3986);
    }
}
