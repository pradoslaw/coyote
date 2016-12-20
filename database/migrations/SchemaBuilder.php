<?php

trait SchemaBuilder
{
    /**
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $schema;

    /**
     * @var \Illuminate\Database\Connection
     */
    protected $db;

    public function __construct()
    {
        $this->db = app('db');
        $this->schema = $this->db->getSchemaBuilder();
    }
}
