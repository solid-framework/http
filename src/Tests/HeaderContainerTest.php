<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use PHPUnit\Framework\TestCase;
use Solid\Http\HeaderContainer;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @coversDefaultClass Solid\Http\HeaderContainer
 */
class HeaderContainerTest extends TestCase
{
    /**
     * @internal
     * @since 0.1.0
     * @var HeaderContainer
     */
    protected $headerContainer;

    /**
     * @api
     * @before
     * @since 0.1.0
     * @return void
     */
    public function setup()
    {
        $this->headerContainer = new HeaderContainer([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @since 0.1.0
     * @return void
     */
    public function testConstructor()
    {
        $this->assertEquals(
            [
                'Accept' => ['application/json'],
                'Content-Type' => ['application/json']
            ],
            $this->headerContainer->get(),
            'Constructor should accept header parameters'
        );

        $headerContainer = new HeaderContainer;

        $this->assertEquals(
            [],
            $headerContainer->get(),
            'Not passing header parameters to constructor should not break functionality'
        );
    }

    /**
     * @api
     * @test
     * @covers ::__toString
     * @since 0.1.0
     * @return void
     */
    public function testToString()
    {
        $header = <<<HEADER
Accept: application/json,text/html
Content-Type: application/json
HEADER;
        $this->headerContainer->add('accept', 'text/html');

        $this->assertEquals($header, (string) $this->headerContainer, 'Should render as a string properly');
    }

    /**
     * @api
     * @test
     * @covers ::has
     * @covers ::getHeaderKey
     * @since 0.1.0
     * @return void
     */
    public function testHas()
    {
        $this->assertTrue(
            $this->headerContainer->has('Content-Type'),
            'Should be able to determine if a header key exists'
        );
        $this->assertFalse(
            $this->headerContainer->has('non-existing-key'),
            'Should be able to determine if a header key exists'
        );
        $this->assertTrue(
            $this->headerContainer->has('accept'),
            'Should look for header keys case insensitive'
        );
    }

    /**
     * @api
     * @test
     * @covers ::get
     * @covers ::getHeaderKey
     * @since 0.1.0
     * @return void
     */
    public function testGet()
    {
        $this->assertEquals(
            [
                'Accept' => ['application/json'],
                'Content-Type' => ['application/json']
            ],
            $this->headerContainer->get(),
            'Should return all headers if no header key was given'
        );
        $this->assertEquals(
            ['application/json'],
            $this->headerContainer->get('Content-Type'),
            'Should return an array of header values'
        );
        $this->assertEquals(
            [],
            $this->headerContainer->get('non-existing-header'),
            'Should return an empty array of no header key was found'
        );
        $this->assertEquals(
            ['application/json'],
            $this->headerContainer->get('content-type'),
            'Should return header values based on case insensitive keys'
        );

        $this->headerContainer->add('test', 'test header 1');
        $this->headerContainer->add('test', 'test header 2');

        $this->assertEquals(
            [
                'test header 1',
                'test header 2'
            ],
            $this->headerContainer->get('test'),
            'Should return an array of header values'
        );
    }

    /**
     * @api
     * @test
     * @covers ::set
     * @covers ::getHeaderKey
     * @since 0.1.0
     * @return void
     */
    public function testSet()
    {
        $this->headerContainer->set('Content-Type', 'test/type');

        $this->assertEquals(
            ['test/type'],
            $this->headerContainer->get('Content-Type'),
            'Should replace values when setting them'
        );
    }

    /**
     * @api
     * @test
     * @covers ::add
     * @covers ::getHeaderKey
     * @since 0.1.0
     * @return void
     */
    public function testAdd()
    {
        $this->headerContainer->add('content-type', 'test/type');

        $this->assertEquals(
            ['application/json', 'test/type'],
            $this->headerContainer->get('Content-Type'),
            'Should add values to the first key created (case sensitive)'
        );

        $this->headerContainer->add('test', 'value1');
        $this->headerContainer->add('TeSt', 'value2');

        $this->assertEquals(
            ['value1', 'value2'],
            $this->headerContainer->get('TesT'),
            'Should create new header keys when adding if they do not already exist'
        );
    }

    /**
     * @api
     * @test
     * @covers ::remove
     * @covers ::getHeaderKey
     * @since 0.1.0
     * @return void
     */
    public function testRemove()
    {
        // make sure the headers are there to begin with
        $this->assertTrue($this->headerContainer->has('Content-Type'));
        $this->assertTrue($this->headerContainer->has('Accept'));

        $this->headerContainer->remove('Content-Type');
        $this->assertFalse(
            $this->headerContainer->has('Content-Type'),
            'Should be able to remove header keys'
        );

        $this->headerContainer->remove('accept');
        $this->assertFalse(
            $this->headerContainer->has('Accept'),
            'Should be able to remove header keys case insensitive'
        );
    }
}
