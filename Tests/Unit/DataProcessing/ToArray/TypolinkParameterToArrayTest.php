<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\LinkResult;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

final class TypolinkParameterToArrayTest extends TestCase
{
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

    public function testLinkIsConvertedToArray(): void
    {
        $typolinkParameter = new TypolinkParameter(url: 'https://example.com', target: '_blank');
        $codecService = new TypoLinkCodecService($this->createEventDispatcher());

        $linkResult = (new LinkResult('url', 'https://example.com'))
            ->withTarget('_blank')
            ->withLinkText('Example')
            ->withLinkConfiguration(['parameter' => 'value'])
            ->withAttributes(['class' => 'button']);
        $linkFactory = $this->createMock(LinkFactory::class);
        $linkFactory->expects($this->once())
            ->method('createUri')
            ->with(self::isType('string'))
            ->willReturn($linkResult);

        $subject = $this->createSubject($typolinkParameter, $codecService, $linkFactory);

        self::assertSame([
            'url' => 'https://example.com',
            'target' => '_blank',
            'type' => 'url',
            'title' => 'Example',
            'config' => ['parameter' => 'value'],
            'attr' => [
                'href' => 'https://example.com',
                'target' => '_blank',
                'class' => 'button',
            ],
        ], $subject->toArray());
    }

    public function testUnableToLinkExceptionReturnsErrorArray(): void
    {
        $exception = new UnableToLinkException('Unable to create link');
        $codecService = new TypoLinkCodecService($this->createEventDispatcher());
        $linkFactory = $this->createMock(LinkFactory::class);
        $linkFactory->method('createUri')->willThrowException($exception);

        $subject = $this->createSubject(
            new TypolinkParameter(url: 'https://example.com'),
            $codecService,
            $linkFactory
        );

        self::assertSame([
            'url' => '',
            'target' => '',
            'type' => '',
            'title' => '',
            'config' => [],
            'attr' => [],
            '__errorMessage' => 'Unable to create link',
        ], $subject->toArray());
    }

    private function createSubject(
        TypolinkParameter $typolinkParameter,
        TypoLinkCodecService $codecService,
        LinkFactory $linkFactory
    ): TypolinkParameterToArray {
        return new class ($typolinkParameter, $codecService, $linkFactory) extends TypolinkParameterToArray {
            public function __construct(
                TypolinkParameter $typolinkParameter,
                private readonly TypoLinkCodecService $codecService,
                private readonly LinkFactory $linkFactory
            ) {
                parent::__construct($typolinkParameter);
            }

            protected function getTypoLinkCodecService(): TypoLinkCodecService
            {
                return $this->codecService;
            }

            protected function getLinkFactory(): LinkFactory
            {
                return $this->linkFactory;
            }
        };
    }

    private function createEventDispatcher(): EventDispatcher
    {
        return new EventDispatcher(new class implements ListenerProviderInterface {
            public function getListenersForEvent(object $event): iterable
            {
                return [];
            }
        });
    }
}
