<?php

class JoinClause {
    public string $type;
    public string $table;
    public array $condition;

    public function __construct(string $type, string $table, array $condition) {
        $this->type = $type;
        $this->table = $table;
        $this->condition = $condition;
    }
}
