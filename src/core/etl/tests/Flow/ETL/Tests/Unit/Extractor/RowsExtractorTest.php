<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use function Flow\ETL\DSL\{from_rows, int_entry, str_entry};
use Flow\ETL\{Row, Rows};

final class RowsExtractorTest extends ExtractorTestCase
{
    public function test_process_extractor() : void
    {
        $rows = new Rows(
            Row::create(int_entry('number', 1), str_entry('name', 'one')),
            Row::create(int_entry('number', 2), str_entry('name', 'two')),
            Row::create(int_entry('number', 3), str_entry('name', 'tree')),
            Row::create(int_entry('number', 4), str_entry('name', 'four')),
            Row::create(int_entry('number', 5), str_entry('name', 'five')),
        );

        $extractor = from_rows($rows);

        $this->assertExtractorYieldedArray(
            [
                ['number' => 1, 'name' => 'one'],
                ['number' => 2, 'name' => 'two'],
                ['number' => 3, 'name' => 'tree'],
                ['number' => 4, 'name' => 'four'],
                ['number' => 5, 'name' => 'five'],
            ],
            $extractor
        );
    }
}
