<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Closure;
use ReflectionClass;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass Solid\Http\UriTrait
 */
class UriTraitTest extends TestCase
{
    /**
     * @param string $methodName
     * @return \Closure
     */
    protected function getMockForMethod(string $methodName): Closure
    {
        $uriTraitReflection = new ReflectionClass(Fixtures\UriTraitImplementation::class);
        $method = $uriTraitReflection->getMethod($methodName);
        $method->setAccessible(true);

        return function (...$parameters) use ($uriTraitReflection, $method) {
            return $method->invokeArgs($uriTraitReflection->newInstanceWithoutConstructor(), $parameters);
        };
    }

    /**
     * @test
     * @covers ::normalizeScheme
     */
    public function shouldNormalizeScheme(): void
    {
        $normalizeScheme = $this->getMockForMethod('normalizeScheme');

        $this->assertSame('http', $normalizeScheme('HTtP'));
    }

    /**
     * @test
     * @covers ::normalizeHost
     */
    public function shouldNormalizeHost(): void
    {
        $normalizeHost = $this->getMockForMethod('normalizeHost');

        $this->assertSame('solid-framework.com', $normalizeHost('Solid-Framework.com'));
    }

    /**
     * @test
     * @covers ::encodePath
     */
    public function shouldEncodePath(): void
    {
        $encodePath = $this->getMockForMethod('encodePath');

        $this->assertSame('/path/with%20space', $encodePath('/path/with space'));
    }

    /**
     * @test
     * @covers ::encodePath
     */
    public function shouldNotDoubleEncodePath(): void
    {
        $encodePath = $this->getMockForMethod('encodePath');

        $this->assertSame('/path/%2F', $encodePath('/path/%2F'));
    }

    /**
     * @test
     * @covers ::encodeQuery
     */
    public function shouldEncodeQuery(): void
    {
        $encodeQuery = $this->getMockForMethod('encodeQuery');

        $this->assertSame(
            'key=value&another-key=value%20with%20spaces',
            $encodeQuery('key=value&another-key=value with spaces')
        );
    }

    /**
     * @test
     * @covers ::encodeQuery
     */
    public function shouldNotDoubleEncodeQuery(): void
    {
        $encodeQuery = $this->getMockForMethod('encodeQuery');

        $this->assertSame('key=value&another-key=%2F', $encodeQuery('key=value&another-key=%2F'));
    }

    /**
     * @test
     * @covers ::encodeFragment
     */
    public function shouldEncodeFragment(): void
    {
        $encodeFragment = $this->getMockForMethod('encodeFragment');

        $this->assertSame(
            'fragment%20with%20spaces',
            $encodeFragment('fragment with spaces')
        );
    }

    /**
     * @test
     * @covers ::encodeFragment
     */
    public function shouldNotDoubleEncodeFragment(): void
    {
        $encodeFragment = $this->getMockForMethod('encodeFragment');

        $this->assertSame('fragment%2F', $encodeFragment('fragment%2F'));
    }
}
