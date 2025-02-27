<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\Parquet\Tests\Benchmark;

use function Flow\ETL\Adapter\Parquet\{from_parquet, to_parquet};
use function Flow\ETL\DSL\{config, flow_context};
use Flow\ETL\{FlowContext, Rows};
use PhpBench\Attributes\Groups;

#[Groups(['loader'])]
final class ParquetLoaderBench
{
    private readonly FlowContext $context;

    private readonly string $outputPath;

    private Rows $rows;

    public function __construct()
    {
        $this->context = flow_context(config());
        $this->outputPath = \tempnam(\sys_get_temp_dir(), 'etl_parquet_loader_bench') . '.parquet';
        $this->rows = \Flow\ETL\DSL\rows();

        foreach (from_parquet(__DIR__ . '/Fixtures/orders_10k.parquet')->extract($this->context) as $rows) {
            $this->rows = $this->rows->merge($rows);
        }
    }

    public function __destruct()
    {
        if (!\file_exists($this->outputPath)) {
            throw new \RuntimeException("Benchmark failed, \"{$this->outputPath}\" doesn't exist");
        }

        \unlink($this->outputPath);
    }

    public function bench_load_10k() : void
    {
        to_parquet($this->outputPath)->load($this->rows, $this->context);
    }
}
