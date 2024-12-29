<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Function;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{lit, lower};
use Flow\ETL\Row;

final class ToLowerTest extends FlowTestCase
{
    public function test_string_to_lower() : void
    {
        self::assertSame(
            'lower',
            lower(lit('LOWER'))->eval(Row::create())
        );
    }
}
