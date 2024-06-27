<?php

class SelectStatement extends Statement {
    public string $type = 'SELECT';
    public array $columns;
    public string $table;
    public array $joins;
    public array $where;
    public array $groupBy;
    public array $orderBy;
    public ?int $limit;

    public function __construct(array $columns, string $table, array $joins, array $where, array $groupBy, array $orderBy, ?int $limit = null) {
        $this->columns = $columns;
        $this->table = $table;
        $this->joins = $joins;
        $this->where = $where;
        $this->groupBy = $groupBy;
        $this->orderBy = $orderBy;
        $this->limit = $limit;
    }
}
