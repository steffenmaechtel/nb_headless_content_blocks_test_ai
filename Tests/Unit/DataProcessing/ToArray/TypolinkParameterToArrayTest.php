<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\TestHelper\ContentBlocksDefinitionTrait;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TypolinkParameterToArrayTest extends UnitTestCase
{
    use ContentBlocksDefinitionTrait;

    public function testEmptyUrlReturnsNull(): void
    {
        $typolinkParameter = new TypolinkParameter(url: '');

        $subject = new TypolinkParameterToArray($typolinkParameter);

        self::assertNull($subject->toArray());
    }

    public function testZeroUrlReturnsNull(): void
    {
        $typolinkParameter = new TypolinkParameter(url: '0');

        $subject = new TypolinkParameterToArray($typolinkParameter);

        self::assertNull($subject->toArray());
    }

    public function testSuccessfulLinkIsMappedToAllResultProperties(): void
    {
        $linkResult = $this->createMock(LinkResultInterface::class);
        $linkResult->method('getUrl')->willReturn('https://example.org/target');
        $linkResult->method('getTarget')->willReturn('_blank');
        $linkResult->method('getType')->willReturn('url');
        $linkResult->method('getLinkText')->willReturn('My title');
        $linkResult->method('getLinkConfiguration')->willReturn(['parameter' => 'https://example.org/target']);
        $linkResult->method('getAttributes')->willReturn(['rel' => 'noreferrer']);

        $subject = $this->createSubject(
            new TypolinkParameter(url: 'https://example.org/target'),
            $this->createLinkFactory($linkResult)
        );

        self::assertSame([
            'url' => 'https://example.org/target',
            'target' => '_blank',
            'type' => 'url',
            'title' => 'My title',
            'config' => ['parameter' => 'https://example.org/target'],
            'attr' => ['rel' => 'noreferrer'],
        ], $subject->toArray());
    }

    public function testNullLinkTextIsMappedToNullTitle(): void
    {
        $linkResult = $this->createMock(LinkResultInterface::class);
        $linkResult->method('getUrl')->willReturn('https://example.org/');
        $linkResult->method('getTarget')->willReturn('');
        $linkResult->method('getType')->willReturn('url');
        $linkResult->method('getLinkText')->willReturn(null);
        $linkResult->method('getLinkConfiguration')->willReturn([]);
        $linkResult->method('getAttributes')->willReturn([]);

        $subject = $this->createSubject(
            new TypolinkParameter(url: 'https://example.org/'),
            $this->createLinkFactory($linkResult)
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('title', (array)$result);
        self::assertNull($result['title']);
    }

    public function testTypolinkParameterIsEncodedBeforeItIsPassedToLinkFactory(): void
    {
        $linkResult = $this->createMock(LinkResultInterface::class);
        $linkResult->method('getUrl')->willReturn('https://example.org/');
        $linkResult->method('getTarget')->willReturn('_blank');
        $linkResult->method('getType')->willReturn('url');
        $linkResult->method('getLinkText')->willReturn('');
        $linkResult->method('getLinkConfiguration')->willReturn([]);
        $linkResult->method('getAttributes')->willReturn([]);

        $linkFactory = $this->createMock(LinkFactory::class);
        $linkFactory->expects($this->once())
            ->method('createUri')
            ->with('https://example.org/ _blank my-class "My title" &foo=bar')
            ->willReturn($linkResult);

        $typolinkParameter = new TypolinkParameter(
            url: 'https://example.org/',
            target: '_blank',
            class: 'my-class',
            title: 'My title',
            additionalParams: '&foo=bar',
        );

        $subject = $this->createSubject($typolinkParameter, $linkFactory);

        self::assertSame('https://example.org/', $subject->toArray()['url'] ?? null);
    }

    public function testUnableToLinkExceptionReturnsEmptyValuesWithErrorMessage(): void
    {
        $linkFactory = $this->createMock(LinkFactory::class);
        $linkFactory->method('createUri')
            ->willThrowException(new UnableToLinkException('Page id 4711 could not be resolved', 1745000000));

        $subject = $this->createSubject(new TypolinkParameter(url: 't3://page?uid=4711'), $linkFactory);

        self::assertSame([
            'url' => '',
            'target' => '',
            'type' => '',
            'title' => '',
            'config' => [],
            'attr' => [],
            '__errorMessage' => 'Page id 4711 could not be resolved',
        ], $subject->toArray());
    }

    private function createLinkFactory(LinkResultInterface $linkResult): LinkFactory
    {
        $linkFactory = $this->createMock(LinkFactory::class);
        $linkFactory->method('createUri')->willReturn($linkResult);

        return $linkFactory;
    }

    /**
     * TypolinkParameterToArray resolves LinkFactory and TypoLinkCodecService
     * through protected factory methods. Overriding them keeps the test free of
     * a service container while still exercising the real codec service.
     */
    private function createSubject(
        TypolinkParameter $typolinkParameter,
        LinkFactory $linkFactory
    ): TypolinkParameterToArray {
        $typoLinkCodecService = new TypoLinkCodecService($this->createEventDispatcher());

        return new class ($typolinkParameter, $linkFactory, $typoLinkCodecService) extends TypolinkParameterToArray {
            public function __construct(
                TypolinkParameter $typolinkParameter,
                private readonly LinkFactory $injectedLinkFactory,
                private readonly TypoLinkCodecService $injectedTypoLinkCodecService
            ) {
                parent::__construct($typolinkParameter);
            }

            protected function getLinkFactory(): LinkFactory
            {
                return $this->injectedLinkFactory;
            }

            protected function getTypoLinkCodecService(): TypoLinkCodecService
            {
                return $this->injectedTypoLinkCodecService;
            }
        };
    }
}
