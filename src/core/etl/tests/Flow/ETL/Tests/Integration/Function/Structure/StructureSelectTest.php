<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Function\Structure;

use function Flow\ETL\DSL\{df, from_array, structure_ref};
use Flow\ETL\Tests\FlowTestCase;

final class StructureSelectTest extends FlowTestCase
{
    public function test_structure_keep() : void
    {
        $rows = df()
            ->read(
                from_array(
                    [
                        [
                            'user' => [
                                'id' => 1,
                                'name' => 'username',
                                'email' => 'user_email@email.com',
                                'tags' => [
                                    'tag1',
                                    'tag2',
                                    'tag3',
                                ],
                            ],
                        ],
                    ]
                )
            )
            ->withEntry('user', structure_ref('user')->select('id', 'email', 'tags'))
            ->fetch();

        self::assertEquals(
            [
                [
                    'user' => [
                        'id' => 1,
                        'email' => 'user_email@email.com',
                        'tags' => [
                            'tag1',
                            'tag2',
                            'tag3',
                        ],
                    ],
                ],
            ],
            $rows->toArray()
        );
    }
}
