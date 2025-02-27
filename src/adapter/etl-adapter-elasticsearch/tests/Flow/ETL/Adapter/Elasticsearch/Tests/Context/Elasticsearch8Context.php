<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\Elasticsearch\Tests\Context;

use function Flow\ETL\Adapter\Elasticsearch\to_es_bulk_index;
use function Flow\ETL\DSL\{config, flow_context};
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\{Client, ClientBuilder};
use Flow\ETL\Adapter\Elasticsearch\IdFactory;
use Flow\ETL\{Rows};

final class Elasticsearch8Context implements ElasticsearchContext
{
    private ?Client $client = null;

    public function __construct(private readonly array $hosts)
    {
    }

    public function client() : Client
    {
        if ($this->client === null) {
            $this->client = ClientBuilder::fromConfig($this->clientConfig());
        }

        return $this->client;
    }

    public function clientConfig() : array
    {
        return [
            'hosts' => $this->hosts,
        ];
    }

    public function createIndex(string $name) : void
    {
        try {
            $params = [
                'index' => $name,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 2,
                        'number_of_replicas' => 0,
                    ],
                ],
            ];

            $response = $this->client()->indices()->create($params);
        } catch (ClientResponseException) {
        }
    }

    public function deleteIndex(string $name) : void
    {
        try {
            $deleteParams = [
                'index' => $name,
            ];
            $response = $this->client()->indices()->delete($deleteParams);
        } catch (ClientResponseException) {
        }
    }

    public function loadRows(Rows $rows, string $index, IdFactory $idFactory) : void
    {
        to_es_bulk_index(
            $this->clientConfig(),
            $index,
            $idFactory,
            ['refresh' => true]
        )
            ->load($rows, flow_context(config()));
    }

    public function version() : int
    {
        return 8;
    }
}
