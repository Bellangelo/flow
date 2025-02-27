<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\HTTP\Tests\Integration;

use function Flow\ETL\DSL\{config, flow_context};
use Flow\ETL\Adapter\Http\DynamicExtractor\NextRequestFactory;
use Flow\ETL\Adapter\Http\PsrHttpClientDynamicExtractor;
use Flow\ETL\{Tests\FlowTestCase};
use Http\Mock\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

final class PsrHttpClientDynamicExtractorTest extends FlowTestCase
{
    public function test_http_extractor() : void
    {
        $psr17Factory = new Psr17Factory();
        $psr18Client = new Client($psr17Factory);

        $psr18Client->addResponse(
            new Response(200, [
                'Server' => 'GitHub.com',
            ], \file_get_contents(__DIR__ . '/../Fixtures/flow-php.json')),
        );

        $extractor = new PsrHttpClientDynamicExtractor($psr18Client, new class implements NextRequestFactory {
            public function create(?ResponseInterface $previousResponse = null) : ?RequestInterface
            {
                $psr17Factory = new Psr17Factory();

                if ($previousResponse === null) {
                    return $psr17Factory
                        ->createRequest('GET', 'https://api.github.com/orgs/flow-php')
                        ->withHeader('Accept', 'application/vnd.github.v3+json')
                        ->withHeader('User-Agent', 'flow-php/etl');
                }

                return null;
            }
        });

        $rows = $extractor->extract(flow_context(config()));

        $body = \json_decode((string) $rows->current()->first()->valueOf('response_body'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(1, $rows->current()->count());
        self::assertSame('flow-php', $body['login'], \json_encode($body, JSON_THROW_ON_ERROR));
        self::assertSame(73_495_297, $body['id'], \json_encode($body, JSON_THROW_ON_ERROR));
        self::assertSame(['GitHub.com'], $rows->current()->first()->valueOf('response_headers')['Server']);
        self::assertSame(200, $rows->current()->first()->valueOf('response_status_code'));
        self::assertSame('1.1', $rows->current()->first()->valueOf('response_protocol_version'));
        self::assertSame('OK', $rows->current()->first()->valueOf('response_reason_phrase'));
        self::assertSame('https://api.github.com/orgs/flow-php', $rows->current()->first()->valueOf('request_uri'));
        self::assertSame('GET', $rows->current()->first()->valueOf('request_method'));
    }
}
