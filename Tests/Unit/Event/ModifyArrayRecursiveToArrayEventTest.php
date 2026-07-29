<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\Event;

use Netzbewegung\NbHeadlessContentBlocks\Event\ModifyArrayRecursiveToArrayEvent;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentType;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinition;
use TYPO3\CMS\ContentBlocks\FieldType\TextFieldType;

final class ModifyArrayRecursiveToArrayEventTest extends TestCase
{
    public function testGetKeyReturnsKey(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('testKey', 'testValue', null);

        self::assertSame('testKey', $event->getKey());
    }

    public function testGetKeyReturnsIntegerKey(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent(123, 'testValue', null);

        self::assertSame(123, $event->getKey());
    }

    public function testGetValueReturnsValue(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('key', 'myValue', null);

        self::assertSame('myValue', $event->getValue());
    }

    public function testGetValueReturnsNull(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('key', null, null);

        self::assertNull($event->getValue());
    }

    public function testGetTcaFieldDefinitionReturnsNull(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('key', 'value', null);

        self::assertNull($event->getTcaFieldDefinition());
    }

    public function testIsHandledReturnsFalseByDefault(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('key', 'value', null);

        self::assertFalse($event->isHandled());
    }

    public function testSetProcessedValueMarksEventAsHandled(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('key', 'value', null);

        $event->setProcessedValue('processedValue');

        self::assertTrue($event->isHandled());
    }

    public function testGetProcessedValueReturnsSetValue(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('key', 'value', null);

        $event->setProcessedValue('processedValue');

        self::assertSame('processedValue', $event->getProcessedValue());
    }

    public function testGetProcessedValueReturnsNullByDefault(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('key', 'value', null);

        self::assertNull($event->getProcessedValue());
    }

    public function testSetProcessedValueCanSetNull(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('key', 'value', null);

        $event->setProcessedValue(null);

        self::assertTrue($event->isHandled());
        self::assertNull($event->getProcessedValue());
    }

    public function testGetTcaFieldDefinitionReturnsDefinition(): void
    {
        $fieldDefinition = new TcaFieldDefinition(
            ContentType::CONTENT_ELEMENT,
            'field',
            'field',
            '',
            '',
            '',
            false,
            new TextFieldType()
        );
        $event = new ModifyArrayRecursiveToArrayEvent('key', 'value', $fieldDefinition);

        self::assertSame($fieldDefinition, $event->getTcaFieldDefinition());
    }

    public function testLatestProcessedValueIsReturned(): void
    {
        $event = new ModifyArrayRecursiveToArrayEvent('key', 'value', null);

        $event->setProcessedValue('first');
        $event->setProcessedValue('second');

        self::assertTrue($event->isHandled());
        self::assertSame('second', $event->getProcessedValue());
    }
}
