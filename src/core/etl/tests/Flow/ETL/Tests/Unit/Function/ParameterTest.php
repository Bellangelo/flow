<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Function;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{ref, row, str_entry, type_boolean, type_int, type_string};
use Flow\ETL\Function\Parameter;

final class ParameterTest extends FlowTestCase
{
    public function test_as_one_of() : void
    {
        $parameter = new Parameter(ref('value'));

        self::assertNull($parameter->as(row(str_entry('value', '42')), type_int(), type_boolean()));
        self::assertSame('42', $parameter->as(row(str_entry('value', '42')), type_string(), type_int()));
    }

    public function test_as_scalar() : void
    {
        $parameter = new Parameter(ref('value'));

        self::assertNull($parameter->as(row(str_entry('value', '42')), type_int()));
        self::assertSame('42', $parameter->as(row(str_entry('value', '42')), type_string()));
    }
}
