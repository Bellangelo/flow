<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use Flow\ETL\Extractor\ChunkExtractor;
use Flow\ETL\Tests\Double\FakeExtractor;

final class ChunkExtractorTest extends ExtractorTestCase
{
    public function test_chunk_extractor() : void
    {
        $extractor = new ChunkExtractor(new FakeExtractor($batches = 100), $chunkSize = 10);

        $this->assertCountRows(
            $batches / $chunkSize,
            $extractor
        );
    }

    public function test_chunk_extractor_with_chunk_size_greater_than_() : void
    {
        $extractor = new ChunkExtractor(new FakeExtractor(total: 20), chunkSize: 25);

        $this->assertCountRows(
            1,
            $extractor
        );
    }
}
