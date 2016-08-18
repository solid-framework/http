<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use PHPUnit\Framework\TestCase;
use Solid\Http\QueryContainer;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class QueryContainerTest extends TestCase
{
    /**
     * @internal
     * @since 0.1.0
     * @var QueryContainer
     */
    protected $queryContainer;

    /**
     * @api
     * @before
     * @since 0.1.0
     * @return void
     */
    public function setup()
    {
        $this->queryContainer = new QueryContainer('param1=value1&param2=value2');
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testConstructor()
    {
        $this->assertEquals(
            'value1',
            $this->queryContainer->get('param1'),
            'Constructor should accept a query string'
        );
        $this->assertEquals(
            'value2',
            $this->queryContainer->get('param2'),
            'Constructor should accept a query string'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testHas()
    {
        $this->assertTrue(
            $this->queryContainer->has('param1'),
            'Should be able to determine if parameter exists'
        );
        $this->assertFalse(
            $this->queryContainer->has('non-existing-parameter'),
            'Should be able to determine if parameter exists'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testGet()
    {
        $this->assertEquals(
            'value1',
            $this->queryContainer->get('param1'),
            'Should be able to retrieve value by their key'
        );
        $this->assertNull(
            $this->queryContainer->get('non-existing-parameter'),
            'Should return null if the given parameter does not exist'
        );
        $this->assertEquals(
            'default',
            $this->queryContainer->get('non-existing-parameter', 'default'),
            'Should return the default value if one is given when a parameter is not found'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testSet()
    {
        $this->queryContainer->set('param1', '5');
        $this->assertEquals(
            '5',
            $this->queryContainer->get('param1'),
            'Should be able to set new parameter values'
        );

        $this->queryContainer->set('new-param', 'new-value');
        $this->assertEquals(
            'new-value',
            $this->queryContainer->get('new-param'),
            'Should create new parameters if needed'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testAsArray()
    {
        $this->queryContainer->set('new-param', 'new-value');
        $this->assertEquals(
            [
                'param1' => 'value1',
                'param2' => 'value2',
                'new-param' => 'new-value'
            ],
            $this->queryContainer->asArray(),
            'Should return all parameters as an array'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testToString()
    {
        $this->queryContainer->set('param3', 'Value 3');
        $this->assertEquals(
            'param1=value1&param2=value2&param3=Value%203',
            (string) $this->queryContainer,
            'Should render correctly as a string'
        );
    }
}
