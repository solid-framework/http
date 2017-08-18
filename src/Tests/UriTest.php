<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Solid\Http\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @coversDefaultClass Solid\Http\Uri
 */
class UriTest extends TestCase
{
    /**
     * @since 0.1.0
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrUriInterface(): void
    {
        $this->assertContains(UriInterface::class, class_implements(Uri::class));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getScheme
     * @covers ::__construct
     */
    public function shouldReturnScheme(): void
    {
        $uri = new Uri('http');

        $this->assertSame('http', $uri->getScheme());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getScheme
     * @covers ::__construct
     */
    public function schemeShouldBeLowerCase(): void
    {
        $uri = new Uri('HTtP');

        $this->assertSame('http', $uri->getScheme());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getScheme
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoScheme(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getScheme());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getHost
     * @covers ::__construct
     */
    public function shouldReturnHost(): void
    {
        $uri = new Uri(null, null, null, 'solid-framework.com');

        $this->assertSame('solid-framework.com', $uri->getHost());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getHost
     * @covers ::__construct
     */
    public function hostShouldBeLowerCase(): void
    {
        $uri = new Uri(null, null, null, 'Solid-Framework.com');

        $this->assertSame('solid-framework.com', $uri->getHost());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getHost
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoHost(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getHost());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnNullIfNoPortIsPresent(): void
    {
        $uri = new Uri('http');

        $this->assertNull($uri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnNullIfNoPortOrSchemeIsPresent(): void
    {
        $uri = new Uri(null, null, null, 'solid-framework.com');

        $this->assertNull($uri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnNullIfStandardPort(): void
    {
        $uri = new Uri('http', null, null, 'solid-framework.com', 80);
        $sslUri = new Uri('https', null, null, 'solid-framework.com', 443);

        $this->assertNull($uri->getPort());
        $this->assertNull($sslUri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnPortIfNonStandard(): void
    {
        $uri = new Uri('http', null, null, 'solid-framework.com', 8080);
        $this->assertSame(8080, $uri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnPort(): void
    {
        $uri = new Uri(null, null, null, null, 8080);

        $this->assertSame(8080, $uri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPath
     * @covers ::__construct
     */
    public function shouldReturnThePath(): void
    {
        $uri = new Uri(null, null, null, null, null, '/some/path');

        $this->assertSame('/some/path', $uri->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPath
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoPath(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPath
     * @covers ::__construct
     */
    public function shouldNotNormalizePath(): void
    {
        $withSlash = new Uri(null, null, null, null, null, '/');
        $withoutSlash = new Uri();

        $this->assertSame('/', $withSlash->getPath());
        $this->assertSame('', $withoutSlash->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPath
     * @covers ::__construct
     */
    public function shouldPercentEncodePath(): void
    {
        $uri = new Uri(null, null, null, null, null, '/some/path with spaces');

        $this->assertSame('/some/path%20with%20spaces', $uri->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPath
     * @covers ::__construct
     */
    public function shouldNotDoubleEncodePath(): void
    {
        $uri = new Uri(null, null, null, null, null, '/path%2F');

        $this->assertSame('/path%2F', $uri->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getQuery
     * @covers ::__construct
     */
    public function shouldReturnTheQuery(): void
    {
        $uri = new Uri(null, null, null, null, null, null, 'key=value');

        $this->assertSame('key=value', $uri->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getQuery
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoQuery(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getQuery
     * @covers ::__construct
     */
    public function shouldPercentEncodeQuery(): void
    {
        $uri = new Uri(null, null, null, null, null, '/path', 'key=value&another-key=value with spaces');

        $this->assertSame('key=value&another-key=value%20with%20spaces', $uri->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getQuery
     * @covers ::__construct
     */
    public function shouldNotDoubleEncodeQuery(): void
    {
        $uri = new Uri(null, null, null, null, null, '/path', 'key=value%2F');

        $this->assertSame('key=value%2F', $uri->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getFragment
     * @covers ::__construct
     */
    public function shouldReturnFragment(): void
    {
        $uri = new Uri(null, null, null, null, null, null, null, 'fragment');

        $this->assertSame('fragment', $uri->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getFragment
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoFragment(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getFragment
     * @covers ::__construct
     */
    public function shouldPercentEncodeFragment(): void
    {
        $uri = new Uri(null, null, null, null, null, null, null, 'fragment with spaces');

        $this->assertSame('fragment%20with%20spaces', $uri->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getFragment
     * @covers ::__construct
     */
    public function shouldNotDoubleEncodeFragment(): void
    {
        $uri = new Uri(null, null, null, null, null, null, null, 'fragment%2F');

        $this->assertSame('fragment%2F', $uri->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getUserInfo
     * @covers ::__construct
     */
    public function shouldReturnUserInfo(): void
    {
        $uri = new Uri(null, 'username', 'password');

        $this->assertSame('username:password', $uri->getUserInfo());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getUserInfo
     * @covers ::__construct
     */
    public function shouldReturnUserInfoUsernameOnlyIfPresent(): void
    {
        $uri = new Uri(null, 'username');

        $this->assertSame('username', $uri->getUserInfo());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getUserInfo
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoUserInfo(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getUserInfo());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getAuthority
     * @covers ::__construct
     */
    public function shouldReturnAuthority(): void
    {
        $uri = new Uri(null, 'username', 'password', 'solid-framework.com', 8080);

        $this->assertSame('username:password@solid-framework.com:8080', $uri->getAuthority());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getAuthority
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoAuthority(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getAuthority());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getAuthority
     * @covers ::__construct
     */
    public function shouldOmitOptionalUserInfoIfNotPresent(): void
    {
        $uri = new Uri(null, null, null, 'solid-framework.com', 8080);

        $this->assertSame('solid-framework.com:8080', $uri->getAuthority());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getAuthority
     * @covers ::__construct
     */
    public function shouldOmitOptionalPortIfNotPresentOrStandard(): void
    {
        $uri = new Uri(null, null, null, 'solid-framework.com');

        $this->assertSame('solid-framework.com', $uri->getAuthority());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::__toString
     * @covers ::__construct
     */
    public function shouldRenderCorrectlyAsString(): void
    {
        $this->assertSame('', (string)(new Uri()));
        $this->assertSame(
            'http://username:password@www.solid-framework.com:8080/path?key=value#fragment',
            (string)(new Uri(
                'http',
                'username',
                'password',
                'www.solid-framework.com',
                8080,
                '/path',
                'key=value',
                'fragment'
            ))
        );
        $this->assertSame('//solid-framework.com', (string)(new Uri(null, null, null, 'solid-framework.com')));
        $this->assertSame(
            '//username@solid-framework.com',
            (string)(new Uri(null, 'username', null, 'solid-framework.com'))
        );
        $this->assertSame(
            'http:path',
            (string)new Uri(
                'http',
                null,
                null,
                null,
                null,
                'path',
                null,
                null
            )
        );
        $this->assertSame(
            'http:/path',
            (string)new Uri(
                'http',
                null,
                null,
                null,
                null,
                '//path',
                null,
                null
            )
        );
        $this->assertSame(
            '//solid-framework.com/path',
            (string)new Uri(
                null,
                null,
                null,
                'solid-framework.com',
                null,
                'path',
                null,
                null
            )
        );
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withScheme
     * @covers ::normalizeScheme
     */
    public function shouldReturnNewInstanceWithScheme(): void
    {
        $uri = new Uri('http', null, null, 'solid-framework.com');
        $uriWithHttps = $uri->withScheme('https');

        $this->assertInstanceOf(Uri::class, $uriWithHttps);
        $this->assertSame('https', $uriWithHttps->getScheme());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withScheme
     * @covers ::normalizeScheme
     */
    public function shouldSanitizeSchemeForNewInstance(): void
    {
        $uri = new Uri('http', null, null, 'solid-framework.com');
        $uriWithHttps = $uri->withScheme('hTTpS');

        $this->assertSame('https', $uriWithHttps->getScheme());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withScheme
     * @covers ::normalizeScheme
     */
    public function withSchemeShouldPreserveTheOriginalUri(): void
    {
        $uri = new Uri('http', null, null, 'solid-framework.com');
        $uriWithHttps = $uri->withScheme('https');

        $this->assertNotSame($uri, $uriWithHttps);
        $this->assertSame('http', $uri->getScheme());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withUserInfo
     */
    public function shouldReturnNewInstanceWithUserInfo(): void
    {
        $uri = new Uri(null, 'username', 'password', 'solid-framework.com');
        $uriWithUserInfo = $uri->withUserInfo('new-user', 'new-password');

        $this->assertInstanceOf(Uri::class, $uriWithUserInfo);
        $this->assertSame('new-user:new-password', $uriWithUserInfo->getUserInfo());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withUserInfo
     */
    public function withUserInfoShouldPreserveTheOriginalUri(): void
    {
        $uri = new Uri(null, 'username', 'password', 'solid-framework.com');
        $uriWithUserInfo = $uri->withUserInfo('new-user', 'new-password');

        $this->assertNotSame($uri, $uriWithUserInfo);
        $this->assertSame('username:password', $uri->getUserInfo());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withHost
     * @covers ::normalizeHost
     */
    public function shouldReturnNewInstanceWithHost(): void
    {
        $uri = new Uri(null, null, null, 'solid-framework.com');
        $uriWithHost = $uri->withHost('another-framework.com');

        $this->assertInstanceOf(Uri::class, $uriWithHost);
        $this->assertSame('another-framework.com', $uriWithHost->getHost());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withHost
     * @covers ::normalizeHost
     */
    public function shouldSanitizeHostForNewInstance(): void
    {
        $uri = new Uri(null, null, null, 'solid-framework.com');
        $uriWithHost = $uri->withHost('Another-Framework.COM');

        $this->assertSame('another-framework.com', $uriWithHost->getHost());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withHost
     * @covers ::normalizeHost
     */
    public function withHostShouldPreserveTheOriginalUri(): void
    {
        $uri = new Uri(null, null, null, 'solid-framework.com');
        $uriWithHost = $uri->withHost('another-framework.com');

        $this->assertNotSame($uri, $uriWithHost);
        $this->assertSame('solid-framework.com', $uri->getHost());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withPort
     */
    public function shouldReturnNewInstanceWithPort(): void
    {
        $uri = new Uri(null, null, null, null, 8080);
        $uriWithPort = $uri->withPort(8081);

        $this->assertInstanceOf(Uri::class, $uriWithPort);
        $this->assertSame(8081, $uriWithPort->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withPort
     */
    public function withPortShouldPreserveTheOriginalUri(): void
    {
        $uri = new Uri(null, null, null, null, 8080);
        $uriWithPort = $uri->withPort(8081);

        $this->assertNotSame($uri, $uriWithPort);
        $this->assertSame(8080, $uri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withPath
     * @covers ::encodePath
     */
    public function shouldReturnNewInstanceWithPath(): void
    {
        $uri = new Uri(null, null, null, null, null, '/path');
        $uriWithPath = $uri->withPath('/new/path');

        $this->assertInstanceOf(Uri::class, $uriWithPath);
        $this->assertSame('/new/path', $uriWithPath->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withPath
     * @covers ::encodePath
     */
    public function shouldSanitizePathForNewInstance(): void
    {
        $uri = new Uri(null, null, null, null, null, '/path');
        $uriWithPath = $uri->withPath('/new path%2F');

        $this->assertSame('/new%20path%2F', $uriWithPath->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withPath
     * @covers ::encodePath
     */
    public function withPathShouldPreserveTheOriginalUri(): void
    {
        $uri = new Uri(null, null, null, null, null, '/path');
        $uriWithPath = $uri->withPath('/new/path');

        $this->assertNotSame($uri, $uriWithPath);
        $this->assertSame('/path', $uri->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withQuery
     * @covers ::encodeQuery
     */
    public function shouldReturnNewInstanceWithQuery()
    {
        $uri = new Uri(null, null, null, null, null, null, 'key=value');
        $uriWithQuery = $uri->withQuery('new-key=new-value');

        $this->assertInstanceOf(Uri::class, $uriWithQuery);
        $this->assertSame('new-key=new-value', $uriWithQuery->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withQuery
     * @covers ::encodeQuery
     */
    public function shouldSanitizeQueryForNewInstance()
    {
        $uri = new Uri(null, null, null, null, null, null, 'key=value');
        $uriWithQuery = $uri->withQuery('new-key%2F=new value');

        $this->assertSame('new-key%2F=new%20value', $uriWithQuery->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withQuery
     * @covers ::encodeQuery
     */
    public function withQueryShouldPreserveTheOriginalUri(): void
    {
        $uri = new Uri(null, null, null, null, null, null, 'key=value');
        $uriWithQuery = $uri->withQuery('new-key=new-value');

        $this->assertNotSame($uri, $uriWithQuery);
        $this->assertSame('key=value', $uri->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withFragment
     * @covers ::encodeFragment
     */
    public function shouldReturnNewInstanceWithFragment()
    {
        $uri = new Uri(null, null, null, null, null, null, null, 'fragment');
        $uriWithFragment = $uri->withFragment('new-fragment');

        $this->assertInstanceOf(Uri::class, $uriWithFragment);
        $this->assertSame('new-fragment', $uriWithFragment->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withFragment
     * @covers ::encodeFragment
     */
    public function shouldSanitizeFragmentForNewInstance()
    {
        $uri = new Uri(null, null, null, null, null, null, null, 'fragment');
        $uriWithFragment = $uri->withFragment('new fragment%2F');

        $this->assertSame('new%20fragment%2F', $uriWithFragment->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withFragment
     * @covers ::encodeFragment
     */
    public function withFragmentShouldPreserveTheOriginalUri(): void
    {
        $uri = new Uri(null, null, null, null, null, null, null, 'fragment');
        $uriWithFragment = $uri->withFragment('new-fragment');

        $this->assertNotSame($uri, $uriWithFragment);
        $this->assertSame('fragment', $uri->getFragment());
    }
}
