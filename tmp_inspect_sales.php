<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
foreach ($pdo->query('SELECT count(*) as c, sum(total_amount) as s FROM sales') as $row) {
    echo 'sales:' . $row['c'] . '|' . $row['s'] . PHP_EOL;
}
foreach ($pdo->query('SELECT count(*) as c, sum(quantity * unit_price) as s FROM sale_items') as $row) {
    echo 'items:' . $row['c'] . '|' . $row['s'] . PHP_EOL;
}
foreach ($pdo->query('SELECT id,total_amount,created_at FROM sales LIMIT 5') as $row) {
    echo $row['id'] . '|' . $row['total_amount'] . '|' . $row['created_at'] . PHP_EOL;
}
