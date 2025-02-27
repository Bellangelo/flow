<?php

declare(strict_types=1);

namespace Flow\ETL\Transformer;

use Flow\ETL\{FlowContext, Row, Rows, Transformer};

final readonly class RenameStrReplaceAllEntriesTransformer implements Transformer
{
    public function __construct(
        private string $search,
        private string $replace,
    ) {
    }

    public function transform(Rows $rows, FlowContext $context) : Rows
    {
        return $rows->map(function (Row $row) : Row {
            foreach ($row->entries()->all() as $entry) {
                $row = $row->rename($entry->name(), \str_replace($this->search, $this->replace, $entry->name()));
            }

            return $row;
        });
    }
}
