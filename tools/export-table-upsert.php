<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$table = $argv[1] ?? '';
if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
    fwrite(STDERR, "Usage: php tools/export-table-upsert.php TABLE [COLUMN VALUE]\n");
    exit(1);
}
$filterColumn = $argv[2] ?? null;
$filterValue = $argv[3] ?? null;
if (($filterColumn === null) !== ($filterValue === null)) {
    fwrite(STDERR, "Both filter COLUMN and VALUE must be supplied.\n");
    exit(1);
}

require dirname(__DIR__) . '/../private/dbcon.php';
if (empty($DB_OK) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Database connection unavailable: " . ($DB_ERROR ?? 'unknown error') . "\n");
    exit(1);
}

$quotedTable = '`' . str_replace('`', '``', $table) . '`';
$columns = $pdo->query("SHOW COLUMNS FROM {$quotedTable}")->fetchAll(PDO::FETCH_ASSOC);
if ($columns === []) {
    fwrite(STDERR, "Table not found or contains no columns: {$table}\n");
    exit(1);
}

$names = array_column($columns, 'Field');
if ($filterColumn !== null && !in_array($filterColumn, $names, true)) {
    fwrite(STDERR, "Unknown filter column {$filterColumn} on table {$table}.\n");
    exit(1);
}
$primary = [];
foreach ($pdo->query("SHOW INDEX FROM {$quotedTable} WHERE Key_name = 'PRIMARY'") as $index) {
    $primary[] = $index['Column_name'];
}
if ($primary === []) {
    fwrite(STDERR, "Table has no primary key: {$table}\n");
    exit(1);
}

$quotedNames = array_map(static fn(string $name): string => '`' . str_replace('`', '``', $name) . '`', $names);
$updateNames = array_values(array_diff($names, $primary));
$order = implode(', ', array_map(static fn(string $name): string => '`' . str_replace('`', '``', $name) . '`', $primary));
$selectSql = "SELECT * FROM {$quotedTable}";
if ($filterColumn !== null) {
    $quotedFilter = '`' . str_replace('`', '``', $filterColumn) . '`';
    $selectSql .= " WHERE {$quotedFilter} = :filter_value";
}
$selectSql .= " ORDER BY {$order}";
$select = $pdo->prepare($selectSql);
$select->execute($filterColumn === null ? [] : ['filter_value' => $filterValue]);
$rows = $select->fetchAll(PDO::FETCH_ASSOC);

$sqlValue = static function ($value) use ($pdo): string {
    return $value === null ? 'NULL' : $pdo->quote((string) $value);
};

echo "-- Data-only upsert generated from the development `{$table}` table.\n";
if ($filterColumn !== null) {
    echo "-- Filter: `{$filterColumn}` = " . $pdo->quote((string) $filterValue) . ".\n";
}
echo "-- Existing matching rows are updated; missing rows are inserted; no rows are deleted.\n";
echo "SET NAMES utf8mb4;\nSTART TRANSACTION;\n\n";

if ($rows !== []) {
    echo "INSERT INTO {$quotedTable} (" . implode(', ', $quotedNames) . ") VALUES\n";
    $values = [];
    foreach ($rows as $row) {
        $values[] = '  (' . implode(', ', array_map(static fn(string $name): string => $sqlValue($row[$name]), $names)) . ')';
    }
    echo implode(",\n", $values) . "\n";
    echo "ON DUPLICATE KEY UPDATE\n  ";
    echo implode(",\n  ", array_map(static function (string $name): string {
        $quoted = '`' . str_replace('`', '``', $name) . '`';
        return "{$quoted} = VALUES({$quoted})";
    }, $updateNames));
    echo ";\n\n";
}

echo "COMMIT;\n";
