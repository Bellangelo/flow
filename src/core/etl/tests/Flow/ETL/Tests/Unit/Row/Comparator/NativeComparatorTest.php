<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Row\Comparator;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\string_entry;
use Flow\ETL\Row;

final class NativeComparatorTest extends FlowTestCase
{
    public function test_row_comparison() : void
    {
        $row = Row::create(string_entry('test', 'test'));
        $nextRow = Row::create(string_entry('test', 'test'));

        $comparator = new Row\Comparator\NativeComparator();

        self::assertTrue($comparator->equals($row, $nextRow));
    }

    public function test_row_comparison_for_different_rows() : void
    {
        $row = Row::create(string_entry('test', 'test'));
        $nextRow = Row::create(new Row\Entry\IntegerEntry('test', 2));

        $comparator = new Row\Comparator\NativeComparator();

        self::assertFalse($comparator->equals($row, $nextRow));
    }
}
