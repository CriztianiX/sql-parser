<?php

class DeleteStatement extends Statement {
    public string $table;
    public array $where;

    public function __construct(string $table, array $where) {
        $this->table = $table;
        $this->where = $where;
    }
}
