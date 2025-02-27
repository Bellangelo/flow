<?php

declare(strict_types=1);

namespace Flow\ETL\PHP\Type\Caster;

use function Flow\ETL\DSL\{type_string, type_xml};
use Flow\ETL\Exception\CastingException;
use Flow\ETL\PHP\Type\Logical\XMLType;
use Flow\ETL\PHP\Type\{Caster, Type};

final class XMLCastingHandler implements CastingHandler
{
    /**
     * @param Type<\DOMDocument> $type
     */
    public function supports(Type $type) : bool
    {
        return $type instanceof XMLType;
    }

    public function value(mixed $value, Type $type, Caster $caster, Options $options) : \DOMDocument
    {
        if ($value instanceof \DOMDocument) {
            return $value;
        }

        if (\is_string($value)) {
            $doc = new \DOMDocument();

            if (!@$doc->loadXML($value)) {
                throw new CastingException($value, type_xml());
            }

            return $doc;
        }

        try {
            $stringValue = $caster->to(type_string())->value($value);

            $doc = new \DOMDocument();

            if (!@$doc->loadXML((string) $stringValue)) {
                throw new CastingException($stringValue, type_xml());
            }

            return $doc;
        } catch (CastingException $e) {
            throw new CastingException($value, type_xml(), $e);
        }
    }
}
