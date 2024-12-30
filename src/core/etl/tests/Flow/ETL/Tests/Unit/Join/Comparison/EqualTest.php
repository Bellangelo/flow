<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Join\Comparison;

use function Flow\ETL\DSL\datetime_entry;
use Flow\ETL\Join\Comparison\Equal;
use Flow\ETL\Row;
use Flow\ETL\Tests\FlowTestCase;

final class EqualTest extends FlowTestCase
{
    public function test_failure() : void
    {
        self::assertFalse(
            (new Equal('datetime', 'datetime'))->compare(
                Row::create(datetime_entry('datetime', new \DateTimeImmutable('2022-10-01 00:00:00'))),
                Row::create(datetime_entry('datetime', new \DateTimeImmutable('2022-10-01 01:00:00'))),
            )
        );
    }

    public function test_success() : void
    {
        self::assertTrue(
            (new Equal('datetime', 'datetime'))->compare(
                Row::create(datetime_entry('datetime', $datetime = new \DateTimeImmutable('2022-10-01 00:00:00'))),
                Row::create(datetime_entry('datetime', $datetime)),
            )
        );
    }
}
