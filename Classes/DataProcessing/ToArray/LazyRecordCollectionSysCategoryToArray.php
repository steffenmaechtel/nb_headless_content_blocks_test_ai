<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray;

use TYPO3\CMS\Core\Collection\LazyRecordCollection;

class LazyRecordCollectionSysCategoryToArray
{
    public function __construct(protected LazyRecordCollection $lazyRecordCollection) {}

    /**
     * Convert the sys_category record collection to an array.
     */
    public function toArray(): array
        {
            $data = [];

            foreach ($this->lazyRecordCollection as $key => $value) {
                // Skip null values in the collection (test case: withNullValuesIsHandled)
                if (is_null($value)) {
                    continue;
                }

                try {
                    $array = $value->toArray();

                    if (!isset($array['uid'], $array['pid'], $array['title'])) {
                        // Skip entries without required fields
                        continue;
                    }

                    $data[$key] = [</parameter>
</function>
</tool_call>
<tool_call>
<function=edit>
<parameter=newString>
                try {
                    $array = $value->toArray();

                    if (!isset($array['uid'], $array['pid'], $array['title'])) {
                        // Skip entries without required fields
                        continue;
                    }

                    $data[$key] = [
                        'uid' => $array['uid'],
                        'pid' => $array['pid'],
                        'title' => $array['title'],
                    ];
                } catch (\Throwable) {
                    // Handle any exceptions during processing - skip this entry
                    continue;
                }
            }

            return $data;
        }
}
