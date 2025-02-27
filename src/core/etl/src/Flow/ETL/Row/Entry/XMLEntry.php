<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Entry;

use function Flow\ETL\DSL\type_xml;
use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\PHP\Type\Logical\XMLType;
use Flow\ETL\PHP\Type\Type;
use Flow\ETL\Row\Schema\Definition;
use Flow\ETL\Row\{Entry, Reference};

/**
 * @implements Entry<?\DOMDocument, ?\DOMDocument>
 */
final class XMLEntry implements Entry
{
    use EntryRef;

    private readonly XMLType $type;

    private readonly ?\DOMDocument $value;

    public function __construct(private readonly string $name, \DOMDocument|string|null $value)
    {
        if (\is_string($value)) {
            $doc = new \DOMDocument();

            if (!@$doc->loadXML($value)) {
                throw new InvalidArgumentException(\sprintf('Given string "%s" is not valid XML', $value));
            }

            $this->value = $doc;
        } else {
            $this->value = $value;
        }

        $this->type = type_xml($this->value === null);
    }

    public function __serialize() : array
    {
        return [
            'name' => $this->name,
            /** @phpstan-ignore-next-line  */
            'value' => $this->value === null ? null : \base64_encode(\gzcompress($this->toString())),
            'type' => $this->type,
        ];
    }

    public function __toString() : string
    {
        if ($this->value === null) {
            return '';
        }

        return $this->toString();
    }

    public function __unserialize(array $data) : void
    {
        $this->name = $data['name'];
        $this->type = $data['type'];

        if ($data['value'] === null) {
            $this->value = null;

            return;
        }

        /** @phpstan-ignore-next-line  */
        $xmlString = \gzuncompress(\base64_decode((string) $data['value'], true));
        $doc = new \DOMDocument();

        /** @phpstan-ignore-next-line  */
        if (!@$doc->loadXML($xmlString)) {
            throw new InvalidArgumentException(\sprintf('Given string "%s" is not valid XML', $xmlString));
        }

        $this->value = $doc;
    }

    public function definition() : Definition
    {
        return Definition::xml($this->ref(), $this->type->nullable());
    }

    public function is(Reference|string $name) : bool
    {
        if ($name instanceof Reference) {
            return $this->name === $name->name();
        }

        return $this->name === $name;
    }

    public function isEqual(Entry $entry) : bool
    {
        if (!$entry instanceof self || !$this->is($entry->name())) {
            return false;
        }

        if (!$this->type->isEqual($entry->type)) {
            return false;
        }

        if ($entry->value?->documentElement === null && $this->value?->documentElement === null) {
            return true;
        }

        return $entry->value()?->C14N() === $this->value?->C14N();
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
        return new self($name, $this->value);
    }

    public function toString() : string
    {
        if ($this->value === null) {
            return '';
        }

        /** @phpstan-ignore-next-line */
        return $this->value->saveXML($this->value->documentElement);
    }

    public function type() : Type
    {
        return $this->type;
    }

    public function value() : ?\DOMDocument
    {
        return $this->value;
    }

    public function withValue(mixed $value) : Entry
    {
        return new self($this->name, $value);
    }
}
