<?php

class Lexer {
    private string $input;
    private int $position;
    private int $length;
    private array $tokens;

    public function __construct(string $input) {
        $this->input = $input;
        $this->position = 0;
        $this->length = strlen($input);
        $this->tokens = [];
    }

    public function tokenize(): array {
        while ($this->position < $this->length) {
            $char = $this->input[$this->position];
            if (ctype_space($char)) {
                $this->position++;
                continue;
            }

            if (ctype_alpha($char)) {
                $this->tokens[] = $this->tokenizeWord();
            } elseif (ctype_digit($char)) {
                $this->tokens[] = $this->tokenizeNumber();
            } elseif ($char === '\'') {
                $this->tokens[] = $this->tokenizeString();
            } elseif ($char === ',') {
                $this->tokens[] = new Token(TokenType::COMMA, $char);
                $this->position++;
            } elseif ($char === '.') {
                $this->tokens[] = new Token(TokenType::DOT, $char);
                $this->position++;
            } elseif ($char === '(') {
                $this->tokens[] = new Token(TokenType::OPEN_PAREN, $char);
                $this->position++;
            } elseif ($char === ')') {
                $this->tokens[] = new Token(TokenType::CLOSE_PAREN, $char);
                $this->position++;
            } elseif (preg_match('/[<>=!]/', $char)) {
                $this->tokens[] = $this->tokenizeOperator();
            } else {
                throw new Exception("Unexpected character: $char");
            }
        }

        $this->tokens[] = new Token(TokenType::EOF, '');
        return $this->tokens;
    }

    private function tokenizeWord(): Token {
        $start = $this->position;
        while ($this->position < $this->length && (ctype_alpha($this->input[$this->position]) || $this->input[$this->position] === '_')) {
            $this->position++;
        }

        $word = substr($this->input, $start, $this->position - $start);
        $type = match (strtoupper($word)) {
            'SELECT' => TokenType::SELECT,
            'INSERT' => TokenType::INSERT,
            'DELETE' => TokenType::DELETE,
            'FROM' => TokenType::FROM,
            'WHERE' => TokenType::WHERE,
            'GROUP' => TokenType::GROUP,
            'ORDER' => TokenType::ORDER,
            'BY' => TokenType::BY,
            'INTO' => TokenType::INTO,
            'VALUES' => TokenType::VALUES,
            'JOIN' => TokenType::JOIN,
            'ON' => TokenType::ON,
            'INNER' => TokenType::INNER,
            'LEFT' => TokenType::LEFT,
            'RIGHT' => TokenType::RIGHT,
            'FULL' => TokenType::FULL,
            'LIMIT' => TokenType::LIMIT,
            default => TokenType::IDENTIFIER
        };

        return new Token($type, $word);
    }

    private function tokenizeNumber(): Token {
        $start = $this->position;
        while ($this->position < $this->length && ctype_digit($this->input[$this->position])) {
            $this->position++;
        }
        return new Token(TokenType::NUMBER, substr($this->input, $start, $this->position - $start));
    }

    private function tokenizeString(): Token {
        $this->position++; // Skip opening quote
        $start = $this->position;
        while ($this->position < $this->length && $this->input[$this->position] !== '\'') {
            $this->position++;
        }
        $string = substr($this->input, $start, $this->position - $start);
        $this->position++; // Skip closing quote
        return new Token(TokenType::STRING, $string);
    }

    private function tokenizeOperator(): Token {
        $start = $this->position;
        while ($this->position < $this->length && preg_match('/[<>=!]/', $this->input[$this->position])) {
            $this->position++;
        }
        return new Token(TokenType::OPERATOR, substr($this->input, $start, $this->position - $start));
    }
}
