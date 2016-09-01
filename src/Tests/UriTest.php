<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use PHPUnit\Framework\TestCase;
use Solid\Http\Uri;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @coversDefaultClass Solid\Http\Uri
 */
class UriTest extends TestCase
{
    /**
     * @api
     * @test
     * @covers ::__construct
     * @expectedException InvalidArgumentException
     * @since 0.1.0
     * @return void
     */
    public function testInvalidPort()
    {
        $uri = new Uri('example.com:3000');
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @covers ::getScheme
     * @since 0.1.0
     * @return void
     */
    public function testScheme()
    {
        $empty = new Uri;
        $this->assertSame('', $empty->getScheme(), 'Should return an empty string if there is no scheme');

        $noScheme = new Uri('example.com');
        $this->assertSame('', $noScheme->getScheme(), 'Should return an empty string if there is no scheme');

        $httpLoserCase = new Uri('http://example.com');
        $this->assertSame('http', $httpLoserCase->getScheme(), 'Should return the correct scheme');

        $httpsMixedCase = new Uri('HtTpS://example.com');
        $this->assertSame('https', $httpsMixedCase->getScheme(), 'Should return the scheme in lowercase');
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @covers ::getAuthority
     * @since 0.1.0
     * @return void
     */
    public function testAuthority()
    {
        $empty = new Uri;
        $this->assertSame('', $empty->getAuthority(), 'Should return an empty string if there is no authority');

        $hostOnly = new Uri('example.com');
        $this->assertSame('example.com', $hostOnly->getAuthority(), 'Should return the correct authority string');

        $hostAndPort = new Uri('example.com:80');
        $this->assertSame(
            'example.com:80',
            $hostAndPort->getAuthority(),
            'Should return the correct authority string'
        );

        $hostAndStandardPort1 = new Uri('http://example.com:80');
        $this->assertSame(
            'example.com',
            $hostAndStandardPort1->getAuthority(),
            'Should omit protocol standard ports'
        );
        $hostAndStandardPort2 = new Uri('https://example.com:443');
        $this->assertSame(
            'example.com',
            $hostAndStandardPort2->getAuthority(),
            'Should omit protocol standard ports'
        );

        $userAndHost = new Uri('username@example.com');
        $this->assertSame(
            'username@example.com',
            $userAndHost->getAuthority(),
            'Should return the correct authority string'
        );

        $userPasswordAndHost = new Uri('username:password@example.com');
        $this->assertSame(
            'username:password@example.com',
            $userPasswordAndHost->getAuthority(),
            'Should return the correct authority string'
        );
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @covers ::getUserInfo
     * @since 0.1.0
     * @return void
     */
    public function testUserInfo()
    {
        $empty = new Uri;
        $this->assertSame('', $empty->getUserInfo(), 'Should return an empty string if there is no user info');

        $username = new Uri('username@example.com');
        $this->assertSame(
            'username',
            $username->getUserInfo(),
            'Should return the correct user info string'
        );

        $usernameAndPassword = new Uri('username:password@example.com');
        $this->assertSame(
            'username:password',
            $usernameAndPassword->getUserInfo(),
            'Should return the correct user info string'
        );
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @covers ::getHost
     * @since 0.1.0
     * @return void
     */
    public function testGetHost()
    {
        $empty = new Uri;
        $this->assertSame('', $empty->getHost(), 'Should return an empty string if no host is present');

        $full = new Uri('http://username:password@example.com:22/path?query=string#hash');
        $this->assertSame('example.com', $full->getHost(), 'Should return correct host string');;

        $mixedCase = new Uri('ExaMplE.cOm/paTh');
        $this->assertSame('example.com', $mixedCase->getHost(), 'Should return correct host string');
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @covers ::isStandardPort
     * @covers ::getPort
     * @since 0.1.0
     * @return void
     */
    public function testGetPort()
    {
        $empty = new Uri;
        $this->assertNull($empty->getPort(), 'Should return null if no port is present');

        $noPort = new Uri('http://example.com');
        $this->assertNull($noPort->getPort(), 'Should return null if no port is present');

        $standardPort = new Uri('http://example.com:80');
        $this->assertNull($standardPort->getPort(), 'Should return null if port is protocol standard');

        $nonStandardPort = new Uri('https://example.com:80');
        $this->assertSame(80, $nonStandardPort->getPort(), 'Should return correct port');
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @covers ::getPath
     * @since 0.1.0
     * @return void
     */
    public function testGetPath()
    {
        $empty = new Uri;
        $this->assertSame('', $empty->getPath(), 'Should return an empty string if no path is present');

        $emptyPath = new Uri('example.com');
        $this->assertSame('', $emptyPath->getPath(), 'Should return correct path string');

        $rootPath = new Uri('example.com/');
        $this->assertSame('/', $rootPath->getPath(), 'Should return correct path string');

        $full = new Uri('http://username:password@example.com:22/test/path?query=string#hash');
        $this->assertSame('/test/path', $full->getPath(), 'Should return correct path string');

        $encoded = new Uri('example.com/test%2Fpath');
        $this->assertSame('/test%2Fpath', $encoded->getPath(), 'Should return correct path string');
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @covers ::getQuery
     * @since 0.1.0
     * @return void
     */
    public function testGetQuery()
    {
        $empty = new Uri;
        $this->assertSame('', $empty->getQuery(), 'Should return an empty string if no query is present');

        $full = new Uri('http://username:password@example.com:22/test/path?query=string#hash');
        $this->assertSame('query=string', $full->getQuery(), 'Should return correct query string');

        $encoded = new Uri('example.com?encoded=parameter%26value&parameter2=value2');
        $this->assertSame(
            'encoded=parameter%26value&parameter2=value2',
            $encoded->getQuery(),
            'Should return correct query string'
        );
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @covers ::getFragment
     * @since 0.1.0
     * @return void
     */
    public function testGetFragment()
    {
        $empty = new Uri;
        $this->assertSame('', $empty->getFragment(), 'Should return an empty string if no fragment is present');

        $fragment = new Uri('example.com#fragment');
        $this->assertSame('fragment', $fragment->getFragment(), 'Should return correct fragment string');

        $encoded = new Uri('example.com#encoded%26fragment with space');
        $this->assertSame(
            'encoded%26fragment%20with%20space',
            $encoded->getFragment(),
            'Should return correct fragment string'
        );
    }

    /**
     * @api
     * @test
     * @covers ::withScheme
     * @covers ::getScheme
     * @since 0.1.0
     * @return void
     */
    public function testWithScheme()
    {
        $uri = new Uri('http://example.com');

        $mixedCase = $uri->withScheme('HttPs');
        $this->assertInstanceOf('Solid\Http\Uri', $mixedCase, 'Should return a Uri instance');
        $this->assertNotSame($uri, $mixedCase, 'Should return a new instance');
        $this->assertSame('https', $mixedCase->getScheme(), 'Should be able to set new case insensitive scheme');

        $noScheme = $uri->withScheme('');
        $this->assertSame('', $noScheme->getScheme(), 'Should be able to remove the scheme');
    }

    /**
     * @api
     * @test
     * @covers ::withUserInfo
     * @covers ::getUserInfo
     * @since 0.1.0
     * @return void
     */
    public function testWithUserInfo()
    {
        $uri = new Uri('username:password@example.com');

        $noPassword = $uri->withUserInfo('new-username');
        $this->assertInstanceOf('Solid\Http\Uri', $noPassword, 'Should return a Uri instance');
        $this->assertNotSame($uri, $noPassword, 'Should return a new instance');
        $this->assertSame('new-username', $noPassword->getUserInfo(), 'Should be able to set new user info');

        $userPass = $uri->withUserInfo('new-username', 'new-password');
        $this->assertSame(
            'new-username:new-password',
            $userPass->getUserInfo(),
            'Should be able to set new user info'
        );

        $noInfo = $uri->withUserInfo('');
        $this->assertSame('', $noInfo->getUserInfo(), 'Should be able to remove the user info');
    }

    /**
     * @api
     * @test
     * @covers ::withHost
     * @covers ::getHost
     * @since 0.1.0
     * @return void
     */
    public function testWithHost()
    {
        $uri = new Uri('http://username:password@example.com:22/path?query=string#hash');

        $newHost = $uri->withHost('another-example.com');
        $this->assertInstanceOf('Solid\Http\Uri', $newHost, 'Should return a Uri instance');
        $this->assertNotSame($uri, $newHost, 'Should return a new instance');
        $this->assertSame('another-example.com', $newHost->getHost(), 'Should be able to set new host');
    }

    /**
     * @api
     * @test
     * @covers ::withPort
     * @covers ::checkPortRange
     * @covers ::isStandardPort
     * @covers ::getPort
     * @since 0.1.0
     * @return void
     */
    public function testWithPort()
    {
        $uri = new Uri('example.com:22');

        $newPort = $uri->withPort(80);
        $this->assertInstanceOf('Solid\Http\Uri', $newPort, 'Should return a Uri instance');
        $this->assertNotSame($uri, $newPort, 'Should return a new instance');
        $this->assertSame(80, $newPort->getPort(), 'Should be able to set new port');

        $noPort = $uri->withPort(null);
        $this->assertNull($noPort->getPort(), 'Should be able to remove the port');
    }

    /**
     * @api
     * @test
     * @covers ::withPort
     * @covers ::checkPortRange
     * @expectedException InvalidArgumentException
     * @since 0.1.0
     * @return void
     */
    public function testWithInvalidPort()
    {
        $uri = new Uri('example.com:22');
        $newPort = $uri->withPort(3000);
    }

    /**
     * @api
     * @test
     * @covers ::withPath
     * @covers ::getPath
     * @since 0.1.0
     * @return void
     */
    public function testWithPath()
    {
        $uri = new Uri('example.com/test/path');

        $newPath = $uri->withPath('new/path');
        $this->assertInstanceOf('Solid\Http\Uri', $newPath, 'Should return a Uri instance');
        $this->assertNotSame($uri, $newPath);
        $this->assertSame('new/path', $newPath->getPath(), 'Should be able to set new relative path');

        $noPath = $uri->withPath('');
        $this->assertSame('', $noPath->getPath(), 'Should be able to remove path');

        $absoluteEncoded = $uri->withPath('/absolute/encoded%2Fpath');
        $this->assertSame(
            '/absolute/encoded%2Fpath',
            $absoluteEncoded->getPath(),
            'Should be able to set absolute path'
        );
    }

    /**
     * @api
     * @test
     * @covers ::withQuery
     * @covers ::__clone
     * @covers ::getQuery
     * @since 0.1.0
     * @return void
     */
    public function testWithQuery()
    {
        $uri = new Uri('example.com?parameter=value');

        $newQuery = $uri->withQuery('parameter=value&encoded%26parameter=value2');
        $this->assertInstanceOf('Solid\Http\Uri', $newQuery, 'Should return a Uri instance');
        $this->assertNotSame($uri, $newQuery);
        $this->assertSame(
            'parameter=value&encoded%26parameter=value2',
            $newQuery->getQuery(),
            'Should be able to set new query'
        );

        $noQuery = $uri->withQuery('');
        $this->assertSame('', $noQuery->getQuery(), 'Should be able to remove query');
    }

    /**
     * @api
     * @test
     * @covers ::withFragment
     * @covers ::getFragment
     * @since 0.1.0
     * @return void
     */
    public function testWithFragment()
    {
        $uri = new Uri('example.com#fragment');

        $newFragment = $uri->withFragment('new%26encoded fragment');
        $this->assertInstanceOf('Solid\Http\Uri', $newFragment, 'Should return a Uri instance');
        $this->assertNotSame($uri, $newFragment);
        $this->assertSame(
            'new%26encoded%20fragment',
            $newFragment->getFragment(),
            'Should be able to set new fragment'
        );

        $noFragment = $uri->withFragment('');
        $this->assertSame('', $noFragment->getFragment(), 'Should be able to remove fragment');
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
        $empty = new Uri;
        $example = new Uri('example.com');
        $full = new Uri('http://username:password@example.com:22/path?query=string#hash');

        $this->assertSame(
            'http://username:password@example.com:22/path?query=string#hash',
            (string) $full,
            'Should render as a string correctly'
        );

        $noScheme = new Uri('username:password@example.com');
        $this->assertSame(
            '//username:password@example.com',
            (string) $noScheme,
            'Should render as a string correctly'
        );

        $relativePath = $example->withPath('relative/path');
        $this->assertSame(
            '//example.com/relative/path',
            (string) $relativePath,
            'Should render as a string correctly'
        );

        $doubleRoot = $example->withPath('//double/path');
        $this->assertSame(
            '//example.com//double/path',
            (string) $doubleRoot,
            'Should render as a string correctly'
        );

        $doubleRootNoAuthority = $empty->withPath('//double/path');
        $this->assertSame(
            '/double/path',
            (string) $doubleRootNoAuthority,
            'Should render as a string correctly'
        );
    }
}
