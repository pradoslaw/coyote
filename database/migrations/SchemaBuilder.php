<?php

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Builder;

trait SchemaBuilder
{
    protected DatabaseManager $db;
    protected Builder $schema;

    public function __construct()
    {
        $this->db = app('db');
        $this->schema = $this->db->getSchemaBuilder();
    }
}
