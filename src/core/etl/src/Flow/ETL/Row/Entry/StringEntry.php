<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Entry;

use function Flow\ETL\DSL\type_string;
use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\PHP\Type\Native\StringType;
use Flow\ETL\PHP\Type\Type;
use Flow\ETL\Row\Schema\Definition;
use Flow\ETL\Row\{Entry, Reference, Schema\Metadata};

/**
 * @implements Entry<?string, ?string>
 */
final class StringEntry implements Entry
{
    use EntryRef;

    private bool $fromNull = false;

    private readonly StringType $type;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(private readonly string $name, private readonly ?string $value)
    {
        if ('' === $name) {
            throw InvalidArgumentException::because('Entry name cannot be empty');
        }

        $this->type = type_string($this->value === null);
    }

    public static function fromNull(string $name) : self
    {
        $entry = new self($name, null);
        $entry->fromNull = true;

        return $entry;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function lowercase(string $name, string $value) : self
    {
        return new self($name, \mb_strtolower($value));
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function uppercase(string $name, string $value) : self
    {
        return new self($name, \mb_strtoupper($value));
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function definition() : Definition
    {
        return Definition::string(
            $this->name,
            $this->type->nullable(),
            $this->fromNull ? Metadata::fromArray([Metadata::FROM_NULL => true]) : null
        );
    }

    public function is(string|Reference $name) : bool
    {
        if ($name instanceof Reference) {
            return $this->name === $name->name();
        }

        return $this->name === $name;
    }

    public function isEqual(Entry $entry) : bool
    {
        return $this->is($entry->name()) && $entry instanceof self && $this->type->isEqual($entry->type) && $this->value() === $entry->value();
    }

    public function map(callable $mapper) : Entry
    {
        return new self($this->name, $mapper($this->value()));
    }

    public function name() : string
    {
        return $this->name;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function rename(string $name) : Entry
    {
        return new self($name, $this->value);
    }

    public function toLowercase() : self
    {
        return new self($this->name, $this->value ? \mb_strtolower($this->value) : null);
    }

    public function toString() : string
    {
        $value = $this->value();

        if ($value === null) {
            return '';
        }

        return $value;
    }

    public function toUppercase() : self
    {
        return new self($this->name, $this->value ? \mb_strtoupper($this->value) : null);
    }

    public function type() : Type
    {
        return $this->type;
    }

    public function value() : ?string
    {
        return $this->value;
    }

    public function withValue(mixed $value) : Entry
    {
        return new self($this->name, $value);
    }
}
