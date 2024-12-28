<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use Flow\ETL\Config;
use Flow\ETL\FlowContext;
use PHPUnit\Framework\TestCase;
use Flow\ETL\Extractor;

abstract class ExtractorTestCase extends TestCase
{
    public function toArray(Extractor $extractor): array
    {
        $data = [];

        foreach ($extractor->extract(new FlowContext(Config::default())) as $rowsData) {
            $data = [...$data, ...$rowsData->toArray()];
        }

        return $data;
    }
}