<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use function Flow\ETL\DSL\rows;
use Flow\ETL\{Config, Extractor, FlowContext, Rows};
use PHPUnit\Framework\TestCase;

abstract class FlowTestCase extends TestCase
{
    public function assertExtractedBatchesCount(
        int $expectedCount,
        Extractor $extractor,
        ?FlowContext $flowContext = null,
        string $message = '',
    ) : void {
        $flowContext = $flowContext ?? new FlowContext(Config::default());

        static::assertCount(
            $expectedCount,
            \iterator_to_array($extractor->extract($flowContext)),
            $message
        );
    }

    public function assertExtractedBatchesSize(
        int $expectedCount,
        Extractor $extractor,
        ?FlowContext $flowContext = null,
        string $message = '',
    ) : void {
        $flowContext = $flowContext ?? new FlowContext(Config::default());
        $extractorContainsBatches = false;

        foreach ($extractor->extract($flowContext) as $rows) {
            static::assertCount($expectedCount, $rows, $message);
            $extractorContainsBatches = true;
        }

        if (!$extractorContainsBatches) {
            static::fail('Extractor does not contain any batches');
        }
    }

    /**
     * @param array<mixed> $expectedArray
     * @param Extractor $extractor
     * @param null|FlowContext $flowContext
     */
    public function assertExtractedRowsAsArrayEquals(
        array $expectedArray,
        Extractor $extractor,
        ?FlowContext $flowContext = null,
        string $message = '',
    ) : void {
        $flowContext = $flowContext ?? new FlowContext(Config::default());
        $extractedRows = rows();

        foreach ($extractor->extract($flowContext) as $nextRows) {
            $extractedRows = $extractedRows->merge($nextRows);
        }

        static::assertEquals($expectedArray, $extractedRows->toArray(), $message);
    }

    public function assertExtractedRowsCount(
        int $expectedCount,
        Extractor $extractor,
        ?FlowContext $flowContext = null,
        string $message = '',
    ) : void {
        $flowContext = $flowContext ?? new FlowContext(Config::default());
        $totalRows = 0;

        foreach ($extractor->extract($flowContext) as $rows) {
            $totalRows += $rows->count();
        }

        static::assertSame($expectedCount, $totalRows, $message);
    }

    public function assertExtractedRowsEquals(
        Rows $expectedRows,
        Extractor $extractor,
        ?FlowContext $flowContext = null,
        string $message = '',
    ) : void {
        $flowContext = $flowContext ?? new FlowContext(Config::default());
        $extractedRows = rows();

        foreach ($extractor->extract($flowContext) as $nextRows) {
            $extractedRows = $extractedRows->merge($nextRows);
        }

        static::assertEquals($expectedRows, $extractedRows, $message);
    }
}
