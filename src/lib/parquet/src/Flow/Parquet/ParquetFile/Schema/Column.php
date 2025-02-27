<?php

declare(strict_types=1);

namespace Flow\Parquet\ParquetFile\Schema;

use Flow\Parquet\Thrift\SchemaElement;

interface Column
{
    public function convertedType() : ?ConvertedType;

    public function ddl() : array;

    public function flatPath() : string;

    public function isList() : bool;

    public function isMap() : bool;

    public function isStruct() : bool;

    public function logicalType() : ?LogicalType;

    public function maxDefinitionsLevel() : int;

    public function maxRepetitionsLevel() : int;

    public function name() : string;

    public function parent() : ?NestedColumn;

    /**
     * @return array<string>
     */
    public function path() : array;

    public function repetition() : ?Repetition;

    public function repetitions() : Repetitions;

    public function setParent(NestedColumn $parent) : void;

    public function toThrift() : SchemaElement|array;

    public function type() : ?PhysicalType;

    public function typeLength() : ?int;
}
