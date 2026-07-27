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
use TYPO3\CMS\ContentBlocks\FieldType\CategoryFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\FieldTypeInterface;
use TYPO3\CMS\ContentBlocks\FieldType\JsonFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\PasswordFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\TextareaFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\TextFieldType;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Domain\FlexFormFieldValues;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
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

    public function testTextFieldPassesThrough(): void
    {
        $subject = $this->createSubjectWithField('text', $this->initFieldType(new TextFieldType()), 'value');

        self::assertSame(['text' => 'value'], $subject->toArray());
    }

    public function testPasswordFieldTypeValueIsEmptied(): void
    {
        $subject = $this->createSubjectWithField('password', $this->initFieldType(new PasswordFieldType()), 'secret');

        self::assertSame(['password' => ''], $subject->toArray());
    }

    public function testTextareaFieldWithoutRichtextPassesThrough(): void
    {
        $subject = $this->createSubjectWithField(
            'text',
            $this->initFieldType((new TextareaFieldType())->createFromArray([])),
            'hello world'
        );

        self::assertSame(['text' => 'hello world'], $subject->toArray());
    }

    public function testJsonFieldTypeArrayIsPassedThroughRaw(): void
    {
        $subject = $this->createSubjectWithField(
            'json',
            $this->initFieldType(new JsonFieldType()),
            ['keep' => true, 'nested' => ['flag' => false]]
        );

        self::assertSame(
            ['json' => ['keep' => true, 'nested' => ['flag' => false]]],
            $subject->toArray()
        );
    }

    public function testFlexFormFieldValuesIsConverted(): void
    {
        $subject = $this->createSubject(['flex' => new FlexFormFieldValues(['sheet' => ['a' => 'b']])]);

        self::assertSame(['flex' => ['sheet' => ['a' => 'b']]], $subject->toArray());
    }

    public function testUnknownObjectTypeIsDropped(): void
    {
        $subject = $this->createSubject(['obj' => new \stdClass()]);

        self::assertSame([], $subject->toArray());
    }

    public function testGetTableNameByKeyReturnsForeignTable(): void
    {
        $method = $this->getTableNameByKeyMethod(
            $this->createTableDefinitionWithField('rel', $this->fakeFieldType(['config' => ['foreign_table' => 'tx_foreign']]))
        );

        self::assertSame('tx_foreign', $method('rel'));
    }

    public function testGetTableNameByKeyReturnsNullForMultipleAllowedTables(): void
    {
        $method = $this->getTableNameByKeyMethod(
            $this->createTableDefinitionWithField('rel', $this->fakeFieldType(['config' => ['allowed' => 'a,b']]))
        );

        self::assertNull($method('rel'));
    }

    public function testGetTableNameByKeyReturnsSysCategoryForCategoryField(): void
    {
        $method = $this->getTableNameByKeyMethod(
            $this->createTableDefinitionWithField('cat', $this->initFieldType(new CategoryFieldType()))
        );

        self::assertSame('sys_category', $method('cat'));
    }

    /**
     * @param callable[] $listeners
     */
    private function createSubject(array $array, array $listeners = []): ArrayRecursiveToArray
    {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        return new ArrayRecursiveToArray(
            $array,
            null,
            $tableDefinitionCollection,
            $this->createEventDispatcher($listeners)
        );
    }

    private function createSubjectWithField(string $fieldName, FieldTypeInterface $fieldType, mixed $value): ArrayRecursiveToArray
    {
        $tableDefinition = $this->createTableDefinitionWithField($fieldName, $fieldType);

        return new ArrayRecursiveToArray(
            [$fieldName => $value],
            $tableDefinition,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            $this->createEventDispatcher([])
        );
    }

    private function createTableDefinitionWithField(string $fieldName, FieldTypeInterface $fieldType): TableDefinition
    {
        $tcaFieldCollection = new TcaFieldDefinitionCollection();
        $tcaFieldCollection->addField(new TcaFieldDefinition(
            ContentType::RECORD_TYPE,
            'tt_content',
            $fieldName,
            $fieldName,
            'label',
            '',
            '',
            false,
            $fieldType
        ));

        return new TableDefinition(
            'tt_content',
            TableDefinitionCapability::createFromArray([]),
            null,
            ContentType::RECORD_TYPE,
            new ContentTypeDefinitionCollection(),
            new SqlColumnDefinitionCollection(),
            $tcaFieldCollection,
            new PaletteDefinitionCollection(),
            []
        );
    }

    /**
     * @return \Closure(string):mixed
     */
    private function getTableNameByKeyMethod(TableDefinition $tableDefinition): \Closure
    {
        $subject = new ArrayRecursiveToArray(
            [],
            $tableDefinition,
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            $this->createEventDispatcher([])
        );

        $reflection = new \ReflectionMethod($subject, 'getTableNameByKey');
        $reflection->setAccessible(true);

        return static function (string $key) use ($subject, $reflection): mixed {
            return $reflection->invoke($subject, $key);
        };
    }

    private function initFieldType(FieldTypeInterface $fieldType): FieldTypeInterface
    {
        $fieldType->setName('test');
        $fieldType->setTcaType('test');

        return $fieldType;
    }

    private function fakeFieldType(array $tca): FieldTypeInterface
    {
        return new class ($tca) implements FieldTypeInterface {
            public function __construct(private readonly array $tca) {}

            public function getName(): string
            {
                return 'fake';
            }

            public function getTcaType(): string
            {
                return 'input';
            }

            public function setName(string $name): void {}

            public function setTcaType(string $tcaType): void {}

            public function createFromArray(array $settings): FieldTypeInterface
            {
                return $this;
            }

            public function getTca(): array
            {
                return $this->tca;
            }

            public function getSql(string $column): string
            {
                return '';
            }
        };
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
