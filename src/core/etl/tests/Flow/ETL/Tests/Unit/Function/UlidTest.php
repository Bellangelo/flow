<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Function;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\{lit, ulid};
use Flow\ETL\Row;
use Symfony\Component\Uid\Ulid;

final class UlidTest extends FlowTestCase
{
    public function test_ulid() : void
    {
        $expression = ulid();
        self::assertTrue(
            Ulid::isValid(
                $expression->eval(Row::create())->toBase32()
            )
        );
        self::assertNotSame(
            $expression->eval(Row::create()),
            $expression->eval(Row::create())
        );
    }

    public function test_ulid_is_unique() : void
    {
        $expression = ulid();

        self::assertNotEquals(
            $expression->eval(Row::create()),
            $expression->eval(Row::create())
        );
    }

    public function test_ulid_with_invalid_value_returns_null() : void
    {
        self::assertNull(
            ulid(lit(''))->eval(Row::create())
        );
    }
}
