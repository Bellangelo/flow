<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Entry;

use function Flow\ETL\DSL\type_json;
use Flow\ArrayComparison\ArrayComparison;
use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\PHP\Type\Logical\JsonType;
use Flow\ETL\PHP\Type\Type;
use Flow\ETL\Row\Schema\Definition;
use Flow\ETL\Row\{Entry, Reference};

/**
 * @implements Entry<?array<mixed>, ?string>
 */
final class JsonEntry implements Entry
{
    use EntryRef;

    private bool $object = false;

    private readonly JsonType $type;

    private readonly ?array $value;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(private readonly string $name, array|string|null $value)
    {
        if ('' === $name) {
            throw InvalidArgumentException::because('Entry name cannot be empty');
        }

        if (\is_string($value)) {
            $this->object = \str_starts_with($value, '{') && \str_ends_with($value, '}');

            try {
                $this->value = (array) \json_decode($value, true, flags: \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new InvalidArgumentException("Invalid value given: '{$value}', reason: " . $e->getMessage(), previous: $e);
            }
        } else {
            $this->value = $value;
        }

        $this->type = type_json($this->value === null);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function object(string $name, ?array $value) : self
    {
        if (\is_array($value)) {
            foreach (\array_keys($value) as $key) {
                if (!\is_string($key)) {
                    throw InvalidArgumentException::because('All keys for JsonEntry object must be strings');
                }
            }
        }

        $entry = new self($name, $value);
        $entry->object = true;

        return $entry;
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function definition() : Definition
    {
        return Definition::json($this->name, $this->type()->nullable());
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
        $entryValue = $entry instanceof self ? $entry->value : $entry->value();
        $thisValue = $this->value;

        if ($entryValue === null && $thisValue !== null) {
            return false;
        }

        if ($entryValue !== null && $thisValue === null) {
            return false;
        }

        if ($entryValue === null && $thisValue === null) {
            return $this->is($entry->name())
                && $entry instanceof self
                && $this->type->isEqual($entry->type);
        }

        return $this->is($entry->name()) && $entry instanceof self && $this->type->isEqual($entry->type) && (new ArrayComparison())->equals($thisValue, $entryValue);
    }

    public function map(callable $mapper) : Entry
    {
        return new self($this->name, $mapper($this->value()));
    }

    public function name() : string
    {
        return $this->name;
    }

    public function rename(string $name) : Entry
    {
        $entry = new self($name, $this->value);
        $entry->object = $this->object;

        return $entry;
    }

    public function toString() : string
    {
        if ($this->value === null) {
            return '';
        }

        if (!\count($this->value) && $this->object) {
            return '{}';
        }

        return \json_encode($this->value, JSON_THROW_ON_ERROR);
    }

    public function type() : Type
    {
        return $this->type;
    }

    /**
     * @return null|array<mixed>
     */
    public function value() : ?array
    {
        return $this->value;
    }

    public function withValue(mixed $value) : Entry
    {
        return new self($this->name, $value);
    }
}
