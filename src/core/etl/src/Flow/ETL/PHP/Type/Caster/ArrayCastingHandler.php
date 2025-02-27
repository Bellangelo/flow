<?php

declare(strict_types=1);

namespace Flow\ETL\PHP\Type\Caster;

use Flow\ETL\Exception\CastingException;
use Flow\ETL\PHP\Type\Caster\XML\XMLConverter;
use Flow\ETL\PHP\Type\Native\ArrayType;
use Flow\ETL\PHP\Type\{Caster, Type};

final class ArrayCastingHandler implements CastingHandler
{
    /**
     * @param Type<array> $type
     */
    public function supports(Type $type) : bool
    {
        return $type instanceof ArrayType;
    }

    /**
     * @param Type<array> $type
     */
    public function value(mixed $value, Type $type, Caster $caster, Options $options) : array
    {
        try {
            if (\is_array($value)) {
                return $value;
            }

            if (\is_string($value) && (\str_starts_with($value, '{') || \str_starts_with($value, '['))) {
                return \json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
            }

            if ($value instanceof \DOMDocument) {
                return (new XMLConverter())->toArray($value);
            }

            if (\is_object($value)) {
                return \json_decode(\json_encode($value, JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR);
            }

            return (array) $value;
        } catch (\Throwable) {
            throw new CastingException($value, $type);
        }
    }
}
