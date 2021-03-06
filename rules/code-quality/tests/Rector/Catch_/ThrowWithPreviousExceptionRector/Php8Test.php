<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Catch_\ThrowWithPreviousExceptionRector;

use Iterator;
use Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Php8Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     * @requires PHP 8
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePhp8');
    }

    protected function getRectorClass(): string
    {
        return ThrowWithPreviousExceptionRector::class;
    }
}
