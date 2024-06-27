<?php

class InsertStatement extends Statement {
    public string $table;
    public array $columns;
    public array $values;

    public function __construct(string $table, array $columns, array $values) {
        $this->table = $table;
        $this->columns = $columns;
        $this->values = $values;
    }
}
