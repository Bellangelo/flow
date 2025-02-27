<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\PHP\Type\Caster;

use function Flow\ETL\DSL\{caster, caster_options, type_xml};
use Flow\ETL\Exception\CastingException;
use Flow\ETL\PHP\Type\Caster\XMLCastingHandler;
use Flow\ETL\Tests\FlowTestCase;

final class XMLCastingHandlerTest extends FlowTestCase
{
    public function test_casting_integer_to_xml() : void
    {
        $this->expectException(CastingException::class);
        $this->expectExceptionMessage('Can\'t cast "integer" into "xml" type');

        (new XMLCastingHandler())->value(1, type_xml(), caster(), caster_options())->saveXML();
    }

    public function test_casting_string_to_xml() : void
    {
        self::assertSame(
            '<?xml version="1.0"?>' . "\n" . '<items><item>1</item></items>' . "\n",
            (new XMLCastingHandler())->value('<items><item>1</item></items>', type_xml(), caster(), caster_options())->saveXML()
        );
    }
}
