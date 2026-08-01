<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
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

    public function testUnableToLinkExceptionReturnsErrorMessageArray(): void
    {
        $typolinkParameter = new TypolinkParameter(url: 'https://example.invalid');

        $subject = new class ($typolinkParameter) extends TypolinkParameterToArray {
            protected function getTypoLinkCodecService(): TypoLinkCodecService
            {
                $noopDispatcher = new class implements EventDispatcherInterface {
                    public function dispatch(object $event): object
                    {
                        return $event;
                    }
                };

                return new TypoLinkCodecService($noopDispatcher);
            }

            protected function getLinkFactory(): LinkFactory
            {
                throw new UnableToLinkException('Could not link to "https://example.invalid"', 12345);
            }
        };

        $result = $subject->toArray();

        self::assertSame([
            'url' => '',
            'target' => '',
            'type' => '',
            'title' => '',
            'config' => [],
            'attr' => [],
            '__errorMessage' => 'Could not link to "https://example.invalid"',
        ], $result);
    }
}
