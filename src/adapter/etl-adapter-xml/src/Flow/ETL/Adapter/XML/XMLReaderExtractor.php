<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\XML;

use function Flow\ETL\DSL\array_to_rows;
use Flow\ETL\Extractor\{FileExtractor, Limitable, LimitableExtractor, PathFiltering, Signal};
use Flow\ETL\{Exception\InvalidArgumentException, Extractor, FlowContext};
use Flow\Filesystem\Path;

final class XMLReaderExtractor implements Extractor, FileExtractor, LimitableExtractor
{
    use Limitable;
    use PathFiltering;

    /**
     * @deprecated Use XMLParserExtractor instead, XMLReaderExtractor can't properly handle reading remote files since it requires a local file.
     *
     * In order to iterate only over <element> nodes us root/elements/element.
     *
     * <root>
     *   <elements>
     *     <element></element>
     *     <element></element>
     *   <elements>
     * </root>
     *
     * $xmlNodePath does not support attributes and it's not xpath, it is just a sequence
     * of node names separated with slash.
     *
     * @param string $xmlNodePath
     */
    public function __construct(
        private readonly Path $path,
        private readonly string $xmlNodePath = '',
    ) {
        if (!$this->path->isLocal()) {
            throw new InvalidArgumentException('XMLReaderExtractor supports only local files, please use XMLParserExtractor that depends on php-xml extension.');
        }
        $this->resetLimit();
    }

    public function extract(FlowContext $context) : \Generator
    {
        $shouldPutInputIntoRows = $context->config->shouldPutInputIntoRows();

        foreach ($context->streams()->list($this->path, $this->filter()) as $stream) {
            $xmlReader = new \XMLReader();
            $xmlReader->open($stream->path()->path());

            $previousDepth = 0;
            $currentPathBreadCrumbs = [];

            while ($xmlReader->read()) {
                if ($xmlReader->nodeType === \XMLReader::ELEMENT) {
                    if ($previousDepth === $xmlReader->depth) {
                        \array_pop($currentPathBreadCrumbs);
                        $currentPathBreadCrumbs[] = $xmlReader->name;
                    }

                    if ($xmlReader->depth > $previousDepth) {
                        $currentPathBreadCrumbs[] = $xmlReader->name;
                    }

                    while ($xmlReader->depth < $previousDepth) {
                        \array_pop($currentPathBreadCrumbs);
                        $previousDepth--;
                    }

                    $currentPath = \implode('/', $currentPathBreadCrumbs);

                    if ($currentPath === $this->xmlNodePath || ($this->xmlNodePath === '' && $xmlReader->depth === 0)) {
                        $dom = new \DOMDocument('1.0', '');
                        $node = $xmlReader->expand($dom);

                        if ($shouldPutInputIntoRows) {
                            $rowData = [
                                'node' => $node,
                                '_input_file_uri' => $stream->path()->uri(),
                            ];
                        } else {
                            $rowData = ['node' => $node];
                        }

                        $signal = yield array_to_rows($rowData, $context->entryFactory(), $stream->path()->partitions());

                        $this->incrementReturnedRows();

                        if ($signal === Signal::STOP || $this->reachedLimit()) {
                            $xmlReader->close();
                            $context->streams()->closeStreams($this->path);

                            return;
                        }
                    }

                    $previousDepth = $xmlReader->depth;
                }
            }

            $xmlReader->close();
        }
    }

    public function source() : Path
    {
        return $this->path;
    }
}
