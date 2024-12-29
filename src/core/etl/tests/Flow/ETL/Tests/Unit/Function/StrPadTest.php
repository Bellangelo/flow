<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Function;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{ref, str_entry};
use Flow\ETL\Row;

final class StrPadTest extends FlowTestCase
{
    public function test_str_pad_on_valid_string() : void
    {
        self::assertSame(
            '----N',
            ref('value')->strPad(5, '-', \STR_PAD_LEFT)->eval(Row::create(str_entry('value', 'N'))),
        );
    }
}
