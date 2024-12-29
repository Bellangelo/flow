<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Function;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{ref, str_entry};
use Flow\ETL\Function\Trim\Type;
use Flow\ETL\Row;

final class TrimTest extends FlowTestCase
{
    public function test_trim_both_valid_string() : void
    {
        self::assertSame(
            'value',
            ref('string')->trim()->eval(Row::create(str_entry('string', '   value')))
        );
    }

    public function test_trim_left_valid_string() : void
    {
        self::assertSame(
            'value   ',
            ref('string')->trim(Type::LEFT)->eval(Row::create(str_entry('string', '   value   ')))
        );
    }

    public function test_trim_right_valid_string() : void
    {
        self::assertSame(
            '   value',
            ref('string')->trim(Type::RIGHT)->eval(Row::create(str_entry('string', '   value   ')))
        );
    }
}
