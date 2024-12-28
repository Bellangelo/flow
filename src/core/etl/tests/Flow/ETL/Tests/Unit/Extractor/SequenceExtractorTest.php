<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use function Flow\ETL\DSL\{date_entry,
    float_entry,
    from_sequence_date_period,
    from_sequence_date_period_recurrences,
    from_sequence_number};
use Flow\ETL\{Row, Rows};

final class SequenceExtractorTest extends ExtractorTestCase
{
    public function test_extracting_from_date_period() : void
    {
        $extractor = from_sequence_date_period('day', new \DateTimeImmutable('2023-01-01'), new \DateInterval('P1D'), new \DateTimeImmutable('2023-01-11'), \DatePeriod::EXCLUDE_START_DATE);

        self::assertEquals(
            [
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-02')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-03')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-04')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-05')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-06')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-07')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-08')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-09')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-10')))),
            ],
            $this->toRowsArray($extractor)
        );
    }

    public function test_extracting_from_date_period_recurrences() : void
    {
        $extractor = from_sequence_date_period_recurrences('day', new \DateTimeImmutable('2023-01-01'), new \DateInterval('P1D'), 10, \DatePeriod::EXCLUDE_START_DATE);

        self::assertEquals(
            [
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-02')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-03')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-04')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-05')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-06')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-07')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-08')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-09')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-10')))),
                new Rows(Row::create(date_entry('day', new \DateTimeImmutable('2023-01-11')))),
            ],
            $this->toRowsArray($extractor)
        );
    }

    public function test_extracting_from_numbers_range() : void
    {
        $extractor = from_sequence_number('num', 0, 10, 1.5);

        self::assertEquals(
            [
                new Rows(Row::create(float_entry('num', 0))),
                new Rows(Row::create(float_entry('num', 1.5))),
                new Rows(Row::create(float_entry('num', 3))),
                new Rows(Row::create(float_entry('num', 4.5))),
                new Rows(Row::create(float_entry('num', 6))),
                new Rows(Row::create(float_entry('num', 7.5))),
                new Rows(Row::create(float_entry('num', 9))),
            ],
            $this->toRowsArray($extractor)
        );
    }
}
