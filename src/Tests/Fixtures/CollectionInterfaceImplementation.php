<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests\Fixtures;

use ArrayIterator;
use Solid\Collection\CollectionInterface;
use Solid\Collection\ReadableCollectionInterface;
use Traversable;

/**
 * @package Solid\Http\Tests\Fixtures
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class CollectionInterfaceImplementation implements CollectionInterface
{
    protected $store;

    public function __construct($store = [])
    {
        $this->store = $store;
    }

    public function get($key, $default)
    {
        return array_key_exists($key, $this->store) ? $this->store[$key] : $default;
    }
    public function set($key, $value): void
    {
        $this->store[$key] = $value;
    }

    public function remove($key): void
    {
        unset($this->store[$key]);
    }

    public function all(): array
    {
        return $this->store;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->store);
    }

    /*
     |--------------------------------------------------------------------------
     | Methods ignored for testing purposes
     |--------------------------------------------------------------------------
     |
     | This is a partial implementation of a CollectionInterface and thus only
     | methods needed for testing are implemented.
     |
     */

    public function count(): int
    {
        return 0;
    }

    public function take(int $amount): array
    {
        return [];
    }

    public function slice(int $start, int $amount): array
    {
        return [];
    }

    public function keys(): array
    {
        return [];
    }

    public function values(): array
    {
        return [];
    }

    public function first()
    {
        // ...
    }

    public function has($key): bool
    {
        return false;
    }

    public function contains($value, bool $strict): bool
    {
        return false;
    }

    public function map(callable $callback): ReadableCollectionInterface
    {
        return $this;
    }

    public function filter(callable $callback): ReadableCollectionInterface
    {
        return $this;
    }

    public function reduce(callable $callback, $initialValue)
    {
        // ...
    }

    public function join(string $glue): string
    {
        return '';
    }

    public function add($item): void
    {
        // ...
    }

    public function merge(ReadableCollectionInterface $collection, $key = null, bool $mergeIndexed = false): void
    {
        // ...
    }

    public function clear(): void
    {
        // ...
    }
}
