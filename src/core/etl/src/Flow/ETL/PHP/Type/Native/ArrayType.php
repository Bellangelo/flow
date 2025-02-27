<?php

declare(strict_types=1);

namespace Flow\ETL\PHP\Type\Native;

use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\PHP\Type\Type;

/**
 * @implements Type<?array>
 */
final readonly class ArrayType implements Type
{
    public function __construct(private bool $empty = false, private bool $nullable = false)
    {
    }

    public static function empty() : self
    {
        return new self(true);
    }

    public static function fromArray(array $data) : self
    {
        return new self($data['empty'] ?? false, $data['nullable'] ?? false);
    }

    public function isComparableWith(Type $type) : bool
    {
        if ($type instanceof NullType) {
            return true;
        }

        if ($type instanceof self) {
            return true;
        }

        return false;
    }

    public function isEqual(Type $type) : bool
    {
        return $type instanceof self && $this->empty === $type->empty;
    }

    public function isValid(mixed $value) : bool
    {
        if ($this->nullable && $value === null) {
            return true;
        }

        return \is_array($value);
    }

    public function makeNullable(bool $nullable) : self
    {
        return new self($this->empty, $nullable);
    }

    public function merge(Type $type) : self
    {
        if ($type instanceof NullType) {
            return $this->makeNullable(true);
        }

        if (!$type instanceof self) {
            throw new InvalidArgumentException('Cannot merge different types, ' . $this->toString() . ' and ' . $type->toString());
        }

        return new self($this->empty || $type->empty, $this->nullable || $type->nullable());
    }

    public function normalize() : array
    {
        return [
            'type' => 'array',
            'empty' => $this->empty,
            'nullable' => $this->nullable,
        ];
    }

    public function nullable() : bool
    {
        return $this->nullable;
    }

    public function toString() : string
    {
        if ($this->empty) {
            return ($this->nullable ? '?' : '') . 'array<empty, empty>';
        }

        return ($this->nullable ? '?' : '') . 'array<mixed>';
    }
}
