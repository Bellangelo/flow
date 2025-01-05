<?php

declare(strict_types=1);

namespace Flow\ETL\PHP\Type\Native;

use function Flow\ETL\DSL\type_float;
use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\PHP\Type\Type;

/**
 * @implements Type<integer>
 */
final readonly class IntegerType implements Type
{
    public function __construct(private readonly bool $nullable = false)
    {
    }

    public static function fromArray(array $data) : Type
    {
        $nullable = $data['nullable'] ?? false;

        return new self($nullable);
    }

    public function isComparableWith(Type $type) : bool
    {
        if ($type instanceof self) {
            return true;
        }

        if ($type instanceof NullType) {
            return true;
        }

        if ($type instanceof FloatType) {
            return true;
        }

        return false;
    }

    public function isEqual(Type $type) : bool
    {
        return $type instanceof self && $this->nullable === $type->nullable;
    }

    public function isValid(mixed $value) : bool
    {
        if ($this->nullable && $value === null) {
            return true;
        }

        return \is_int($value);
    }

    public function makeNullable(bool $nullable) : Type
    {
        return new self($nullable);
    }

    public function merge(Type $type) : Type
    {
        if ($type instanceof NullType) {
            return $this->makeNullable(true);
        }

        if ($type instanceof FloatType) {
            return type_float($this->nullable || $type->nullable(), $type->precision);
        }

        if (!$type instanceof self) {
            throw new InvalidArgumentException('Cannot merge different types, ' . $this->toString() . ' and ' . $type->toString());
        }

        return new self($this->nullable || $type->nullable());
    }

    public function normalize() : array
    {
        return [
            'type' => 'integer',
            'nullable' => $this->nullable,
        ];
    }

    public function nullable() : bool
    {
        return $this->nullable;
    }

    public function toString() : string
    {
        return ($this->nullable ? '?' : '') . 'integer';
    }
}
