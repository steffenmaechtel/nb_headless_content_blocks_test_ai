<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray;

use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LazyFileReferenceCollectionToArray
{
    public function __construct(protected LazyFileReferenceCollection $lazyFileReferenceCollection) {}

    public function toArray(): array
        {
            $data = [];
            foreach ($this->lazyFileReferenceCollection as $key => $value) {
                if (is_null($value)) {
                    continue; // Skip null values in the collection
                }

                $converterInstance = GeneralUtility::makeInstance(FileReferenceToArray::class, $value);
                try {
                    $data[$key] = $converterInstance->toArray();
                } catch (\RuntimeException) {
                    // ImageService may not be available - skip this entry
                    continue;
                }
            }

            return $data;
        }
}
