<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\PHP\Type\Native;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{type_object};

final class ObjectTypeTest extends FlowTestCase
{
    public function test_valid() : void
    {
        self::assertTrue(
            type_object(\stdClass::class, true)->isValid(null)
        );
        self::assertFalse(
            type_object(\stdClass::class)->isValid(null)
        );
        self::assertFalse(
            type_object(\stdClass::class)->isValid('one')
        );
        self::assertFalse(
            type_object(\stdClass::class)->isValid(new \ArrayIterator([]))
        );
        self::assertTrue(
            type_object(\stdClass::class)->isValid(new \stdClass())
        );
    }
}
