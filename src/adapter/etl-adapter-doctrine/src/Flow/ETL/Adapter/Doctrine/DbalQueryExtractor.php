<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\Doctrine;

use function Flow\ETL\DSL\array_to_rows;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\{ArrayParameterType, Connection, ParameterType};
use Flow\ETL\{Extractor, FlowContext, Row\Schema};

final class DbalQueryExtractor implements Extractor
{
    private ParametersSet $parametersSet;

    private ?Schema $schema = null;

    private array $types = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly string $query,
    ) {
        $this->parametersSet = new ParametersSet([]);
    }

    /**
     * @param array<string, mixed>|list<mixed> $parameters
     * @param array<int|string, ArrayParameterType|int|ParameterType|string|Type> $types
     */
    public static function single(Connection $connection, string $query, array $parameters = [], array $types = []) : self
    {
        $extractor = (new self($connection, $query));

        if ($parameters !== []) {
            $extractor->withParameters(new ParametersSet($parameters));
        }

        if ($types !== []) {
            $extractor->withTypes($types);
        }

        return $extractor;
    }

    public function extract(FlowContext $context) : \Generator
    {
        foreach ($this->parametersSet->all() as $parameters) {
            foreach ($this->connection->fetchAllAssociative($this->query, $parameters, $this->types) as $row) {
                $signal = yield array_to_rows($row, $context->entryFactory(), [], $this->schema);

                if ($signal === Extractor\Signal::STOP) {
                    return;
                }
            }
        }
    }

    public function withParameters(ParametersSet $parametersSet) : self
    {
        $this->parametersSet = $parametersSet;

        return $this;
    }

    public function withSchema(Schema $schema) : self
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @param array<int|string, ArrayParameterType|int|ParameterType|string|Type> $types
     */
    public function withTypes(array $types) : self
    {
        $this->types = $types;

        return $this;
    }
}
