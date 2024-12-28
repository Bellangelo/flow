<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use Flow\ETL\{Config, Extractor, FlowContext, Rows};
use PHPUnit\Framework\TestCase;

abstract class ExtractorTestCase extends TestCase
{
    public function assertExtractorCountRowsPerBatch(int $expectedCount, Extractor $extractor) : void
    {
        $extractorContainsBatches = false;

        foreach ($extractor->extract(new FlowContext(Config::default())) as $rows) {
            static::assertCount($expectedCount, $rows);
            $extractorContainsBatches = true;
        }

        if (!$extractorContainsBatches) {
            static::fail('Extractor does not contain any batches');
        }
    }

    public function assertExtractorCountRows(int $expectedCount, Extractor $extractor) : void
    {
        $totalRows = 0;

        foreach ($extractor->extract(new FlowContext(Config::default())) as $rows) {
            $totalRows += $rows->count();
        }

        static::assertSame($expectedCount, $totalRows);
    }

    public function assertExtractorCountBatches(int $expectedCount, Extractor $extractor) : void
    {
        static::assertCount(
            $expectedCount,
            \iterator_to_array($extractor->extract(new FlowContext(Config::default())))
        );
    }

    /**
     * @param array<mixed> $expectedArray
     * @param Extractor $extractor
     */
    public function assertExtractorSameArray(array $expectedArray, Extractor $extractor) : void
    {
        $data = [];

        foreach ($extractor->extract(new FlowContext(Config::default())) as $rowsData) {
            $data = [...$data, ...$rowsData->toArray()];
        }

        static::assertSame($expectedArray, $data);
    }

    /**
     * @param array<Rows> $expectedRows
     * @param Extractor $extractor
     */
    public function assertExtractorEqualsRows(array $expectedRows, Extractor $extractor) : void
    {
        static::assertEquals(
            $expectedRows,
            \iterator_to_array($extractor->extract(new FlowContext(Config::default())))
        );
    }
}
