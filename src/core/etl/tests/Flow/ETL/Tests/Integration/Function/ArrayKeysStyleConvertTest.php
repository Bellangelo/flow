<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Function;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{array_keys_style_convert, from_array, ref, to_memory};
use Flow\ETL\Flow;
use Flow\ETL\Memory\ArrayMemory;

final class ArrayKeysStyleConvertTest extends FlowTestCase
{
    public function test_array_keys_style_convert() : void
    {
        (new Flow())
            ->read(
                from_array(
                    [
                        ['id' => 1, 'array' => ['camelCased' => 1, 'snake_cased' => 2, 'space word' => 3]],
                    ]
                )
            )
            ->withEntry('array', array_keys_style_convert(ref('array'), 'kebab'))
            ->write(to_memory($memory = new ArrayMemory()))
            ->run();

        self::assertSame(
            [
                ['id' => 1, 'array' => ['camel-cased' => 1, 'snake-cased' => 2, 'space-word' => 3]],
            ],
            $memory->dump()
        );
    }
}
