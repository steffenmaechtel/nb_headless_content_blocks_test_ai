<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\ArrayRecursiveToArray;
use Netzbewegung\NbHeadlessContentBlocks\Event\ModifyArrayRecursiveToArrayEvent;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\ContentBlocks\Definition\Capability\TableDefinitionCapability;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentType;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\PaletteDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\SqlColumnDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinitionCollection;
use TYPO3\CMS\ContentBlocks\FieldType\FieldTypeInterface;
use TYPO3\CMS\ContentBlocks\FieldType\JsonFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\PasswordFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\TextareaFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\TextFieldType;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ArrayRecursiveToArrayTest extends UnitTestCase
{
    public function testNullValueIsPassedThrough(): void
    {
        $subject = $this->createSubject(['key' => null]);

        self::assertSame(['key' => null], $subject->toArray());
    }

    public function testIntegerValueIsPassedThrough(): void
    {
        $subject = $this->createSubject(['key' => 42]);

        self::assertSame(['key' => 42], $subject->toArray());
    }

    public function testStringValueIsPassedThrough(): void
    {
        $subject = $this->createSubject(['key' => 'value']);

        self::assertSame(['key' => 'value'], $subject->toArray());
    }

    public function testBooleanValueIsDropped(): void
    {
        $subject = $this->createSubject(['key' => true]);

        self::assertSame([], $subject->toArray());
    }

    public function testFloatValueIsDropped(): void
    {
        $subject = $this->createSubject(['key' => 13.37]);

        self::assertSame([], $subject->toArray());
    }

    public function testDateTimeImmutableIsFormattedAsW3C(): void
    {
        $dateTime = new \DateTimeImmutable('2026-07-22 10:15:30', new \DateTimeZone('UTC'));

        $subject = $this->createSubject(['key' => $dateTime]);

        self::assertSame(['key' => $dateTime->format(\DateTimeImmutable::W3C)], $subject->toArray());
    }

    public function testNestedArrayIsProcessedRecursively(): void
    {
        $subject = $this->createSubject([
            'level1' => [
                'level2' => [
                    'key' => 'value',
                ],
            ],
        ]);

        self::assertSame([
            'level1' => [
                'level2' => [
                    'key' => 'value',
                ],
            ],
        ], $subject->toArray());
    }

    public function testResultIsSortedByKey(): void
    {
        $subject = $this->createSubject([
            'zulu' => 1,
            'alpha' => 2,
            'mike' => 3,
        ]);

        self::assertSame([
            'alpha' => 2,
            'mike' => 3,
            'zulu' => 1,
        ], $subject->toArray());
    }

    public function testHandledEventProcessedValueIsUsed(): void
    {
        $listener = static function (ModifyArrayRecursiveToArrayEvent $event): void {
            $event->setProcessedValue('processed');
        };

        $subject = $this->createSubject(['key' => 'original'], [$listener]);

        self::assertSame(['key' => 'processed'], $subject->toArray());
    }

    public function testEventReceivesKeyAndValue(): void
    {
        $receivedEvents = [];
        $listener = static function (ModifyArrayRecursiveToArrayEvent $event) use (&$receivedEvents): void {
            $receivedEvents[] = [$event->getKey(), $event->getValue()];
        };

        $subject = $this->createSubject(['myKey' => 'myValue'], [$listener]);
        $subject->toArray();

        self::assertSame([['myKey', 'myValue']], $receivedEvents);
    }

    public function testJsonFieldIsPreservedWithoutRecursiveProcessing(): void
    {
        $subject = $this->createSubject(
            [
                'settings' => [
                    'z' => 1,
                    'nested' => ['b' => 2, 'a' => 1],
                ],
            ],
            tableDefinition: $this->createTableDefinition(
                $this->createFieldDefinition('settings', 'publicSettings', new JsonFieldType())
            )
        );

        self::assertSame([
            'publicSettings' => [
                'z' => 1,
                'nested' => ['b' => 2, 'a' => 1],
            ],
        ], $subject->toArray());
    }

    public function testTcaFieldIdentifierIsUsedAsOutputKey(): void
    {
        $subject = $this->createSubject(
            ['internalName' => 'value'],
            tableDefinition: $this->createTableDefinition(
                $this->createFieldDefinition('internalName', 'publicName', new TextFieldType())
            )
        );

        self::assertSame(['publicName' => 'value'], $subject->toArray());
    }

    public function testPasswordFieldIsEmptiedAndTextFieldIsPassedThrough(): void
    {
        $subject = $this->createSubject(
            [
                'password' => 'secret',
                'text' => 'visible',
            ],
            tableDefinition: $this->createTableDefinition(
                $this->createFieldDefinition('password', 'password', new PasswordFieldType()),
                $this->createFieldDefinition('text', 'text', new TextFieldType())
            )
        );

        self::assertSame([
            'password' => '',
            'text' => 'visible',
        ], $subject->toArray());
    }

    public function testNonRichTextTextareaIsPassedThrough(): void
    {
        $subject = $this->createSubject(
            ['body' => '<strong>visible</strong>'],
            tableDefinition: $this->createTableDefinition(
                $this->createFieldDefinition(
                    'body',
                    'body',
                    $this->createTextareaFieldType()
                )
            )
        );

        self::assertSame(['body' => '<strong>visible</strong>'], $subject->toArray());
    }

    public function testRichTextTextareaIsParsed(): void
    {
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $contentObjectRenderer->expects($this->once())
            ->method('parseFunc')
            ->with('<strong>source</strong>', null, '< lib.parseFunc_RTE')
            ->willReturn('<p>parsed</p>');
        GeneralUtility::addInstance(ContentObjectRenderer::class, $contentObjectRenderer);

        $subject = $this->createSubject(
            ['body' => '<strong>source</strong>'],
            tableDefinition: $this->createTableDefinition(
                $this->createFieldDefinition(
                    'body',
                    'body',
                    $this->createTextareaFieldType(true)
                )
            )
        );

        self::assertSame(['body' => '<p>parsed</p>'], $subject->toArray());
    }

    /**
     * @param callable[] $listeners
     */
    private function createSubject(
        array $array,
        array $listeners = [],
        ?TableDefinition $tableDefinition = null
    ): ArrayRecursiveToArray {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        return new ArrayRecursiveToArray(
            $array,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher($listeners)
        );
    }

    private function createTableDefinition(TcaFieldDefinition ...$fields): TableDefinition
    {
        $fieldDefinitionCollection = new TcaFieldDefinitionCollection();
        foreach ($fields as $field) {
            $fieldDefinitionCollection->addField($field);
        }

        return new TableDefinition(
            'test_table',
            TableDefinitionCapability::createFromArray([]),
            'CType',
            ContentType::CONTENT_ELEMENT,
            new ContentTypeDefinitionCollection(),
            new SqlColumnDefinitionCollection(),
            $fieldDefinitionCollection,
            new PaletteDefinitionCollection(),
            []
        );
    }

    private function createFieldDefinition(
        string $uniqueIdentifier,
        string $identifier,
        FieldTypeInterface $fieldType
    ): TcaFieldDefinition {
        return new TcaFieldDefinition(
            ContentType::CONTENT_ELEMENT,
            $identifier,
            $uniqueIdentifier,
            '',
            '',
            '',
            false,
            $fieldType
        );
    }

    private function createTextareaFieldType(bool $enableRichtext = false): TextareaFieldType
    {
        $fieldType = (new TextareaFieldType())->createFromArray(['enableRichtext' => $enableRichtext]);
        $fieldType->setName('Textarea');
        $fieldType->setTcaType('text');
        $fieldType->setSearchable(true);

        return $fieldType;
    }

    /**
     * @param callable[] $listeners
     */
    private function createEventDispatcher(array $listeners): EventDispatcher
    {
        $listenerProvider = new class ($listeners) implements ListenerProviderInterface {
            public function __construct(private readonly array $listeners) {}

            public function getListenersForEvent(object $event): iterable
            {
                return $this->listeners;
            }
        };

        return new EventDispatcher($listenerProvider);
    }
}
