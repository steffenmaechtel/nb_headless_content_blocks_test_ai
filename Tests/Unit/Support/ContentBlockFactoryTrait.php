<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\Support;

use TYPO3\CMS\ContentBlocks\Definition\Capability\TableDefinitionCapability;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentType;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\PaletteDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\SqlColumnDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TcaFieldDefinitionCollection;
use TYPO3\CMS\ContentBlocks\FieldType\FieldTypeInterface;

/**
 * Helper to assemble real Content Blocks {@link TableDefinition} instances
 * without booting the DI container or a Content Block loader.
 */
trait ContentBlockFactoryTrait
{
    /**
     * @param array<string, FieldTypeInterface> $fields mapping of unique-identifier => field type instance.
     *        The TCA field {@link TcaFieldDefinition::$identifier} is set to the same value.
     */
    private function buildTableDefinition(array $fields = [], string $table = 'tt_content'): TableDefinition
    {
        $collection = new TcaFieldDefinitionCollection();
        foreach ($fields as $uniqueIdentifier => $fieldType) {
            $collection->addField($this->buildTcaFieldDefinition($uniqueIdentifier, $uniqueIdentifier, $fieldType));
        }

        return $this->buildTableDefinitionFromCollection($collection, $table);
    }

    /**
     * @param TcaFieldDefinition[] $tcaFieldDefinitions
     */
    private function buildTableDefinitionFromDefinitions(
        array $tcaFieldDefinitions,
        string $table = 'tt_content'
    ): TableDefinition {
        $collection = new TcaFieldDefinitionCollection();
        foreach ($tcaFieldDefinitions as $tcaFieldDefinition) {
            $collection->addField($tcaFieldDefinition);
        }

        return $this->buildTableDefinitionFromCollection($collection, $table);
    }

    private function buildTableDefinitionFromCollection(
        TcaFieldDefinitionCollection $collection,
        string $table = 'tt_content'
    ): TableDefinition {
        return new TableDefinition(
            table: $table,
            capability: TableDefinitionCapability::createFromArray([]),
            typeField: null,
            contentType: ContentType::CONTENT_ELEMENT,
            contentTypeDefinitionCollection: ContentTypeDefinitionCollection::createFromArray([], $table),
            sqlColumnDefinitionCollection: SqlColumnDefinitionCollection::createFromArray([], $table),
            tcaFieldDefinitionCollection: $collection,
            paletteDefinitionCollection: PaletteDefinitionCollection::createFromArray([], $table),
            parentReferences: [],
        );
    }

    private function buildTcaFieldDefinition(
        string $identifier,
        string $uniqueIdentifier,
        FieldTypeInterface $fieldType
    ): TcaFieldDefinition {
        return new TcaFieldDefinition(
            parentContentType: ContentType::CONTENT_ELEMENT,
            identifier: $identifier,
            uniqueIdentifier: $uniqueIdentifier,
            labelPath: '',
            descriptionPath: '',
            placeholderPath: '',
            useExistingField: false,
            fieldType: $fieldType,
            typeOverrides: null,
        );
    }
}
