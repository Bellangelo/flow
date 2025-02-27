<?php

declare(strict_types=1);

namespace Flow\Parquet\ParquetFile\Page;

use Flow\Parquet\ParquetFile\Schema\{FlatColumn, LogicalType, PhysicalType};

final readonly class ColumnData
{
    /**
     * @param PhysicalType $type
     * @param null|LogicalType $logicalType
     * @param array<int> $repetitions
     * @param array<int> $definitions
     * @param array $values
     */
    public function __construct(
        public PhysicalType $type,
        public ?LogicalType $logicalType,
        public array $repetitions,
        public array $definitions,
        public array $values,
    ) {
    }

    public static function initialize(FlatColumn $column) : self
    {
        return new self($column->type(), $column->logicalType(), [], [], []);
    }

    public function isEmpty() : bool
    {
        return \count($this->definitions) === 0 && \count($this->values) === 0;
    }

    public function merge(self $columnData) : self
    {
        if ($columnData->type !== $this->type) {
            throw new \LogicException('Column data type mismatch, expected ' . $this->type->name . ', got ' . $columnData->type->name);
        }

        if ($this->logicalType?->name() !== $columnData->logicalType?->name()) {
            throw new \LogicException('Column data logical type mismatch, expected ' . $this->logicalType?->name() . ', got ' . $columnData->logicalType?->name());
        }

        return new self(
            $this->type,
            $this->logicalType,
            \array_merge($this->repetitions, $columnData->repetitions),
            \array_merge($this->definitions, $columnData->definitions),
            \array_merge($this->values, $columnData->values),
        );
    }

    public function size() : int
    {
        if (!\count($this->definitions)) {
            return \count($this->values);
        }

        return \count($this->definitions);
    }

    /**
     * @return array{0: self, 1: self}
     */
    public function splitLastRow() : array
    {
        if (!\count($this->repetitions)) {
            return [$this, new self($this->type, $this->logicalType, [], [], [])];
        }

        $repetitions = [];
        $definitions = [];
        $values = [];

        $maxDefinition = $this->definitions ? \max($this->definitions) : 0;

        $lastRowRepetitions = [];
        $lastRowDefinitions = [];
        $lastRowValues = [];
        $valueIndex = 0;

        foreach ($this->repetitions as $index => $repetition) {
            $definition = $this->definitions[$index];

            if ($repetition === 0 && !\count($lastRowRepetitions)) {
                $lastRowRepetitions[] = $repetition;
                $lastRowDefinitions[] = $definition;

                if ($definition === $maxDefinition) {
                    $lastRowValues[] = $this->values[$valueIndex];
                    $valueIndex++;
                }

                continue;
            }

            if ($repetition === 0) {
                $repetitions = \array_merge($repetitions, $lastRowRepetitions);
                $definitions = \array_merge($definitions, $lastRowDefinitions);
                $values = \array_merge($values, $lastRowValues);

                $lastRowRepetitions = [$repetition];
                $lastRowDefinitions = [$definition];

                if ($definition === $maxDefinition) {
                    $lastRowValues = [$this->values[$valueIndex]];
                    $valueIndex++;
                } else {
                    $lastRowValues = [];
                }

                continue;
            }

            $lastRowRepetitions[] = $repetition;
            $lastRowDefinitions[] = $definition;

            if ($definition === $maxDefinition) {
                $lastRowValues[] = $this->values[$valueIndex];
                $valueIndex++;
            }
        }

        $currentValues = $this->values;

        if (\count($lastRowValues) === 0) {
            $lastRowValues = [];
        } else {
            $lastRowValues = \array_splice($currentValues, -\count($lastRowValues));
        }

        return [
            new self($this->type, $this->logicalType, $repetitions, $definitions, $values),
            new self($this->type, $this->logicalType, $lastRowRepetitions, $lastRowDefinitions, $lastRowValues),
        ];
    }
}
