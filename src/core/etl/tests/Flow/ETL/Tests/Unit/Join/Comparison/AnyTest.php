<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Join\Comparison;

use Flow\ETL\Tests\FlowTestCase;
use function Flow\ETL\DSL\int_entry;
use Flow\ETL\Join\Comparison;
use Flow\ETL\Join\Comparison\Any;
use Flow\ETL\Row;

final class AnyTest extends FlowTestCase
{
    public function test_failure() : void
    {
        $comparison1 = $this->createStub(Comparison::class);
        $comparison1
            ->method('compare')
            ->willReturn(false);

        $comparison2 = $this->createStub(Comparison::class);
        $comparison2
            ->method('compare')
            ->willReturn(false);

        self::assertFalse(
            (new Any($comparison1, $comparison2))
                ->compare(
                    Row::create(int_entry('id', 1)),
                    Row::create(int_entry('id', 2)),
                )
        );
    }

    public function test_success() : void
    {
        $comparison1 = $this->createStub(Comparison::class);
        $comparison1
            ->method('compare')
            ->willReturn(true);

        $comparison2 = $this->createStub(Comparison::class);
        $comparison2
            ->method('compare')
            ->willReturn(false);

        self::assertTrue(
            (new Any($comparison1, $comparison2))
                ->compare(
                    Row::create(int_entry('id', 1)),
                    Row::create(int_entry('id', 2)),
                )
        );
    }
}
