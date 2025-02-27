<?php

declare(strict_types=1);

namespace Flow\ETL;

use function Flow\ETL\DSL\{array_to_rows, row};
use Flow\ETL\Exception\{DuplicatedEntriesException, InvalidArgumentException, RuntimeException};
use Flow\ETL\Hash\{Algorithm, NativePHPHash};
use Flow\ETL\Join\Expression;
use Flow\ETL\Row\CartesianProduct;
use Flow\ETL\Row\Comparator\NativeComparator;
use Flow\ETL\Row\Factory\NativeEntryFactory;
use Flow\ETL\Row\{Comparator, Entries, EntryFactory, Reference, References, Schema, SortOrder};
use Flow\Filesystem\{Partition, Partitions};

/**
 * @implements \ArrayAccess<int, Row>
 * @implements \IteratorAggregate<int, Row>
 */
final class Rows implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private Partitions $partitions;

    /**
     * @var array<int, Row>
     */
    private readonly array $rows;

    public function __construct(Row ...$rows)
    {
        $this->rows = \array_values($rows);
        $this->partitions = new Partitions();
    }

    public static function fromArray(array $data, EntryFactory $entryFactory = new NativeEntryFactory()) : self
    {
        return array_to_rows($data, $entryFactory);
    }

    /**
     * @param array<int, Row>|array<Row> $rows
     */
    public static function partitioned(array $rows, array|Partitions $partitions) : self
    {
        if (!\count($rows)) {
            return new self();
        }

        $partitions = \is_array($partitions) ? new Partitions(...$partitions) : $partitions;

        $rows = new self(...$rows);
        $rows->partitions = $partitions;

        return $rows;
    }

    public function add(Row ...$rows) : self
    {
        return new self(
            ...$this->rows,
            ...$rows
        );
    }

    /**
     * @return array<Row>
     */
    public function all() : array
    {
        return $this->rows;
    }

    /**
     * @param int<1, max> $size
     *
     * @return \Generator<Rows>
     */
    public function chunks(int $size) : \Generator
    {
        foreach (\array_chunk($this->rows, $size) as $chunk) {
            yield self::partitioned($chunk, $this->partitions);
        }
    }

    public function count() : int
    {
        return \count($this->rows);
    }

    public function diffLeft(self $rows) : self
    {
        $differentRows = [];

        foreach ($this->rows as $row) {
            $found = false;

            foreach ($rows->rows as $otherRow) {
                if ($row->isEqual($otherRow)) {
                    $found = true;

                    break;
                }
            }

            if (!$found) {
                $differentRows[] = $row;
            }
        }

        return self::partitioned($differentRows, $this->partitions);
    }

    public function diffRight(self $rows) : self
    {
        $differentRows = [];

        foreach ($rows->rows as $row) {
            $found = false;

            foreach ($this->rows as $otherRow) {
                if ($row->isEqual($otherRow)) {
                    $found = true;

                    break;
                }
            }

            if (!$found) {
                $differentRows[] = $row;
            }
        }

        return self::partitioned($differentRows, $this->partitions);
    }

    public function drop(int $size) : self
    {
        if ($size === 0) {
            return $this;
        }

        return self::partitioned(\array_slice($this->rows, $size), $this->partitions);
    }

    public function dropPartitions(bool $dropPartitionColumns = false) : self
    {
        $rows = new self(...$this->rows);

        if ($dropPartitionColumns) {
            return $rows->map(fn (Row $row) : Row => $row->remove(
                ...\array_map(
                    static fn (Partition $partition) : Reference => $partition->reference(),
                    $this->partitions->toArray()
                )
            ));
        }

        return $rows;
    }

    public function dropRight(int $size) : self
    {
        if ($size === 0) {
            return $this;
        }

        return self::partitioned(\array_slice($this->rows, 0, -$size), $this->partitions);
    }

    /**
     * @param callable(Row) : void $callable
     */
    public function each(callable $callable) : void
    {
        foreach ($this->rows as $row) {
            $callable($row);
        }
    }

    public function empty() : bool
    {
        return $this->count() === 0;
    }

    /**
     * @return array<Entries>
     */
    public function entries() : array
    {
        $entries = [];

        foreach ($this->rows as $row) {
            $entries[] = $row->entries();
        }

        return $entries;
    }

    /**
     * @param callable(Row) : bool $callable
     */
    public function filter(callable $callable) : self
    {
        $results = [];

        foreach ($this->rows as $row) {
            if ($callable($row)) {
                $results[] = $row;
            }
        }

        return self::partitioned($results, $this->partitions);
    }

    public function find(callable $callable) : self
    {
        if (0 === $this->count()) {
            return new self();
        }

        $rows = [];

        foreach ($this->rows as $row) {
            if ($callable($row)) {
                $rows[] = $row;
            }
        }

        return self::partitioned($rows, $this->partitions);
    }

    public function findOne(callable $callable) : ?Row
    {
        foreach ($this->rows as $row) {
            if ($callable($row)) {
                return $row;
            }
        }

        return null;
    }

    public function first() : Row
    {
        return $this->rows[0] ?? throw new RuntimeException('First row does not exist in empty collection');
    }

    /**
     * @param callable(Row) : array<Row> $callable
     */
    public function flatMap(callable $callable) : self
    {
        $rows = [];

        foreach ($this->rows as $row) {
            $rows[] = $callable($row);
        }

        return new self(...\array_merge(...$rows));
    }

    /**
     * @return \Iterator<int, Row>
     */
    public function getIterator() : \Iterator
    {
        return new \ArrayIterator($this->rows);
    }

    public function hash(Algorithm $algorithm = new NativePHPHash()) : string
    {
        $hash = '';

        foreach ($this->rows as $row) {
            $hash .= $row->hash($algorithm);
        }

        return $algorithm->hash($hash);
    }

    public function isPartitioned() : bool
    {
        return \count($this->partitions) > 0;
    }

    public function joinCross(self $right, string $joinPrefix = 'joined_') : self
    {
        /**
         * @var array<Row> $joined
         */
        $joined = [];

        if ($right->count() === 0) {
            return $this;
        }

        if ($this->count() === 0) {
            return $right;
        }

        foreach ($this->rows as $leftRow) {
            foreach ($right->rows as $rightRow) {
                try {
                    $joined[] = $leftRow->merge($rightRow, $joinPrefix);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException($e->getMessage() . '. Please consider using join prefix option');
                }
            }
        }

        return new self(...$joined);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function joinInner(self $right, Expression $expression) : self
    {
        /**
         * @var array<Row> $joined
         */
        $joined = [];

        foreach ($this->rows as $leftRow) {
            /** @var ?Row $joinedRow */
            $joinedRow = null;

            foreach ($right as $rightRow) {
                if ($expression->meet($leftRow, $rightRow)) {
                    try {
                        $joinedRow = $leftRow->merge($rightRow, $expression->prefix());
                    } catch (DuplicatedEntriesException $e) {
                        throw new DuplicatedEntriesException($e->getMessage() . ' try to use a different join prefix than: "' . $expression->prefix() . '"');
                    }

                    break;
                }
            }

            if ($joinedRow) {
                $joined[] = $joinedRow;
            }
        }

        return new self(...$joined);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function joinLeft(self $right, Expression $expression) : self
    {
        /**
         * @var array<Row> $joined
         */
        $joined = [];

        $rightSchema = $right->schema();

        foreach ($this->rows as $leftRow) {
            /** @var ?Row $joinedRow */
            $joinedRow = null;

            foreach ($right as $rightRow) {
                if ($expression->meet($leftRow, $rightRow)) {
                    try {
                        $joinedRow = $leftRow->merge($rightRow, $expression->prefix());
                    } catch (DuplicatedEntriesException $e) {
                        throw new DuplicatedEntriesException($e->getMessage() . ' try to use a different join prefix than: "' . $expression->prefix() . '"');
                    }

                    break;
                }
            }

            if ($joinedRow === null) {
                $entryFactory = new NativeEntryFactory();

                $entries = [];

                foreach ($rightSchema->definitions() as $definition) {
                    $entries[] = $entryFactory->create($definition->entry()->name(), null, $definition->makeNullable());
                }

                $joinedRow = $leftRow->merge(row(...$entries), $expression->prefix());
            }

            $joined[] = $joinedRow;
        }

        return new self(...$joined);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function joinLeftAnti(self $right, Expression $expression) : self
    {
        /**
         * @var array<Row> $joined
         */
        $joined = [];

        foreach ($this->rows as $leftRow) {
            $foundRight = false;

            foreach ($right as $rightRow) {
                if (!$expression->meet($leftRow, $rightRow)) {
                    continue;
                }
                $foundRight = true;
            }

            if (!$foundRight) {
                $joined[] = $leftRow;
            }
        }

        return new self(...$joined);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function joinRight(self $right, Expression $expression) : self
    {
        /**
         * @var array<Row> $joined
         */
        $joined = [];

        $leftSchema = $this->schema();

        foreach ($right->rows as $rightRow) {
            /** @var ?Row $joinedRow */
            $joinedRow = null;

            foreach ($this->rows as $leftRow) {
                if ($expression->meet($leftRow, $rightRow)) {
                    try {
                        $joinedRow = $leftRow->merge($rightRow, $expression->prefix());
                    } catch (DuplicatedEntriesException $e) {
                        throw new DuplicatedEntriesException($e->getMessage() . ' try to use a different join prefix than: "' . $expression->prefix() . '"');
                    }

                    $joined[] = $joinedRow;
                }
            }

            if ($joinedRow === null) {
                $entryFactory = new NativeEntryFactory();

                $entries = [];

                foreach ($leftSchema->definitions() as $definition) {
                    $entries[] = $entryFactory->create($definition->entry()->name(), null, $definition->makeNullable());
                }

                $joined[] = row(...$entries)->merge($rightRow, $expression->prefix());
            }
        }

        return new self(...$joined);
    }

    /**
     * @param callable(Row) : Row $callable
     */
    public function map(callable $callable) : self
    {
        $rows = [];

        foreach ($this->rows as $row) {
            $rows[] = $callable($row);
        }

        return self::partitioned($rows, $this->partitions);
    }

    public function merge(self $rows) : self
    {
        if ($this->empty()) {
            return $rows;
        }

        if ($rows->empty()) {
            return $this;
        }

        if ($this->partitions->id() === $rows->partitions()->id()) {
            $mergedRows = new self(...$this->rows, ...$rows->rows);
            $mergedRows->partitions = $this->partitions;

            return $mergedRows;
        }

        return new self(...$this->rows, ...$rows->rows);
    }

    /**
     * @param int $offset
     *
     * @throws InvalidArgumentException
     */
    public function offsetExists($offset) : bool
    {
        if (!\is_int($offset)) {
            throw new InvalidArgumentException('Rows accepts only integer offsets');
        }

        return isset($this->rows[$offset]);
    }

    /**
     * @param int $offset
     *
     * @throws InvalidArgumentException
     */
    public function offsetGet($offset) : Row
    {
        if ($this->offsetExists($offset)) {
            return $this->rows[$offset];
        }

        throw new InvalidArgumentException("Row {$offset} does not exists.");
    }

    public function offsetSet(mixed $offset, mixed $value) : void
    {
        throw new RuntimeException('In order to add new rows use Rows::add(Row $row) : self');
    }

    /**
     * @param int $offset
     *
     * @throws RuntimeException
     */
    public function offsetUnset(mixed $offset) : void
    {
        throw new RuntimeException('In order to remove rows use Rows::remove(int $offset) : self');
    }

    /**
     * @param Reference|string $entry
     * @param Reference|string ...$entries
     *
     * @throws InvalidArgumentException
     *
     * @return array<Rows>
     */
    public function partitionBy(string|Reference $entry, string|Reference ...$entries) : array
    {
        $refs = References::init($entry, ...$entries);

        /** @var array<string, array<mixed>> $partitions */
        $partitions = [];

        foreach ($refs as $ref) {
            foreach ($this->rows as $row) {
                $partitions[$ref->name()][] = Partition::valueFromRow($ref, $row);
            }

            $partitions[$ref->name()] = \array_values(\array_unique($partitions[$ref->name()]));
        }

        /** @var array<Rows> $partitionedRows */
        $partitionedRows = [];

        /**
         * @var array<string, mixed> $partitionsData
         */
        foreach ((new CartesianProduct())($partitions) as $partitionsData) {
            $parts = Partition::fromArray($partitionsData);
            $rows = [];

            foreach ($this->rows as $row) {
                foreach ($parts as $partition) {
                    if (Partition::valueFromRow($partition->reference(), $row) !== $partition->value) {
                        continue 2;
                    }
                }

                $rows[] = $row;
            }

            if ($rows) {
                $partitionedRows[] = self::partitioned($rows, $parts);
            }
        }

        return $partitionedRows;
    }

    public function partitions() : Partitions
    {
        return $this->partitions;
    }

    /**
     * @param callable(mixed, Row) : mixed $callable
     * @param null|mixed $input
     *
     * @return null|mixed
     */
    public function reduce(callable $callable, mixed $input = null)
    {
        return \array_reduce($this->rows, $callable, $input);
    }

    /**
     * @return array<mixed>
     */
    public function reduceToArray(string|Reference $ref) : array
    {
        return $this->reduce(
            function (array $ids, Row $row) use ($ref) : array {
                $ids[] = $row->get($ref)->value();

                return $ids;
            },
            []
        );
    }

    public function remove(int $offset) : self
    {
        if (!$this->offsetExists($offset)) {
            throw new InvalidArgumentException("Rows does not have {$offset} offset");
        }

        $rows = \iterator_to_array($this->getIterator());
        unset($rows[$offset]);

        return self::partitioned($rows, $this->partitions);
    }

    public function reverse() : self
    {
        return self::partitioned(\array_reverse($this->rows), $this->partitions);
    }

    public function schema() : Schema
    {
        if (!$this->count()) {
            return new Schema();
        }

        /** @var ?Schema $schema */
        $schema = null;

        foreach ($this->rows as $row) {
            $schema = $schema === null
                ? $row->schema()
                : $schema->merge($row->schema());
        }

        /** @var Schema $schema */
        return $schema;
    }

    /**
     * @param callable(mixed, mixed) : int $callback
     */
    public function sort(callable $callback) : self
    {
        $rows = $this->rows;
        \usort($rows, $callback);

        return self::partitioned($rows, $this->partitions);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function sortAscending(string|Reference $ref) : self
    {
        $rows = $this->rows;
        \usort($rows, fn (Row $a, Row $b) : int => $a->valueOf($ref) <=> $b->valueOf($ref));

        return self::partitioned($rows, $this->partitions);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function sortBy(Reference ...$refs) : self
    {
        $rows = $this;

        foreach (\array_reverse($refs) as $ref) {
            $rows = $ref->sort() === SortOrder::ASC ? $rows->sortAscending($ref) : $rows->sortDescending($ref);
        }

        return $rows;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function sortDescending(string|Reference $ref) : self
    {
        $rows = $this->rows;
        \usort($rows, fn (Row $a, Row $b) : int => -($a->valueOf($ref) <=> $b->valueOf($ref)));

        return self::partitioned($rows, $this->partitions);
    }

    public function sortEntries() : self
    {
        return $this->map(fn (Row $row) : Row => $row->sortEntries());
    }

    public function take(int $size) : self
    {
        return self::partitioned(\array_slice($this->rows, 0, $size), $this->partitions);
    }

    public function takeRight(int $size) : self
    {
        return self::partitioned(\array_reverse(\array_slice($this->rows, -$size, $size)), $this->partitions);
    }

    /**
     * @return array<int, array<array-key, mixed>>
     */
    public function toArray(bool $withKeys = true) : array
    {
        $array = [];

        foreach ($this->rows as $row) {
            $array[] = $row->toArray($withKeys);
        }

        return $array;
    }

    public function unique(Comparator $comparator = new NativeComparator()) : self
    {
        /**
         * @var array<Row> $uniqueRows
         */
        $uniqueRows = [];

        foreach ($this->rows as $row) {
            $alreadyAdded = false;

            foreach ($uniqueRows as $uniqueRow) {
                if ($comparator->equals($row, $uniqueRow)) {
                    $alreadyAdded = true;

                    break;
                }
            }

            if (!$alreadyAdded) {
                $uniqueRows[] = $row;
            }
        }

        return self::partitioned($uniqueRows, $this->partitions);
    }
}
