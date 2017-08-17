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
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnScheme(): void
    {
        $uri = Uri::fromString('http://solid-framework.com');

        $this->assertSame('http', $uri->getScheme());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getScheme
     * @covers ::fromString
     * @covers ::__construct
     */
    public function schemeShouldBeLowerCase(): void
    {
        $uri = Uri::fromString('HTtP://solid-framework.com');

        $this->assertSame('http', $uri->getScheme());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getScheme
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoScheme(): void
    {
        $uri = Uri::fromString('solid-framework.com');

        $this->assertSame('', $uri->getScheme());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getHost
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnHost(): void
    {
        $uri = Uri::fromString('solid-framework.com');

        $this->assertSame('solid-framework.com', $uri->getHost());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getHost
     * @covers ::fromString
     * @covers ::__construct
     */
    public function hostShouldBeLowerCase(): void
    {
        $uri = Uri::fromString('solid-framework.com');

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
        $uri = Uri::fromString('');

        $this->assertSame('', $uri->getHost());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::fromString
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnNullIfNoPortIsPresent(): void
    {
        $uri = Uri::fromString('some-protocol://solid-framework.com');

        $this->assertNull($uri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::fromString
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnNullIfNoPortOrSchemeIsPresent(): void
    {
        $uri = Uri::fromString('solid-framework.com');

        $this->assertNull($uri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::fromString
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnNullIfStandardPort(): void
    {
        $uri = Uri::fromString('http://solid-framework.com:80');
        $sslUri = Uri::fromString('https://solid-framework.com:443');

        $this->assertNull($uri->getPort());
        $this->assertNull($sslUri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::fromString
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnPortIfNonStandard(): void
    {
        $uri = Uri::fromString('http://solid-framework.com:8080');

        $this->assertSame(8080, $uri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPort
     * @covers ::fromString
     * @covers ::__construct
     * @covers ::isStandardPort
     */
    public function shouldReturnPort(): void
    {
        $uri = Uri::fromString('some-protocol://solid-framework.com:8080');

        $this->assertSame(8080, $uri->getPort());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPath
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnThePath(): void
    {
        $uri = Uri::fromString('solid-framework.com/some/path?some=parameters');

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
        $uri = Uri::fromString('');

        $this->assertSame('', $uri->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPath
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldNotNormalizePath(): void
    {
        $withSlash = Uri::fromString('solid-framework.com/');
        $withoutSlash = Uri::fromString('solid-framework.com');

        $this->assertSame('/', $withSlash->getPath());
        $this->assertSame('', $withoutSlash->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPath
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldPercentEncodePath(): void
    {
        $uri = Uri::fromString('solid-framework.com/some/path with spaces');

        $this->assertSame('/some/path%20with%20spaces', $uri->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getPath
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldNotDoubleEncodePath(): void
    {
        $uri = Uri::fromString('solid-framework.com/path%2F');

        $this->assertSame('/path%2F', $uri->getPath());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getQuery
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnTheQuery(): void
    {
        $uri = Uri::fromString('solid-framework.com?key=value');

        $this->assertSame('key=value', $uri->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getQuery
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoQuery(): void
    {
        $uri = Uri::fromString('solid-framework.com');

        $this->assertSame('', $uri->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getQuery
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldPercentEncodeQuery(): void
    {
        $uri = Uri::fromString('solid-framework.com/path?key=value&another-key=value with spaces');

        $this->assertSame('key=value&another-key=value%20with%20spaces', $uri->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getQuery
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldNotDoubleEncodeQuery(): void
    {
        $uri = Uri::fromString('solid-framework.com/path?key=value%2F');

        $this->assertSame('key=value%2F', $uri->getQuery());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getFragment
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnFragment(): void
    {
        $uri = Uri::fromString('solid-framework.com#fragment');

        $this->assertSame('fragment', $uri->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getFragment
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoFragment(): void
    {
        $uri = Uri::fromString('solid-framework.com');

        $this->assertSame('', $uri->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getFragment
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldPercentEncodeFragment(): void
    {
        $uri = Uri::fromString('solid-framework.com#fragment with spaces');

        $this->assertSame('fragment%20with%20spaces', $uri->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getFragment
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldDoubleEncodeFragment(): void
    {
        $uri = Uri::fromString('solid-framework.com#fragment%2F');

        $this->assertSame('fragment%2F', $uri->getFragment());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getUserInfo
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnUserInfo(): void
    {
        $uri = Uri::fromString('user:password@solid-framework.com');

        $this->assertSame('user:password', $uri->getUserInfo());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getUserInfo
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnUserInfoUsernameOnlyIfPresent(): void
    {
        $uri = Uri::fromString('user@solid-framework.com');

        $this->assertSame('user', $uri->getUserInfo());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getUserInfo
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoUserInfo(): void
    {
        $uri = Uri::fromString('solid-framework.com');

        $this->assertSame('', $uri->getUserInfo());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getAuthority
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnAuthority(): void
    {
        $uri = Uri::fromString('user:password@solid-framework.com:8080');

        $this->assertSame('user:password@solid-framework.com:8080', $uri->getAuthority());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getAuthority
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldReturnEmptyStringIfNoAuthority(): void
    {
        $uri = Uri::fromString('');

        $this->assertSame('', $uri->getAuthority());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getAuthority
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldOmitOptionalUserInfoIfNotPresent(): void
    {
        $uri = Uri::fromString('solid-framework.com:8080');

        $this->assertSame('solid-framework.com:8080', $uri->getAuthority());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getAuthority
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldOmitOptionalPortIfNotPresentOrStandard(): void
    {
        $uri = Uri::fromString('solid-framework.com');

        $this->assertSame('solid-framework.com', $uri->getAuthority());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::__toString
     * @covers ::fromString
     * @covers ::__construct
     */
    public function shouldRenderCorrectlyAsString(): void
    {
        $this->assertSame('', (string)Uri::fromString(''));
        $this->assertSame(
            'http://user:password@www.solid-framework.com:8080/path?key=value#fragment',
            (string)Uri::fromString('http://user:password@www.solid-framework.com:8080/path?key=value#fragment')
        );
        $this->assertSame('//solid-framework.com', (string)Uri::fromString('solid-framework.com'));
        $this->assertSame('//user@solid-framework.com', (string)Uri::fromString('user@solid-framework.com'));
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
        $uri = Uri::fromString('http://solid-framework.com');
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
        $uri = Uri::fromString('http://solid-framework.com');
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
        $uri = Uri::fromString('http://solid-framework.com');
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
        $uri = Uri::fromString('user:password@solid-framework.com');
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
        $uri = Uri::fromString('user:password@solid-framework.com');
        $uriWithUserInfo = $uri->withUserInfo('new-user', 'new-password');

        $this->assertNotSame($uri, $uriWithUserInfo);
        $this->assertSame('user:password', $uri->getUserInfo());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::withHost
     * @covers ::normalizeHost
     */
    public function shouldReturnNewInstanceWithHost(): void
    {
        $uri = Uri::fromString('http://solid-framework.com');
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
        $uri = Uri::fromString('http://solid-framework.com');
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
        $uri = Uri::fromString('http://solid-framework.com');
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
        $uri = Uri::fromString('solid-framework.com:8080');
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
        $uri = Uri::fromString('solid-framework.com:8080');
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
        $uri = Uri::fromString('solid-framework.com/path');
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
        $uri = Uri::fromString('solid-framework.com/path');
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
        $uri = Uri::fromString('solid-framework.com/path');
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
        $uri = Uri::fromString('solid-framework.com?key=value');
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
        $uri = Uri::fromString('solid-framework.com?key=value');
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
        $uri = Uri::fromString('solid-framework.com?key=value');
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
        $uri = Uri::fromString('solid-framework.com#fragment');
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
        $uri = Uri::fromString('solid-framework.com#fragment');
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
        $uri = Uri::fromString('solid-framework.com#fragment');
        $uriWithFragment = $uri->withFragment('new-fragment');

        $this->assertNotSame($uri, $uriWithFragment);
        $this->assertSame('fragment', $uri->getFragment());
    }
}
