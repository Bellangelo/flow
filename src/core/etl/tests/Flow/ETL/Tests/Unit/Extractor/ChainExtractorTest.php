<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use function Flow\ETL\DSL\{from_all, int_entry};
use Flow\ETL\{Extractor, FlowContext, Row, Rows};

final class ChainExtractorTest extends ExtractorTestCase
{
    public function test_chain_extractor() : void
    {
        $extractor = from_all(
            new class implements Extractor {
                public function extract(FlowContext $context) : \Generator
                {
                    yield new Rows(Row::create(int_entry('id', 1)));
                    yield new Rows(Row::create(int_entry('id', 2)));
                }
            },
            new class implements Extractor {
                public function extract(FlowContext $context) : \Generator
                {
                    yield new Rows(Row::create(int_entry('id', 3)));
                    yield new Rows(Row::create(int_entry('id', 4)));
                }
            },
        );

        self::assertEquals(
            [
                new Rows(Row::create(int_entry('id', 1))),
                new Rows(Row::create(int_entry('id', 2))),
                new Rows(Row::create(int_entry('id', 3))),
                new Rows(Row::create(int_entry('id', 4))),
            ],
            $this->toRowsArray($extractor)
        );
    }
}
