<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use Flow\ETL\{Row, Rows, Tests\FlowTestCase};
use Flow\ETL\Extractor\{PipelineExtractor, RowsExtractor};
use Flow\ETL\Pipeline\SynchronousPipeline;
use function Flow\ETL\DSL\int_entry;

final class PipelineExtractorTest extends FlowTestCase
{
    public function test_pipeline_extractor() : void
    {
        $pipeline = new SynchronousPipeline(new RowsExtractor(
            new Rows(Row::create(int_entry('id', 1)), Row::create(int_entry('id', 2))),
            new Rows(Row::create(int_entry('id', 3)), Row::create(int_entry('id', 4))),
            new Rows(Row::create(int_entry('id', 5)), Row::create(int_entry('id', 6))),
        ));

        $extractor = new PipelineExtractor($pipeline);

        FlowTestCase::assertExtractedBatchesCount(3, $extractor);
    }
}
