<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Benchmark\Transformer;

use function Flow\ETL\DSL\{config, flow_context};
use Flow\ETL\Transformer\RenameEntryTransformer;
use Flow\ETL\{FlowContext, Rows};
use PhpBench\Attributes\{BeforeMethods, Groups};

#[BeforeMethods('setUp')]
#[Groups(['transformer'])]
final class RenameEntryTransformerBench
{
    private FlowContext $context;

    private Rows $rows;

    public function setUp() : void
    {
        $this->rows = Rows::fromArray(
            \array_merge(...\array_map(static fn () : array => [
                ['id' => 1, 'random' => false, 'text' => null, 'from' => 666],
                ['id' => 2, 'random' => true, 'text' => null, 'from' => 666],
                ['id' => 3, 'random' => false, 'text' => null, 'from' => 666],
                ['id' => 4, 'random' => true, 'text' => null, 'from' => 666],
                ['id' => 5, 'random' => false, 'text' => null, 'from' => 666],
            ], \range(0, 10_000)))
        );
        $this->context = flow_context(config());
    }

    public function bench_transform_10k_rows() : void
    {
        (new RenameEntryTransformer('from', 'to'))->transform($this->rows, $this->context);
    }
}
