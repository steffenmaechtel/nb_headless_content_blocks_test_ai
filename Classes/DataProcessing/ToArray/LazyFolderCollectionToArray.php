<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray;

use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;

class LazyFolderCollectionToArray
{
    public function __construct(protected LazyFolderCollection $lazyFolderCollection) {}

    public function toArray(): array
    {
        $data = [];
        foreach ($this->lazyFolderCollection as $key => $value) {
            // Storage drivers other than "Local" (and the fallback storage) have no base path.
            $basePath = (string)($value->getStorage()->getConfiguration()['basePath'] ?? '');
            $path = '/' . $basePath . ltrim((string)$value->getIdentifier(), '/');
            $data[$key] = $path;
        }

        return $data;
    }
}
