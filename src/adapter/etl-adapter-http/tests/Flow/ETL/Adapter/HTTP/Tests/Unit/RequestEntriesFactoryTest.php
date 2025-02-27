<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\HTTP\Tests\Unit;

use Flow\ETL\Adapter\Http\RequestEntriesFactory;
use Flow\ETL\Row\Entry\{JsonEntry, StringEntry};
use Flow\ETL\Tests\FlowTestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\RequestInterface;

final class RequestEntriesFactoryTest extends FlowTestCase
{
    public static function requests() : \Generator
    {
        $messageFactory = new Psr17Factory();
        $request = $messageFactory
            ->createRequest('POST', 'https://flow-php.io/example')
            ->withBody($messageFactory->createStream(\json_encode(['status' => 'success'])));

        yield 'uses StringEntry for request body when neither Accept and Content-Type header is present' => [
            StringEntry::class,
            $request,
        ];

        yield 'uses JsonEntry for request body when Content-Type header is application/json' => [
            JsonEntry::class,
            $request
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Accept', 'application/xml'),
        ];

        yield 'uses JsonEntry for request body when Accept header is application/json' => [
            JsonEntry::class,
            $request->withHeader('Accept', 'application/json'),
        ];

        yield 'uses NullEntry for request body when when request body is empty' => [
            StringEntry::class,
            $messageFactory
                ->createRequest('POST', 'https://flow-php.io/example')
                ->withHeader('Content-Type', 'application/json'),
        ];
    }

    #[DataProvider('requests')]
    public function test_uses_expected_entry_for_request_body(string $expectedRequestBodyEntryClass, RequestInterface $request) : void
    {
        $entryFactory = new RequestEntriesFactory();

        self::assertInstanceOf(
            $expectedRequestBodyEntryClass,
            $entryFactory->create($request)->get('request_body')
        );
    }
}
