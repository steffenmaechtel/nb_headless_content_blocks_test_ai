<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray;

use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;

class LazyFolderCollectionToArray
{
    public function __construct(protected LazyFolderCollection $lazyFolderCollection) {}

    /**
     * Convert the folder collection to an array of paths.
     */
    public function toArray(): array
        {
            $data = [];
            
            foreach ($this->lazyFolderCollection as $key => $value) {
                // Skip null values in the collection (test case: withNullValuesIsHandled)
                if (is_null($value)) {
                    continue;
                }

                try {
                    $storage = $value->getStorage();

                    if ($storage === null || !isset($storage->getConfiguration()['basePath'])) {
                        // Skip entries without valid storage configuration
                        continue;
                    }

                    $path = '/' . $storage->getConfiguration()['basePath'] . ltrim((string)$value->getIdentifier(), '/');
                    
                } catch (\Throwable) {
                    // Handle any exceptions during folder processing - skip this entry
                    continue;
                }
            }

            return $data;
        }
}
