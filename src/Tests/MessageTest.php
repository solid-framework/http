<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Solid\Http\Message;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass Solid\Http\Message
 */
class MessageTest extends TestCase
{
    use MockGeneratorTrait;

    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrMessageInterface(): void
    {
        $this->assertContains(MessageInterface::class, class_implements(Message::class));
    }

    /**
     * @test
     * @covers ::getProtocolVersion
     * @covers ::__construct
     */
    public function shouldReturnProtocolVersion()
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('2.0', $this->getHeadersMock(), $this->getBodyMock());

        $this->assertSame('2.0', $message->getProtocolVersion());
    }

    /**
     * @test
     * @covers ::withProtocolVersion
     * @covers ::getProtocolVersion
     */
    public function shouldReturnNewInstanceWithProtocolVersion()
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $this->getBodyMock());

        $messageWithProtocolVersion = $message->withProtocolVersion('2.0');

        $this->assertInstanceOf(MessageInterface::class, $messageWithProtocolVersion);
        $this->assertSame('2.0', $messageWithProtocolVersion->getProtocolVersion());
    }

    /**
     * @test
     * @covers ::withProtocolVersion
     */
    public function withProtocolVersionShouldPreserveTheOriginalMessage(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $this->getBodyMock());

        $messageWithProtocolVersion = $message->withProtocolVersion('2.0');

        $this->assertNotSame($message, $messageWithProtocolVersion);
        $this->assertSame('1.1', $message->getProtocolVersion());
    }

    /**
     * @test
     * @covers ::getHeaders
     * @covers ::__construct
     */
    public function shouldReturnAllHeaders(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1',
                'header-value-2'
            ],
            'Another-Header' => []
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($headersMock->all(), $message->getHeaders());
    }

    /**
     * @test
     * @covers ::hasHeader
     */
    public function shouldDetermineIfHeaderExists(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1',
                'header-value-2'
            ],
            'Another-Header' => []
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $this->assertTrue($message->hasHeader('header-name'));
        $this->assertTrue($message->hasHeader('Another-Header'));
        $this->assertFalse($message->hasHeader('nokey'));
    }

    /**
     * @test
     * @covers ::hasHeader
     * @covers ::getHeaderKey
     */
    public function hasHeaderShouldTreatHeadersCaseInsensitively(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [],
            'Another-Header' => []
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $this->assertTrue($message->hasHeader('Header-Name'));
        $this->assertTrue($message->hasHeader('another-header'));
        $this->assertFalse($message->hasHeader('nokey'));
    }

    /**
     * @test
     * @covers ::getHeader
     */
    public function shouldReturnHeaderValues(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1',
                'header-value-2'
            ],
            'Another-Header' => []
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $this->assertCount(2, $message->getHeader('header-name'));
        $this->assertCount(0, $message->getHeader('Another-Header'));
        $this->assertContains('header-value-1', $message->getHeader('header-name'));
        $this->assertContains('header-value-2', $message->getHeader('header-name'));
    }

    /**
     * @test
     * @covers ::getHeader
     */
    public function shouldReturnEmptyArrayIfHeaderNotExists(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $this->getBodyMock());

        $this->assertTrue(is_array($message->getHeader('nokey')));
        $this->assertCount(0, $message->getHeader('nokey'));
    }

    /**
     * @test
     * @covers ::getHeader
     * @covers ::getHeaderKey
     */
    public function getHeaderShouldTreatHeadersCaseInsensitively(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => ['value-1'],
            'Another-Header' => ['value-2']
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $this->assertContains('value-1', $message->getHeader('Header-Name'));
        $this->assertContains('value-2', $message->getHeader('another-header'));
    }

    /**
     * @test
     * @covers ::getHeaderLine
     */
    public function shouldReturnFormatedHeaderValues(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1',
                'header-value-2'
            ],
            'Another-Header' => []
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $this->assertSame('header-value-1,header-value-2', $message->getHeaderLine('header-name'));
        $this->assertSame('', $message->getHeaderLine('Another-Header'));
    }

    /**
     * @test
     * @covers ::getHeaderLine
     */
    public function shouldReturnEmptyStringIfHeaderNotExists(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $this->getBodyMock());

        $this->assertSame('', $message->getHeaderLine('nokey'));
    }

    /**
     * @test
     * @covers ::getHeaderLine
     * @covers ::getHeaderKey
     */
    public function getHeaderLineShouldTreatHeadersCaseInsensitively(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1',
                'header-value-2'
            ]
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $this->assertSame('header-value-1,header-value-2', $message->getHeaderLine('header-name'));
        $this->assertSame('header-value-1,header-value-2', $message->getHeaderLine('Header-Name'));
    }

    /**
     * @test
     * @covers ::withHeader
     * @covers ::isValidHeaderName
     * @covers ::isValidHeaderValue
     */
    public function shouldReturnNewInstanceWithReplacedHeader(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1',
                'header-value-2'
            ],
            'Another-Header' => []
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $messageWithHeader = $message->withHeader('header-name', 'new-value-1');

        $this->assertInstanceOf(MessageInterface::class, $messageWithHeader);
        $this->assertCount(0, $messageWithHeader->getHeader('Another-Header'));
        $this->assertCount(1, $messageWithHeader->getHeader('header-name'));
        $this->assertContains('new-value-1', $messageWithHeader->getHeader('header-name'));
    }

    /**
     * @test
     * @covers ::withHeader
     * @covers ::isValidHeaderName
     * @covers ::isValidHeaderValue
     */
    public function withHeaderShouldCreateHeaderIfNotExists(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getCollectionImplementation(), $this->getBodyMock());

        $messageWithHeader = $message->withHeader('header-name', 'new-value-1');

        $this->assertTrue($messageWithHeader->hasHeader('header-name'));
        $this->assertCount(1, $messageWithHeader->getHeader('header-name'));
        $this->assertContains('new-value-1', $messageWithHeader->getHeader('header-name'));
    }

    /**
     * @test
     * @covers ::withHeader
     * @covers ::isValidHeaderName
     * @expectedException \InvalidArgumentException
     */
    public function withHeaderShouldThrowExceptionIfInvalidHeaderName(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $this->getBodyMock());

        $message->withHeader('invalid-header-name-' . chr(127), 'value');
    }

    /**
     * @test
     * @covers ::withHeader
     * @covers ::isValidHeaderValue
     * @expectedException \InvalidArgumentException
     */
    public function withHeaderShouldThrowExceptionIfInvalidHeaderValue(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $this->getBodyMock());

        $message->withHeader('header-name', "Invalid \n header value");
    }

    /**
     * @test
     * @covers ::withHeader
     * @covers ::__clone
     */
    public function withHeaderShouldPreserveTheOriginalMessage(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getCollectionImplementation(), $this->getBodyMock());

        $messageWithHeader = $message->withHeader('new-header', 'new-value-1');

        $this->assertNotSame($message, $messageWithHeader);
        $this->assertFalse($message->hasHeader('new-header'));
    }

    /**
     * @test
     * @covers ::withHeader
     * @covers ::getHeaderKey
     */
    public function withHeaderShouldTreatHeadersCaseInsensitively(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getCollectionImplementation(), $this->getBodyMock());

        $messageWithHeader = $message->withHeader('Header-Name', 'new-value-1');

        $this->assertCount(1, $messageWithHeader->getHeader('header-name'));
        $this->assertContains('new-value-1', $messageWithHeader->getHeader('header-name'));
    }


    /**
     * @test
     * @covers ::withAddedHeader
     * @covers ::isValidHeaderName
     * @covers ::isValidHeaderValue
     */
    public function shouldReturnNewInstanceWithAddedHeader(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1'
            ]
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $messageWithAddedHeader = $message->withAddedHeader('header-name', 'header-value-2');

        $this->assertInstanceOf(MessageInterface::class, $messageWithAddedHeader);
        $this->assertCount(2, $messageWithAddedHeader->getHeader('header-name'));
        $this->assertContains('header-value-1', $messageWithAddedHeader->getHeader('header-name'));
        $this->assertContains('header-value-2', $messageWithAddedHeader->getHeader('header-name'));
    }

    /**
     * @test
     * @covers ::withAddedHeader
     * @covers ::isValidHeaderName
     * @covers ::isValidHeaderValue
     */
    public function withAddedHeaderShouldCreateHeaderIfNotExists(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getCollectionImplementation(), $this->getBodyMock());

        $messageWithAddedHeader = $message->withAddedHeader('header-name', 'new-value-1');

        $this->assertTrue($messageWithAddedHeader->hasHeader('header-name'));
        $this->assertCount(1, $messageWithAddedHeader->getHeader('header-name'));
        $this->assertContains('new-value-1', $messageWithAddedHeader->getHeader('header-name'));
    }

    /**
     * @test
     * @covers ::withAddedHeader
     * @covers ::isValidHeaderName
     * @expectedException \InvalidArgumentException
     */
    public function withAddedHeaderShouldThrowExceptionIfInvalidHeaderName(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $this->getBodyMock());

        $message->withAddedHeader('invalid-header-name-' . chr(127), 'value');
    }

    /**
     * @test
     * @covers ::withAddedHeader
     * @covers ::isValidHeaderValue
     * @expectedException \InvalidArgumentException
     */
    public function withAddedHeaderShouldThrowExceptionIfInvalidHeaderValue(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $this->getBodyMock());

        $message->withAddedHeader('header-name', "Invalid \n header value");
    }

    /**
     * @test
     * @covers ::withAddedHeader
     * @covers ::__clone
     */
    public function withAddedHeaderShouldPreserveTheOriginalMessage(): void
    {
        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getCollectionImplementation(), $this->getBodyMock());

        $messageWithHeader = $message->withAddedHeader('new-header', 'new-value-1');

        $this->assertNotSame($message, $messageWithHeader);
        $this->assertFalse($message->hasHeader('new-header'));
    }

    /**
     * @test
     * @covers ::withAddedHeader
     * @covers ::getHeaderKey
     */
    public function withAddedHeaderShouldTreatHeadersCaseInsensitively(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1'
            ]
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $messageWithAddedHeader = $message->withAddedHeader('Header-Name', 'header-value-2');

        $this->assertCount(2, $messageWithAddedHeader->getHeader('header-name'));
        $this->assertContains('header-value-1', $messageWithAddedHeader->getHeader('header-name'));
        $this->assertContains('header-value-2', $messageWithAddedHeader->getHeader('header-name'));
    }

    /**
     * @test
     * @covers ::withoutHeader
     */
    public function shouldReturnNewInstanceWithoutHeader(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1',
                'header-value-2'
            ],
            'Another-Header' => []
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $messageWithoutHeader = $message->withoutHeader('header-name');

        $this->assertInstanceOf(MessageInterface::class, $messageWithoutHeader);
        $this->assertFalse($messageWithoutHeader->hasHeader('header-name'));
        $this->assertTrue($messageWithoutHeader->hasHeader('Another-Header'));
    }

    /**
     * @test
     * @covers ::withoutHeader
     * @covers ::__clone
     */
    public function withoutHeaderShouldPreserveTheOriginalMessage(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [],
            'Another-Header' => []
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $messageWithoutHeader = $message->withoutHeader('header-name');

        $this->assertNotSame($message, $messageWithoutHeader);
        $this->assertTrue($message->hasHeader('header-name'));
    }

    /**
     * @test
     * @covers ::withoutHeader
     * @covers ::getHeaderKey
     */
    public function withoutHeaderShouldTreatHeadersCaseInsensitively(): void
    {
        $headersMock = $this->getCollectionImplementation([
            'header-name' => [
                'header-value-1',
                'header-value-2'
            ],
            'Another-Header' => []
        ]);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $headersMock, $this->getBodyMock());

        $messageWithoutHeader = $message->withoutHeader('Header-Name');

        $this->assertFalse($messageWithoutHeader->hasHeader('header-name'));
        $this->assertTrue($messageWithoutHeader->hasHeader('Another-Header'));
    }

    /**
     * @test
     * @covers ::getBody
     * @covers ::__construct
     */
    public function shouldReturnBody()
    {
        $bodyMock = $this->getBodyMock();

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $bodyMock);

        $this->assertSame($bodyMock, $message->getBody());
    }

    /**
     * @test
     * @covers ::withBody
     */
    public function shouldReturnNewInstanceWithBody()
    {
        $bodyMock1 = $this->getBodyMock();
        $bodyMock2 = $this->getBodyMock();

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $bodyMock1);

        /** @noinspection PhpParamsInspection */
        $messageWithBody = $message->withBody($bodyMock2);

        $this->assertInstanceOf(MessageInterface::class, $messageWithBody);
        $this->assertSame($bodyMock2, $messageWithBody->getBody());
    }

    /**
     * @test
     * @covers ::withBody
     * @covers ::__clone
     */
    public function withBodyShouldPreserveTheOriginalMessage(): void
    {
        $bodyMock1 = $this->getBodyMock();
        $bodyMock2 = $this->getBodyMock();

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getHeadersMock(), $bodyMock1);

        /** @noinspection PhpParamsInspection */
        $messageWithBody = $message->withBody($bodyMock2);

        $this->assertNotSame($message, $messageWithBody);
        $this->assertSame($bodyMock1, $message->getBody());
    }

    /**
     * @test
     * @covers ::withBody
     */
    public function withBodyShouldUpdateContentLengthHeader(): void
    {
        $bodyMock1 = $this->getBodyMock();
        $bodyMock1->method('getSize')->willReturn(0);

        $bodyMock2 = $this->getBodyMock();
        $bodyMock2->method('getSize')->willReturn(1024);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getCollectionImplementation(), $bodyMock1);

        /** @noinspection PhpParamsInspection */
        $messageWithBody = $message->withBody($bodyMock2);

        $this->assertSame('1024', $messageWithBody->getHeaderLine('Content-Length'));
    }

    /**
     * @test
     * @covers ::withBody
     */
    public function shouldSetInitialContentLengthHeaderIfNotPresent(): void
    {
        $bodyMock1 = $this->getBodyMock();
        $bodyMock1->method('getSize')->willReturn(1024);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getCollectionImplementation(), $bodyMock1);

        $this->assertSame('1024', $message->getHeaderLine('Content-Length'));
    }

    /**
     * @test
     * @covers ::withBody
     */
    public function shouldNotSetInitialContentLengthHeaderIfAlreadyPresent(): void
    {
        $bodyMock1 = $this->getBodyMock();
        $bodyMock1->method('getSize')->willReturn(1024);

        /** @noinspection PhpParamsInspection */
        $message = new Message('1.1', $this->getCollectionImplementation(['content-length' => [24]]), $bodyMock1);

        $this->assertSame('24', $message->getHeaderLine('Content-Length'));
    }
}
