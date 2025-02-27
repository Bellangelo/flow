<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Fixtures;

final class Example
{
    public int $foo = 1;

    /** @phpstan-ignore-next-line */
    private readonly \DateTimeImmutable $bad;

    /** @phpstan-ignore-next-line */
    private int $bar = 2;

    /** @phpstan-ignore-next-line */
    private int $baz = 3;

    public function __construct()
    {
        $this->foo = 1;
        $this->bar = 2;
        $this->baz = 3;
        $this->bad = new \DateTimeImmutable('2020-01-01 00:00:00 UTC');
    }
}
