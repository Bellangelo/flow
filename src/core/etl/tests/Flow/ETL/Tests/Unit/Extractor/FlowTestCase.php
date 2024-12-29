<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use function Flow\ETL\DSL\rows;
use Flow\ETL\{Config, Extractor, FlowContext, Rows};
use PHPUnit\Framework\TestCase;

abstract class FlowTestCase extends TestCase
{
    public function assertExtractedBatchesCount(int $expectedCount, Extractor $extractor) : void
    {
        static::assertCount(
            $expectedCount,
            \iterator_to_array($extractor->extract(new FlowContext(Config::default())))
        );
    }

    public function assertExtractedBatchesSize(int $expectedCount, Extractor $extractor) : void
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

    /**
     * @param array<mixed> $expectedArray
     * @param Extractor $extractor
     */
    public function assertExtractedRowsAsArrayEquals(array $expectedArray, Extractor $extractor) : void
    {
        $extractedRows = rows();

        foreach ($extractor->extract(new FlowContext(Config::default())) as $nextRows) {
            $extractedRows = $extractedRows->merge($nextRows);
        }

        static::assertEquals($expectedArray, $extractedRows->toArray());
    }

    public function assertExtractedRowsCount(int $expectedCount, Extractor $extractor) : void
    {
        $totalRows = 0;

        foreach ($extractor->extract(new FlowContext(Config::default())) as $rows) {
            $totalRows += $rows->count();
        }

        static::assertSame($expectedCount, $totalRows);
    }

    public function assertExtractedRowsEquals(Rows $expectedRows, Extractor $extractor) : void
    {
        $extractedRows = rows();

        foreach ($extractor->extract(new FlowContext(Config::default())) as $nextRows) {
            $extractedRows = $extractedRows->merge($nextRows);
        }

        static::assertEquals($expectedRows, $extractedRows);
    }
}
