<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TypolinkParameterToArrayTest extends UnitTestCase
{
    private MockObject&TypolinkParameter $typolinkParameter;
    private MockObject&LinkFactory $linkFactory;
    private MockObject&TypoLinkCodecService $typoLinkCodecService;

    protected function setUp(): void
    {
        $this->typolinkParameter = $this->createMock(TypolinkParameter::class);
        $this->linkFactory = $this->createMock(LinkFactory::class);
        $this->typoLinkCodecService = $this->createMock(TypoLinkCodecService::class);
    }

    public function testToArrayWithEmptyUrlReturnsNull(): void
    {
        $this->typolinkParameter->url = '';
        
        $subject = new TypolinkParameterToArray($this->typolinkParameter);
        
        $result = $subject->toArray();
        
        $this->assertNull($result);
    }

    public function testToArrayWithZeroUrlReturnsNull(): void
    {
        $this->typolinkParameter->url = '0';
        
        $subject = new TypolinkParameterToArray($this->typolinkParameter);
        
        $result = $subject->toArray();
        
        $this->assertNull($result);
    }

    public function testToArrayWithValidUrl(): void
    {
        $this->typolinkParameter->url = 'http://example.com';
        $this->typolinkParameter->toArray = fn() => ['url' => 'http://example.com'];
        
        $this->typoLinkCodecService->method('encode')->with(['url' => 'http://example.com'])->willReturn('http://example.com');
        $this->linkFactory->method('createUri')->with('http://example.com')->willReturn($this->createMock(\Psr\Http\Message\UriInterface::class));
        
        GeneralUtility::addInstance(LinkFactory::class, $this->linkFactory);
        GeneralUtility::addInstance(TypoLinkCodecService::class, $this->typoLinkCodecService);
        
        $subject = new TypolinkParameterToArray($this->typolinkParameter);
        
        $result = $subject->toArray();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('target', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('attr', $result);
    }

    public function testToArrayWithException(): void
    {
        $this->typolinkParameter->url = 'http://example.com';
        $this->typolinkParameter->toArray = fn() => ['url' => 'http://example.com'];
        
        $exception = new UnableToLinkException('Test error', 123456);
        $this->linkFactory->method('createUri')->with('http://example.com')->willThrowException($exception);
        
        GeneralUtility::addInstance(LinkFactory::class, $this->linkFactory);
        GeneralUtility::addInstance(TypoLinkCodecService::class, $this->typoLinkCodecService);
        
        $subject = new TypolinkParameterToArray($this->typolinkParameter);
        
        $result = $subject->toArray();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('target', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('attr', $result);
        $this->assertArrayHasKey('__errorMessage', $result);
        $this->assertEquals('', $result['url']);
        $this->assertEquals('', $result['target']);
        $this->assertEquals('', $result['type']);
        $this->assertEquals('', $result['title']);
        $this->assertEquals([], $result['config']);
        $this->assertEquals([], $result['attr']);
        $this->assertEquals('Test error', $result['__errorMessage']);
    }
}