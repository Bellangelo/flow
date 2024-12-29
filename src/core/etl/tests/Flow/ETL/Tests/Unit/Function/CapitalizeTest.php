<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Function;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{ref, str_entry};
use Flow\ETL\Row;

final class CapitalizeTest extends FlowTestCase
{
    public function test_capitalize_valid_string() : void
    {
        self::assertSame(
            'This Is A Value',
            ref('string')->capitalize()->eval(Row::create(str_entry('string', 'this is a value')))
        );
    }
}
