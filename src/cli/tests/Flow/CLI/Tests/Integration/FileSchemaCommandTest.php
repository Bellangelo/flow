<?php

declare(strict_types=1);

namespace Flow\CLI\Tests\Integration;

use Flow\CLI\Command\FileSchemaCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class FileSchemaCommandTest extends TestCase
{
    public function test_run_schema() : void
    {
        $tester = new CommandTester(new FileSchemaCommand('file:schema'));

        $tester->execute(['input-file' => __DIR__ . '/Fixtures/orders.csv']);

        $tester->assertCommandIsSuccessful();

        self::assertSame(
            <<<'OUTPUT'
[{"ref":"order_id","type":{"type":"uuid","nullable":false},"metadata":[]},{"ref":"created_at","type":{"type":"string","nullable":false},"metadata":[]},{"ref":"updated_at","type":{"type":"string","nullable":false},"metadata":[]},{"ref":"discount","type":{"type":"string","nullable":true},"metadata":[]},{"ref":"address","type":{"type":"json","nullable":false},"metadata":[]},{"ref":"notes","type":{"type":"json","nullable":false},"metadata":[]},{"ref":"items","type":{"type":"json","nullable":false},"metadata":[]}]

OUTPUT,
            $tester->getDisplay()
        );
    }

    public function test_run_schema_with_pretty_output() : void
    {
        $tester = new CommandTester(new FileSchemaCommand('file:schema'));

        $tester->execute(['input-file' => __DIR__ . '/Fixtures/orders.csv', '--output-pretty' => true]);

        $tester->assertCommandIsSuccessful();

        self::assertSame(
            <<<'OUTPUT'
[
    {
        "ref": "order_id",
        "type": {
            "type": "uuid",
            "nullable": false
        },
        "metadata": []
    },
    {
        "ref": "created_at",
        "type": {
            "type": "string",
            "nullable": false
        },
        "metadata": []
    },
    {
        "ref": "updated_at",
        "type": {
            "type": "string",
            "nullable": false
        },
        "metadata": []
    },
    {
        "ref": "discount",
        "type": {
            "type": "string",
            "nullable": true
        },
        "metadata": []
    },
    {
        "ref": "address",
        "type": {
            "type": "json",
            "nullable": false
        },
        "metadata": []
    },
    {
        "ref": "notes",
        "type": {
            "type": "json",
            "nullable": false
        },
        "metadata": []
    },
    {
        "ref": "items",
        "type": {
            "type": "json",
            "nullable": false
        },
        "metadata": []
    }
]

OUTPUT,
            $tester->getDisplay()
        );
    }

    public function test_run_schema_with_table_output() : void
    {
        $tester = new CommandTester(new FileSchemaCommand('file:schema'));

        $tester->execute(['input-file' => __DIR__ . '/Fixtures/orders.csv', '--output-table' => true]);

        $tester->assertCommandIsSuccessful();

        self::assertSame(
            <<<'OUTPUT'
+------------+--------+----------+----------+
|       name |   type | nullable | metadata |
+------------+--------+----------+----------+
|   order_id |   uuid |    false |       [] |
| created_at | string |    false |       [] |
| updated_at | string |    false |       [] |
|   discount | string |     true |       [] |
|    address |   json |    false |       [] |
|      notes |   json |    false |       [] |
|      items |   json |    false |       [] |
+------------+--------+----------+----------+
7 rows

OUTPUT,
            $tester->getDisplay()
        );
    }

    public function test_run_schema_with_table_output_and_auto_cast() : void
    {
        $tester = new CommandTester(new FileSchemaCommand('file:schema'));

        $tester->execute(['input-file' => __DIR__ . '/Fixtures/orders.csv', '--output-table' => true, '--schema-auto-cast' => true]);

        $tester->assertCommandIsSuccessful();

        self::assertSame(
            <<<'OUTPUT'
+------------+----------+----------+----------+
|       name |     type | nullable | metadata |
+------------+----------+----------+----------+
|   order_id |     uuid |    false |       [] |
| created_at | datetime |    false |       [] |
| updated_at | datetime |    false |       [] |
|   discount |    float |     true |       [] |
|    address |      map |    false |       [] |
|      notes |     list |    false |       [] |
|      items |     list |    false |       [] |
+------------+----------+----------+----------+
7 rows

OUTPUT,
            $tester->getDisplay()
        );
    }

    public function test_run_schema_with_table_output_and_limit_5() : void
    {
        $tester = new CommandTester(new FileSchemaCommand('file:schema'));

        $tester->execute(['input-file' => __DIR__ . '/Fixtures/orders.csv', '--output-table' => true, '--schema-auto-cast' => true, '--input-file-limit' => 5]);

        $tester->assertCommandIsSuccessful();

        self::assertSame(
            <<<'OUTPUT'
+------------+----------+----------+----------+
|       name |     type | nullable | metadata |
+------------+----------+----------+----------+
|   order_id |     uuid |    false |       [] |
| created_at | datetime |    false |       [] |
| updated_at | datetime |    false |       [] |
|   discount |    float |     true |       [] |
|    address |      map |    false |       [] |
|      notes |     list |    false |       [] |
|      items |     list |    false |       [] |
+------------+----------+----------+----------+
7 rows

OUTPUT,
            $tester->getDisplay()
        );
    }

    public function test_run_schema_with_table_output_on_json() : void
    {
        $tester = new CommandTester(new FileSchemaCommand('file:schema'));

        $tester->execute(['input-file' => __DIR__ . '/Fixtures/orders.json', '--output-table' => true, '--schema-auto-cast' => true, '--input-file-limit' => 5]);

        $tester->assertCommandIsSuccessful();

        self::assertSame(
            <<<'OUTPUT'
+--------------+-----------+----------+----------+
|         name |      type | nullable | metadata |
+--------------+-----------+----------+----------+
|     order_id |      uuid |    false |       [] |
|   created_at |  datetime |    false |       [] |
|   updated_at |  datetime |    false |       [] |
| cancelled_at |    string |     true |       [] |
|  total_price |     float |    false |       [] |
|     discount |     float |    false |       [] |
|     customer | structure |    false |       [] |
|      address | structure |    false |       [] |
|        notes |      list |    false |       [] |
+--------------+-----------+----------+----------+
9 rows

OUTPUT,
            $tester->getDisplay()
        );
    }

    public function test_run_schema_with_table_output_on_parquet() : void
    {
        $tester = new CommandTester(new FileSchemaCommand('file:schema'));

        $tester->execute(['input-file' => __DIR__ . '/Fixtures/orders.parquet', '--output-table' => true, '--schema-auto-cast' => true, '--input-file-limit' => 5]);

        $tester->assertCommandIsSuccessful();

        self::assertSame(
            <<<'OUTPUT'
+------------+----------+----------+----------+
|       name |     type | nullable | metadata |
+------------+----------+----------+----------+
|   order_id |     uuid |    false |       [] |
| created_at | datetime |    false |       [] |
| updated_at | datetime |    false |       [] |
|   discount |    float |     true |       [] |
|      email |   string |    false |       [] |
|   customer |   string |    false |       [] |
|    address |      map |    false |       [] |
|      notes |     list |    false |       [] |
|      items |     list |    false |       [] |
+------------+----------+----------+----------+
9 rows

OUTPUT,
            $tester->getDisplay()
        );
    }

    public function test_run_schema_with_table_output_on_txt() : void
    {
        $tester = new CommandTester(new FileSchemaCommand('file:schema'));

        $tester->execute(['input-file' => __DIR__ . '/Fixtures/orders.txt', '--output-table' => true, '--schema-auto-cast' => true]);

        $tester->assertCommandIsSuccessful();

        self::assertSame(
            <<<'OUTPUT'
+------+--------+----------+----------+
| name |   type | nullable | metadata |
+------+--------+----------+----------+
| text | string |    false |       [] |
+------+--------+----------+----------+
1 rows

OUTPUT,
            $tester->getDisplay()
        );
    }

    public function test_run_schema_with_table_output_on_xml() : void
    {
        $tester = new CommandTester(new FileSchemaCommand('file:schema'));

        $tester->execute(['input-file' => __DIR__ . '/Fixtures/orders.xml', '--input-xml-node-path' => 'root/row', '--output-table' => true, '--schema-auto-cast' => true, '--input-file-limit' => 5]);

        $tester->assertCommandIsSuccessful();

        self::assertSame(
            <<<'OUTPUT'
+------+------+----------+----------+
| name | type | nullable | metadata |
+------+------+----------+----------+
| node |  xml |    false |       [] |
+------+------+----------+----------+
1 rows

OUTPUT,
            $tester->getDisplay()
        );
    }
}
