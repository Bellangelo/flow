<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\Elasticsearch\Tests\Integration;

use Flow\ETL\Adapter\Elasticsearch\Tests\Context\{Elasticsearch7Context, Elasticsearch8Context, ElasticsearchContext};
use Flow\ETL\Tests\FlowTestCase;

abstract class ElasticsearchTestCase extends FlowTestCase
{
    protected ElasticsearchContext $elasticsearchContext;

    protected function setUp() : void
    {
        $this->elasticsearchContext = (\class_exists("Elasticsearch\Client"))
            ? new Elasticsearch7Context([\getenv('ELASTICSEARCH_URL')])
            : new Elasticsearch8Context([\getenv('ELASTICSEARCH_URL')]);
    }
}
