<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use function Flow\ETL\DSL\{files, flow_context};
use Flow\ETL\Extractor\Signal;
use PHPUnit\Framework\TestCase;

final class FilesExtractorTest extends ExtractorTestCase
{
    public function test_extracting_files_from_directory() : void
    {
        $extractor = files(__DIR__ . '/Fixtures/FileListExtractor/*');

        self::assertCountMultiRows(3, 1, $extractor);
    }

    public function test_extracting_files_from_directory_after_getting_stop_signal() : void
    {
        $extractor = files(__DIR__ . '/Fixtures/FileListExtractor/*');
        $generator = $extractor->extract(flow_context());
        $totalRows = 0;

        foreach ($generator as $rows) {
            self::assertCount(1, $rows);
            $totalRows += $rows->count();
            $generator->send(Signal::STOP);
        }

        self::assertEquals(1, $totalRows);
    }

    public function test_extracting_files_from_directory_recursive() : void
    {
        $extractor = files(__DIR__ . '/Fixtures/FileListExtractor/**/*');

        self::assertCountMultiRows(6, 1, $extractor);
    }

    public function test_extracting_files_from_directory_with_limit() : void
    {
        $extractor = files(__DIR__ . '/Fixtures/FileListExtractor/**/*');
        $extractor->changeLimit(2);

        self::assertCountMultiRows(2, 1, $extractor);
    }
}
