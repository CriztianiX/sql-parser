<?php

function runTests() {
    $testQueries = [
        "SELECT u.name, u.age, o.order_id FROM users u INNER JOIN orders o ON u.id = o.user_id WHERE u.age > 21 GROUP BY u.age ORDER BY u.name LIMIT 10",
        "INSERT INTO users (name, age) VALUES ('John', 25)",
        "DELETE FROM users WHERE age < 18",
        "SELECT * FROM products LIMIT 5"
    ];

    foreach ($testQueries as $query) {
        echo "Testing query: $query\n";

        $lexer = new Lexer($query);
        $tokens = $lexer->tokenize();

        $parser = new Parser($tokens);
        $parsedQuery = $parser->parse();

        print_r($parsedQuery);
        echo "\n\n";
    }
}

runTests();
