<?php

declare(strict_types=1);

namespace Flow\Parquet\ParquetFile\RowGroupBuilder\PageBuilder;

use Flow\Parquet\BinaryWriter\BinaryBufferWriter;
use Flow\Parquet\Options;
use Flow\Parquet\ParquetFile\Data\PlainValuesPacker;
use Flow\Parquet\ParquetFile\Page\Header\{DictionaryPageHeader, Type};
use Flow\Parquet\ParquetFile\Page\PageHeader;
use Flow\Parquet\ParquetFile\RowGroupBuilder\PageContainer;
use Flow\Parquet\ParquetFile\Schema\FlatColumn;
use Flow\Parquet\ParquetFile\{Codec, Compressions, Encodings, RowGroupBuilder\ColumnData\FlatColumnValues};
use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TMemoryBuffer;

final readonly class DictionaryPageBuilder
{
    public function __construct(
        private Compressions $compression,
        private Options $options,
    ) {
    }

    public function build(FlatColumn $column, FlatColumnValues $data) : PageContainer
    {
        $dictionary = (new DictionaryBuilder())->build($column, $data);

        $pageBuffer = '';
        $pageWriter = new BinaryBufferWriter($pageBuffer);
        (new PlainValuesPacker($pageWriter))->packValues($column, $dictionary->dictionary);

        $compressedBuffer = (new Codec($this->options))->compress($pageBuffer, $this->compression);

        $pageHeader = new PageHeader(
            Type::DICTIONARY_PAGE,
            \strlen($compressedBuffer),
            \strlen($pageBuffer),
            dataPageHeader: null,
            dataPageHeaderV2: null,
            dictionaryPageHeader: new DictionaryPageHeader(
                Encodings::PLAIN_DICTIONARY,
                \count($dictionary->dictionary)
            ),
        );
        $pageHeader->toThrift()->write(new TCompactProtocol($pageHeaderBuffer = new TMemoryBuffer()));

        return new PageContainer(
            $pageHeaderBuffer->getBuffer(),
            $compressedBuffer,
            $dictionary->indices,
            $dictionary->dictionary,
            $pageHeader
        );
    }
}
