<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\TestHelper;

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
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\Record\ComputedProperties;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;

/**
 * Builds real (non-mocked) Content Blocks definition objects and records for unit tests.
 *
 * TableDefinition, TcaFieldDefinition and the field types are `final` (mostly
 * `final readonly`) and can therefore not be mocked. They are plain value
 * objects though, so they can be constructed in memory without a database
 * or a full TYPO3 bootstrap.
 */
trait ContentBlocksDefinitionTrait
{
    /**
     * @param TcaFieldDefinition[] $fields
     */
    protected function createTableDefinition(string $table, array $fields = []): TableDefinition
    {
        $tcaFieldDefinitionCollection = TcaFieldDefinitionCollection::createFromArray([], $table);
        foreach ($fields as $field) {
            $tcaFieldDefinitionCollection->addField($field);
        }

        return new TableDefinition(
            table: $table,
            capability: TableDefinitionCapability::createFromArray([]),
            typeField: null,
            contentType: ContentType::getByTable($table),
            contentTypeDefinitionCollection: ContentTypeDefinitionCollection::createFromArray([], $table),
            sqlColumnDefinitionCollection: SqlColumnDefinitionCollection::createFromArray([], $table),
            tcaFieldDefinitionCollection: $tcaFieldDefinitionCollection,
            paletteDefinitionCollection: PaletteDefinitionCollection::createFromArray([], $table),
            parentReferences: [],
        );
    }

    protected function createTcaFieldDefinition(
        string $uniqueIdentifier,
        string $identifier,
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
        );
    }

    /**
     * Field types are `final` and cannot be mocked. They are constructed the
     * same way the Content Blocks FieldTypeRegistry does it: instantiate,
     * apply the values of the #[FieldType] attribute, then hydrate settings.
     *
     * @param class-string<FieldTypeInterface> $fieldTypeClass
     */
    protected function createFieldType(string $fieldTypeClass, array $settings = []): FieldTypeInterface
    {
        $fieldType = new $fieldTypeClass();

        $attributes = (new \ReflectionClass($fieldTypeClass))
            ->getAttributes(\TYPO3\CMS\ContentBlocks\FieldType\FieldType::class);
        $attribute = $attributes[0]->newInstance();

        $fieldType->setName($attribute->name);
        $fieldType->setTcaType($attribute->tcaType);
        $fieldType->setSearchable($attribute->searchable);

        return $fieldType->createFromArray($settings);
    }

    protected function createTableDefinitionCollection(TableDefinition ...$tableDefinitions): TableDefinitionCollection
    {
        $collection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        foreach ($tableDefinitions as $tableDefinition) {
            $collection->addTable($tableDefinition);
        }

        return $collection;
    }

    protected function createRecord(
        array $properties,
        string $type = 'tt_content',
        int $uid = 1,
        int $pid = 1
    ): Record {
        $rawRecord = new RawRecord($uid, $pid, $properties, new ComputedProperties(), $type);

        return new Record($rawRecord, $properties);
    }

    /**
     * @param callable[] $listeners
     */
    protected function createEventDispatcher(array $listeners = []): EventDispatcher
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
