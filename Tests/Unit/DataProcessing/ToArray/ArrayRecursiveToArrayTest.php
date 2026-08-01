<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\ArrayRecursiveToArray;
use Netzbewegung\NbHeadlessContentBlocks\Event\ModifyArrayRecursiveToArrayEvent;
use Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\Support\ContentBlockFactoryTrait;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinition;
use TYPO3\CMS\ContentBlocks\FieldType\JsonFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\PasswordFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\TextFieldType;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Domain\FlexFormFieldValues;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ArrayRecursiveToArrayTest extends UnitTestCase
{
    use ContentBlockFactoryTrait;

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

    public function testUnknownTypeIsDropped(): void
    {
        $subject = $this->createSubject(['key' => new \stdClass()]);

        self::assertSame([], $subject->toArray());
    }

    public function testFlexFormFieldValuesAreConvertedViaToArray(): void
    {
        $flex = new FlexFormFieldValues(['sheet' => ['k' => 'v']]);

        $subject = $this->createSubject(['flex' => $flex]);

        self::assertSame(['flex' => ['sheet' => ['k' => 'v']]], $subject->toArray());
    }

    public function testPasswordFieldTypeValueIsBlanked(): void
    {
        $tableDefinition = $this->buildTableDefinition(['my_password' => new PasswordFieldType()]);

        $subject = $this->createSubjectWithTableDefinition(
            ['my_password' => 'super-secret'],
            $tableDefinition
        );

        self::assertSame(['my_password' => ''], $subject->toArray());
    }

    public function testTextFieldTypeValueIsPassedThrough(): void
    {
        $tableDefinition = $this->buildTableDefinition(['my_text' => new TextFieldType()]);

        $subject = $this->createSubjectWithTableDefinition(
            ['my_text' => 'some text'],
            $tableDefinition
        );

        self::assertSame(['my_text' => 'some text'], $subject->toArray());
    }

    public function testJsonFieldTypeArrayIsPassedThroughWithoutRecursion(): void
    {
        $tableDefinition = $this->buildTableDefinition(['my_json' => new JsonFieldType()]);

        $payload = ['b' => 1, 'a' => 2, 'flag' => true];

        $subject = $this->createSubjectWithTableDefinition(
            ['my_json' => $payload],
            $tableDefinition
        );

        // Without JsonFieldType the inner array would be recursed (sorted, bool dropped).
        self::assertSame(['my_json' => $payload], $subject->toArray());
    }

    public function testDecoratedKeyIsUsedWhenTcaFieldExists(): void
    {
        $tableDefinition = $this->buildTableDefinitionFromDefinitions([
            $this->buildTcaFieldDefinition('decorated_key', 'raw_key', new TextFieldType()),
        ]);

        $subject = $this->createSubjectWithTableDefinition(
            ['raw_key' => 'value'],
            $tableDefinition
        );

        self::assertSame(['decorated_key' => 'value'], $subject->toArray());
    }

    public function testEventReceivesTcaFieldDefinitionWhenFieldExists(): void
    {
        $tableDefinition = $this->buildTableDefinition(['my_text' => new TextFieldType()]);

        $received = [];
        $listener = static function (ModifyArrayRecursiveToArrayEvent $event) use (&$received): void {
            $received[] = $event->getTcaFieldDefinition();
        };

        $subject = $this->createSubjectWithTableDefinition(
            ['my_text' => 'value'],
            $tableDefinition,
            [$listener]
        );
        $subject->toArray();

        self::assertCount(1, $received);
        self::assertInstanceOf(TcaFieldDefinition::class, $received[0]);
        self::assertSame('my_text', $received[0]->uniqueIdentifier);
    }

    /**
     * @param callable[] $listeners
     */
    private function createSubject(array $array, array $listeners = []): ArrayRecursiveToArray
    {
        return $this->createSubjectWithTableDefinition($array, null, $listeners);
    }

    /**
     * @param callable[] $listeners
     */
    private function createSubjectWithTableDefinition(
        array $array,
        ?TableDefinition $tableDefinition,
        array $listeners = []
    ): ArrayRecursiveToArray {
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());

        return new ArrayRecursiveToArray(
            $array,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher($listeners)
        );
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
