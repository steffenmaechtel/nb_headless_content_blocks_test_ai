<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TypolinkParameterToArrayTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testValidLinkReturnsUrlAndTarget(): void
    {
        $typolinkParameter = new TypolinkParameter('https://example.com', 'target', 'title');
        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('url', $result);
        self::assertArrayHasKey('target', $result);
        self::assertArrayHasKey('type', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('config', $result);
        self::assertArrayHasKey('attr', $result);
    }

    public function testEmptyUrlReturnsNull(): void
    {
        $typolinkParameter = new TypolinkParameter('', 'target', 'title');
        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertNull($result);
    }

    public function testZeroUrlReturnsNull(): void
    {
        $typolinkParameter = new TypolinkParameter('0', 'target', 'title');
        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertNull($result);
    }

    public function testUrlFromUriInterface(): void
    {
        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->expects(self::once())
            ->method('getUrl')
            ->willReturn('https://example.com');

        $typolinkParameter = new TypolinkParameter('', 'target', 'title');
        $typolinkParameter->url = $uriMock;

        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('url', $result);
    }

    public function testTargetFromUriInterface(): void
    {
        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->expects(self::once())
            ->method('getTarget')
            ->willReturn('_blank');

        $typolinkParameter = new TypolinkParameter('', '_blank', 'title');
        $typolinkParameter->url = $uriMock;

        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEquals('_blank', $result['target']);
    }

    public function testTypeFromUriInterface(): void
    {
        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->expects(self::once())
            ->method('getType')
            ->willReturn(1);

        $typolinkParameter = new TypolinkParameter('', 'target', 'title');
        $typolinkParameter->url = $uriMock;

        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEquals(1, $result['type']);
    }

    public function testTitleFromUriInterface(): void
    {
        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->expects(self::once())
            ->method('getLinkText')
            ->willReturn('Link Title');

        $typolinkParameter = new TypolinkParameter('', 'target', 'title');
        $typolinkParameter->url = $uriMock;

        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEquals('Link Title', $result['title']);
    }

    public function testConfigFromUriInterface(): void
    {
        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->expects(self::once())
            ->method('getLinkConfiguration')
            ->willReturn(['config' => ['some' => 'config']]);

        $typolinkParameter = new TypolinkParameter('', 'target', 'title');
        $typolinkParameter->url = $uriMock;

        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('config', $result);
        self::assertArrayHasKey('some', $result['config']);
    }

    public function testAttrFromUriInterface(): void
    {
        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->expects(self::once())
            ->method('getAttributes')
            ->willReturn(['rel' => 'nofollow']);

        $typolinkParameter = new TypolinkParameter('', 'target', 'title');
        $typolinkParameter->url = $uriMock;

        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('attr', $result);
        self::assertArrayHasKey('rel', $result['attr']);
    }

    public function testHandlingUnableToLinkException(): void
    {
        $linkFactory = $this->createMock(LinkFactory::class);
        $linkFactory->expects(self::once())
            ->method('createUri')
            ->willThrowException(new UnableToLinkException('Link cannot be created'));

        GeneralUtility::addInstance(LinkFactory::class, $linkFactory);

        $typolinkParameter = new TypolinkParameter('invalid://url', 'target', 'title');
        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('url', $result);
        self::assertArrayHasKey('__errorMessage', $result);
    }

    public function testEmptyConfigFromUriInterface(): void
    {
        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->expects(self::once())
            ->method('getLinkConfiguration')
            ->willReturn([]);

        $typolinkParameter = new TypolinkParameter('', 'target', 'title');
        $typolinkParameter->url = $uriMock;

        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('config', $result);
        self::assertIsArray($result['config']);
        self::assertEmpty($result['config']);
    }

    public function testEmptyAttrFromUriInterface(): void
    {
        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->expects(self::once())
            ->method('getAttributes')
            ->willReturn([]);

        $typolinkParameter = new TypolinkParameter('', 'target', 'title');
        $typolinkParameter->url = $uriMock;

        $subject = new TypolinkParameterToArray($typolinkParameter);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('attr', $result);
        self::assertIsArray($result['attr']);
        self::assertEmpty($result['attr']);
    }
}