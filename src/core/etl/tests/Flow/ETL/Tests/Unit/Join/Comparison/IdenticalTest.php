<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Join\Comparison;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\int_entry;
use Flow\ETL\Join\Comparison\Identical;
use Flow\ETL\Row;

final class IdenticalTest extends FlowTestCase
{
    public function test_failure() : void
    {
        self::assertFalse(
            (new Identical('id', 'id'))->compare(
                Row::create(int_entry('id', 1)),
                Row::create(int_entry('id', 2)),
            )
        );
    }

    public function test_success() : void
    {
        self::assertTrue(
            (new Identical('id', 'id'))->compare(
                Row::create(int_entry('id', 1)),
                Row::create(int_entry('id', 1)),
            )
        );
    }
}
