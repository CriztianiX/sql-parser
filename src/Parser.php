<?php

class Parser {
    private array $tokens;
    private int $position;

    public function __construct(array $tokens) {
        $this->tokens = $tokens;
        $this->position = 0;
    }

    private function expect(string $expected): bool {
        if ($this->position < count($this->tokens) && $this->tokens[$this->position]->type === $expected) {
            $this->position++;
            return true;
        }
        return false;
    }

    public function parse(): Statement {
        if ($this->expect(TokenType::SELECT)) {
            return $this->parseSelect();
        } elseif ($this->expect(TokenType::INSERT)) {
            return $this->parseInsert();
        } elseif ($this->expect(TokenType::DELETE)) {
            return $this->parseDelete();
        } else {
            throw new Exception('Unsupported SQL statement');
        }
    }

    private function parseColumns(): array {
        $columns = [];
        while ($this->position < count($this->tokens) && $this->tokens[$this->position]->type !== TokenType::FROM) {
            $columns[] = $this->tokens[$this->position++]->value;
        }
        return $columns;
    }

    private function parseTable(): string {
        return $this->tokens[$this->position++]->value;
    }

    private function parseWhere(): array {
        $whereClause = [];
        if ($this->expect(TokenType::WHERE)) {
            while ($this->position < count($this->tokens) && !in_array($this->tokens[$this->position]->type, [TokenType::GROUP, TokenType::ORDER, TokenType::LIMIT])) {
                $whereClause[] = $this->tokens[$this->position++]->value;
            }
        }
        return $whereClause;
    }

    private function parseGroupBy(): array {
        $groupBy = [];
        if ($this->expect(TokenType::GROUP)) {
            if (!$this->expect(TokenType::BY)) {
                throw new Exception('Expected BY');
            }
            while ($this->position < count($this->tokens) && !in_array($this->tokens[$this->position]->type, [TokenType::ORDER, TokenType::LIMIT])) {
                $groupBy[] = $this->tokens[$this->position++]->value;
            }
        }
        return $groupBy;
    }

    private function parseOrderBy(): array {
        $orderBy = [];
        if ($this->expect(TokenType::ORDER)) {
            if (!$this->expect(TokenType::BY)) {
                throw new Exception('Expected BY');
            }
            while ($this->position < count($this->tokens) && $this->tokens[$this->position]->type !== TokenType::LIMIT) {
                $orderBy[] = $this->tokens[$this->position++]->value;
            }
        }
        return $orderBy;
    }

    private function parseLimit(): ?int {
        if ($this->expect(TokenType::LIMIT)) {
            return (int) $this->tokens[$this->position++]->value;
        }
        return null;
    }

    private function parseJoin(): JoinClause {
        $type = $this->tokens[$this->position++]->value;

        if (!in_array($type, ['INNER', 'LEFT', 'RIGHT', 'FULL'])) {
            throw new Exception('Expected JOIN type');
        }

        if (!$this->expect(TokenType::JOIN)) {
            throw new Exception('Expected JOIN');
        }

        $table = $this->tokens[$this->position++]->value;

        if (!$this->expect(TokenType::ON)) {
            throw new Exception('Expected ON');
        }

        $condition = [];
        while ($this->position < count($this->tokens) && !in_array($this->tokens[$this->position]->type, [TokenType::INNER, TokenType::LEFT, TokenType::RIGHT, TokenType::FULL, TokenType::WHERE, TokenType::GROUP, TokenType::ORDER, TokenType::LIMIT])) {
            $condition[] = $this->tokens[$this->position++]->value;
        }

        return new JoinClause($type, $table, $condition);
    }

    private function parseJoins(): array {
        $joins = [];
        while ($this->position < count($this->tokens) && in_array($this->tokens[$this->position]->type, [TokenType::INNER, TokenType::LEFT, TokenType::RIGHT, TokenType::FULL])) {
            $joins[] = $this->parseJoin();
        }
        return $joins;
    }

    private function parseSelect(): SelectStatement {
        $columns = $this->parseColumns();
        if (!$this->expect(TokenType::FROM)) {
            throw new Exception('Expected FROM');
        }
        $table = $this->parseTable();
        $joins = $this->parseJoins();
        $whereClause = $this->parseWhere();
        $groupBy = $this->parseGroupBy();
        $orderBy = $this->parseOrderBy();
        $limit = $this->parseLimit();

        return new SelectStatement($columns, $table, $joins, $whereClause, $groupBy, $orderBy, $limit);
    }

    private function parseInsert(): InsertStatement {
        if (!$this->expect(TokenType::INTO)) {
            throw new Exception('Expected INTO');
        }

        $table = $this->parseTable();

        if (!$this->expect(TokenType::OPEN_PAREN)) {
            throw new Exception('Expected (');
        }

        $columns = [];
        while ($this->position < count($this->tokens) && $this->tokens[$this->position]->type !== TokenType::CLOSE_PAREN) {
            if ($this->tokens[$this->position]->type !== TokenType::COMMA) {
                $columns[] = $this->tokens[$this->position]->value;
            }
            $this->position++;
        }

        if (!$this->expect(TokenType::CLOSE_PAREN)) {
            throw new Exception('Expected )');
        }

        if (!$this->expect(TokenType::VALUES)) {
            throw new Exception('Expected VALUES');
        }

        if (!$this->expect(TokenType::OPEN_PAREN)) {
            throw new Exception('Expected (');
        }

        $values = [];
        while ($this->position < count($this->tokens) && $this->tokens[$this->position]->type !== TokenType::CLOSE_PAREN) {
            if ($this->tokens[$this->position]->type !== TokenType::COMMA) {
                $values[] = $this->tokens[$this->position]->value;
            }
            $this->position++;
        }

        if (!$this->expect(TokenType::CLOSE_PAREN)) {
            throw new Exception('Expected )');
        }

        return new InsertStatement($table, $columns, $values);
    }

    private function parseDelete(): DeleteStatement {
        if (!$this->expect(TokenType::FROM)) {
            throw new Exception('Expected FROM');
        }

        $table = $this->parseTable();
        $whereClause = $this->parseWhere();
        return new DeleteStatement($table, $whereClause);
    }
}
