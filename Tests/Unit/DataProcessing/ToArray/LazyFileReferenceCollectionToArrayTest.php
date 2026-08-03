<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFileReferenceCollectionToArrayTest extends UnitTestCase
{
    public function testLazyFileReferenceCollectionIsConvertedToArray(): void
        {
            $fileReference1 = $this->createMock(FileReference::class);