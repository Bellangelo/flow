<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\Elasticsearch\EntryIdFactory;

use function Flow\ETL\DSL\string_entry;
use Flow\ETL\Adapter\Elasticsearch\IdFactory;
use Flow\ETL\Hash\{Algorithm, NativePHPHash};
use Flow\ETL\Row;
use Flow\ETL\Row\Entry;

final class HashIdFactory implements IdFactory
{
    /**
     * @var string[]
     */
    private array $entryNames;

    private Algorithm $hashAlgorithm;

    public function __construct(string ...$entryNames)
    {
        $this->entryNames = $entryNames;
        $this->hashAlgorithm = new NativePHPHash();
    }

    public function create(Row $row) : Entry
    {
        return string_entry(
            'id',
            $this->hashAlgorithm->hash(
                \implode(':', \array_map(fn (string $name) : string => (string) $row->valueOf($name), $this->entryNames))
            )
        );
    }

    public function withAlgorithm(Algorithm $algorithm) : self
    {
        $factory = new self(...$this->entryNames);
        $factory->hashAlgorithm = $algorithm;

        return $factory;
    }
}
