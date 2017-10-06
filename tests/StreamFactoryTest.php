<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Interop\Http\Factory\StreamFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass \Solid\Http\StreamFactory
 */
class StreamFactoryTest extends TestCase
{
    /**
     * @var string
     */
    private static $resourceStreamFile = __DIR__ . '/Fixtures/resource-stream-file.txt';

    /**
     * @var string
     */
    private static $resourceStreamFileContent = <<<TXT
Line one
Line two
Line three
TXT;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var \Solid\Http\StreamFactory
     */
    protected $factory;

    /**
     * @before
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->factory = new StreamFactory();

        $resource = fopen(self::$resourceStreamFile, 'w');

        fwrite($resource, self::$resourceStreamFileContent, strlen(self::$resourceStreamFileContent));
        fclose($resource);
    }

    /**
     * @after
     */
    public function tearDown()
    {
        parent::tearDown();

        @fclose($this->resource);
        @unlink(self::$resourceStreamFile);
    }

    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrStreamFactoryInterface(): void
    {
        $this->assertContains(StreamFactoryInterface::class, class_implements(StreamFactory::class));
    }

    /**
     * @test
     * @covers ::createStream
     */
    public function shouldCreateStreamFromString(): void
    {
        $streamContent = 'Stream content';
        $stream = $this->factory->createStream($streamContent);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame($streamContent, (string)$stream);
    }

    /**
     * @test
     * @covers ::createStreamFromResource
     */
    public function shouldCreateStreamFromResource(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $stream = $this->factory->createStreamFromResource($this->resource);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(self::$resourceStreamFileContent, (string)$stream);
    }

    /**
     * @test
     * @covers ::createStreamFromResource
     * @expectedException \InvalidArgumentException
     */
    public function createStreamFromResourceShouldThrowExceptionOnInvalidResource(): void
    {
        $this->factory->createStreamFromResource(null);
    }

    /**
     * @test
     * @covers ::createStreamFromFile
     */
    public function shouldCreateStreamFromFile(): void
    {
        $stream = $this->factory->createStreamFromFile(self::$resourceStreamFile);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(self::$resourceStreamFileContent, (string)$stream);
    }

    /**
     * @test
     * @covers ::createStreamFromFile
     * @expectedException \InvalidArgumentException
     */
    public function createStreamFromFileShouldThrowExceptionOnInvalidFile(): void
    {
        $this->factory->createStreamFromFile('invalid-file');
    }
}
