<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Function;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{from_array, ref, to_memory};
use Flow\ETL\Flow;
use Flow\ETL\Memory\ArrayMemory;

final class StrReplaceTest extends FlowTestCase
{
    public function test_str_replace() : void
    {
        (new Flow())
            ->read(
                from_array(
                    [
                        ['key' => 'value'],
                    ]
                )
            )
            ->withEntry('str_replace', ref('key')->strReplace('e', 'es'))
            ->write(to_memory($memory = new ArrayMemory()))
            ->run();

        self::assertSame(
            [
                ['key' => 'value', 'str_replace' => 'values'],
            ],
            $memory->dump()
        );
    }
}
