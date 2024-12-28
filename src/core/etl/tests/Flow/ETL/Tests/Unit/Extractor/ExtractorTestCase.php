<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use Flow\ETL\{Config, Extractor, FlowContext, Row};
use PHPUnit\Framework\TestCase;

abstract class ExtractorTestCase extends TestCase
{
    public function assertCountMultiRows(int $expectedTotalCount, int $expectedCountPerRow, Extractor $extractor) : void
    {
        $totalRows = 0;

        foreach ($extractor->extract(new FlowContext(Config::default())) as $rows) {
            static::assertCount($expectedCountPerRow, $rows);
            $totalRows += $rows->count();
        }

        static::assertSame($expectedTotalCount, $totalRows);
    }

    public function assertCountRows(int $expectedCount, Extractor $extractor) : void
    {
        static::assertCount($expectedCount, $this->toRowsArray($extractor));
    }

    public function toArray(Extractor $extractor) : array
    {
        $data = [];

        foreach ($extractor->extract(new FlowContext(Config::default())) as $rowsData) {
            $data = [...$data, ...$rowsData->toArray()];
        }

        return $data;
    }

    /**
     * @param Extractor $extractor
     *
     * @return array<Row>
     */
    public function toRowsArray(Extractor $extractor) : array
    {
        return \iterator_to_array($extractor->extract(new FlowContext(Config::default())));
    }
}
