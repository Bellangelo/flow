<?php

declare(strict_types=1);

namespace Flow\Website\Factory\Github;

use Flow\ETL\Adapter\Http\DynamicExtractor\NextRequestFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

final class ContributorsRequestFactory implements NextRequestFactory
{
    public function __construct(
        public readonly string $githubToken,
        private readonly Psr17Factory $factory = new Psr17Factory(),
    ) {
    }

    public function create(?ResponseInterface $previousResponse = null) : ?RequestInterface
    {
        if ($previousResponse instanceof ResponseInterface) {
            return null;
        }

        return $this->factory
            ->createRequest('GET', 'https://api.github.com/repos/flow-php/flow/contributors?q=contributions&order=desc&per_page=50')
            ->withHeader('Accept', 'application/vnd.github+json')
            ->withHeader('Authorization', 'Bearer ' . $this->githubToken)
            ->withHeader('X-GitHub-Api-Version', '2022-11-28')
            ->withHeader('User-Agent', 'flow-website-fetch');
    }
}
