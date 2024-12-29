<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Function;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{from_array, ref, to_memory};
use Flow\ETL\Flow;
use Flow\ETL\Function\Trim\Type;
use Flow\ETL\Memory\ArrayMemory;

final class TrimTest extends FlowTestCase
{
    public function test_trim_both() : void
    {
        (new Flow())
            ->read(
                from_array(
                    [
                        ['key' => ' value '],
                    ]
                )
            )
            ->withEntry('trim', ref('key')->trim())
            ->write(to_memory($memory = new ArrayMemory()))
            ->run();

        self::assertSame(
            [
                ['key' => ' value ', 'trim' => 'value'],
            ],
            $memory->dump()
        );
    }

    public function test_trim_custom_characters() : void
    {
        (new Flow())
            ->read(
                from_array(
                    [
                        ['key' => '-value '],
                    ]
                )
            )
            ->withEntry('trim', ref('key')->trim(characters: '-'))
            ->write(to_memory($memory = new ArrayMemory()))
            ->run();

        self::assertSame(
            [
                ['key' => '-value ', 'trim' => 'value '],
            ],
            $memory->dump()
        );
    }

    public function test_trim_left() : void
    {
        (new Flow())
            ->read(
                from_array(
                    [
                        ['key' => ' value '],
                    ]
                )
            )
            ->withEntry('trim', ref('key')->trim(Type::LEFT))
            ->write(to_memory($memory = new ArrayMemory()))
            ->run();

        self::assertSame(
            [
                ['key' => ' value ', 'trim' => 'value '],
            ],
            $memory->dump()
        );
    }

    public function test_trim_right() : void
    {
        (new Flow())
            ->read(
                from_array(
                    [
                        ['key' => ' value '],
                    ]
                )
            )
            ->withEntry('trim', ref('key')->trim(Type::RIGHT))
            ->write(to_memory($memory = new ArrayMemory()))
            ->run();

        self::assertSame(
            [
                ['key' => ' value ', 'trim' => ' value'],
            ],
            $memory->dump()
        );
    }
}
