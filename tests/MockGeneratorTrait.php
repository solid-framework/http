<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Psr\Http\Message\UploadedFileInterface;
use Solid\Collection\CollectionInterface;
use Iterator;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use ReflectionClass;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 */
trait MockGeneratorTrait
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getUriMock(): PHPUnit_Framework_MockObject_MockObject
    {
        /** @var TestCase $this */
        return $this->getMockBuilder(UriInterface::class)
                    ->setMethods((new ReflectionClass(UriInterface::class))->getMethods(null))
                    ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHeadersMock(): PHPUnit_Framework_MockObject_MockObject
    {
        /** @var TestCase $this */
        $headersMock = $this->getMockBuilder(CollectionInterface::class)
                            ->setMethods((new ReflectionClass(CollectionInterface::class))->getMethods(null))
                            ->getMock();
        $headersMock->method('getIterator')->willReturn($this->getMockBuilder(Iterator::class)->getMock());

        return $headersMock;
    }

    /**
     * @param array $defaultStore
     * @return \Solid\Collection\CollectionInterface
     */
    public function getCollectionImplementation($defaultStore = []): CollectionInterface
    {
        return new Fixtures\CollectionInterfaceImplementation($defaultStore);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBodyMock(): PHPUnit_Framework_MockObject_MockObject
    {
        /** @var TestCase $this */
        return $this->getMockBuilder(StreamInterface::class)
                    ->setMethods((new ReflectionClass(StreamInterface::class))->getMethods(null))
                    ->getMock();
    }

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getUploadedFileMock(): PHPUnit_Framework_MockObject_MockObject
	{
		/** @var TestCase $this */
		return $this->getMockBuilder(UploadedFileInterface::class)
		            ->setMethods((new ReflectionClass(UploadedFileInterface::class))->getMethods(null))
		            ->getMock();
	}
}
