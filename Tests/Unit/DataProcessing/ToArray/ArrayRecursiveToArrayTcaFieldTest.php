<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\ArrayRecursiveToArray;
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
use TYPO3\CMS\ContentBlocks\FieldType\JsonFieldType;
use TYPO3\CMS\ContentBlocks\FieldType\PasswordFieldType;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ArrayRecursiveToArrayTcaFieldTest extends UnitTestCase
{
    public function testPasswordFieldIsStripped(): void
    {
        $tableDefinition = $this->createTableDefinitionWithField(
            'secret',
            'secret',
            new PasswordFieldType()
        );

        $subject = $this->createSubject(
            ['secret' => 'myPassword123'],
            $tableDefinition
        );

        $result = $subject->toArray();

        self::assertSame(['secret' => ''], $result);
    }

    public function testJsonFieldArrayIsPassedThroughAsIs(): void
    {
        $tableDefinition = $this->createTableDefinitionWithField(
            'json_data',
            'json_data',
            new JsonFieldType()
        );

        $jsonValue = ['key' => 'value', 'nested' => ['a' => true, 'b' => 1.5]];

        $subject = $this->createSubject(
            ['json_data' => $jsonValue],
            $tableDefinition
        );

        $result = $subject->toArray();

        self::assertSame(['json_data' => $jsonValue], $result);
    }

    public function testTcaFieldIdentifierIsUsedAsKey(): void
    {
        $tableDefinition = $this->createTableDefinitionWithField(
            'my_decorated_key',
            'raw_key',
            new PasswordFieldType()
        );

        $subject = $this->createSubject(
            ['raw_key' => 'someValue'],
            $tableDefinition
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('my_decorated_key', $result);
        self::assertArrayNotHasKey('raw_key', $result);
        self::assertSame(['my_decorated_key' => ''], $result);
    }

    public function testNonTcaFieldUsesOriginalKey(): void
    {
        $tableDefinition = $this->createTableDefinitionWithField(
            'other_field',
            'other_field',
            new PasswordFieldType()
        );

        $subject = $this->createSubject(
            ['not_in_tca' => 'value'],
            $tableDefinition
        );

        $result = $subject->toArray();

        self::assertSame(['not_in_tca' => 'value'], $result);
    }

    private function createTableDefinitionWithField(
        string $identifier,
        string $uniqueIdentifier,
        $fieldType
    ): TableDefinition {
        $tcaFieldDefinition = new TcaFieldDefinition(
            parentContentType: ContentType::RECORD_TYPE,
            identifier: $identifier,
            uniqueIdentifier: $uniqueIdentifier,
            labelPath: '',
            descriptionPath: '',
            placeholderPath: '',
            useExistingField: false,
            fieldType: $fieldType
        );

        $tcaFieldDefinitionCollection = new TcaFieldDefinitionCollection();
        $tcaFieldDefinitionCollection->addField($tcaFieldDefinition);

        return new TableDefinition(
            table: 'tx_test',
            capability: TableDefinitionCapability::createFromArray([]),
            typeField: null,
            contentType: ContentType::RECORD_TYPE,
            contentTypeDefinitionCollection: new ContentTypeDefinitionCollection(),
            sqlColumnDefinitionCollection: new SqlColumnDefinitionCollection(),
            tcaFieldDefinitionCollection: $tcaFieldDefinitionCollection,
            paletteDefinitionCollection: new PaletteDefinitionCollection(),
            parentReferences: []
        );
    }

    private function createSubject(
        array $array,
        ?TableDefinition $tableDefinition
    ): ArrayRecursiveToArray {
        $tableDefinitionCollection = new TableDefinitionCollection(
            new AutomaticLanguageKeysRegistry()
        );

        return new ArrayRecursiveToArray(
            $array,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher()
        );
    }

    private function createEventDispatcher(): EventDispatcher
    {
        $listenerProvider = new class implements ListenerProviderInterface {
            public function getListenersForEvent(object $event): iterable
            {
                return [];
            }
        };

        return new EventDispatcher($listenerProvider);
    }
}
