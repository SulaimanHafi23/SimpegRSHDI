<?php

/**
 * Generator relasi tabel berbasis source migration (offline, tanpa koneksi DB).
 * Output utama: docs/RELASI-ANTAR-TABEL.txt
 */

$migrationFiles = glob(__DIR__ . '/../database/migrations/*.php');
sort($migrationFiles);

$tables = [];
$foreignKeys = [];
$nullableCols = [];
$uniqueCols = [];

/** @var array<string, string> $colTypeMap */
$colTypeMap = [];

function keyForFk(string $childTable, string $childColumn): string
{
    return $childTable . '::' . $childColumn;
}

function deriveParentFromColumn(string $column): string
{
    if (str_ends_with($column, '_id')) {
        $base = substr($column, 0, -3);
        return $base . 's';
    }

    return $column . 's';
}

function parseRulesFromChain(string $chain): array
{
    $updateRule = '-';
    $deleteRule = '-';

    if (preg_match("/->onUpdate\('([^']+)'\)/", $chain, $m)) {
        $updateRule = strtoupper($m[1]);
    } elseif (str_contains($chain, '->cascadeOnUpdate()')) {
        $updateRule = 'CASCADE';
    } elseif (str_contains($chain, '->restrictOnUpdate()')) {
        $updateRule = 'RESTRICT';
    } elseif (str_contains($chain, '->nullOnUpdate()')) {
        $updateRule = 'SET NULL';
    }

    if (preg_match("/->onDelete\('([^']+)'\)/", $chain, $m)) {
        $deleteRule = strtoupper($m[1]);
    } elseif (str_contains($chain, '->cascadeOnDelete()')) {
        $deleteRule = 'CASCADE';
    } elseif (str_contains($chain, '->restrictOnDelete()')) {
        $deleteRule = 'RESTRICT';
    } elseif (str_contains($chain, '->nullOnDelete()')) {
        $deleteRule = 'SET NULL';
    }

    return [$updateRule, $deleteRule];
}

function setColumnFlag(array &$set, string $tableName, string $columnName): void
{
    $set[$tableName . '::' . $columnName] = true;
}

function hasColumnFlag(array $set, string $tableName, string $columnName): bool
{
    return isset($set[$tableName . '::' . $columnName]);
}

function collectColumnMeta(string $tableName, string $block, array &$nullableCols, array &$uniqueCols): void
{
    $statements = explode(';', $block);
    foreach ($statements as $stmtRaw) {
        $stmt = trim($stmtRaw);
        if ($stmt === '' || !str_contains($stmt, '$table->')) {
            continue;
        }

        if (preg_match('/\$table->[A-Za-z_][A-Za-z0-9_]*\(\s*\'([^\']+)\'/', $stmt, $mCol)) {
            $col = $mCol[1];
            if (str_contains($stmt, '->nullable()')) {
                setColumnFlag($nullableCols, $tableName, $col);
            }
            if (str_contains($stmt, '->unique()')) {
                setColumnFlag($uniqueCols, $tableName, $col);
            }
        }

        if (preg_match('/\$table->unique\(\s*\'([^\']+)\'/', $stmt, $mUniqueSingle)) {
            setColumnFlag($uniqueCols, $tableName, $mUniqueSingle[1]);
        }

        if (preg_match('/\$table->unique\(\s*\[\s*\'([^\']+)\'\s*\]\s*/', $stmt, $mUniqueArrSingle)) {
            setColumnFlag($uniqueCols, $tableName, $mUniqueArrSingle[1]);
        }
    }
}

function extractMethodBody(string $content, string $methodName): ?string
{
    $needle = 'public function ' . $methodName . '(): void';
    $start = strpos($content, $needle);
    if ($start === false) {
        return null;
    }

    $braceStart = strpos($content, '{', $start);
    if ($braceStart === false) {
        return null;
    }

    $depth = 0;
    $len = strlen($content);
    for ($i = $braceStart; $i < $len; $i++) {
        $ch = $content[$i];
        if ($ch === '{') {
            $depth++;
        } elseif ($ch === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($content, $braceStart + 1, $i - $braceStart - 1);
            }
        }
    }

    return null;
}

foreach ($migrationFiles as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $upContent = extractMethodBody($content, 'up');
    if ($upContent === null) {
        continue;
    }

    // Schema::create('table', function (...) { ... });
    if (preg_match_all('/Schema::create\(\s*\'([^\']+)\'\s*,\s*function\s*\(Blueprint \$table\)\s*\{(.*?)\}\s*\);/s', $upContent, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $tableName = $match[1];
            $block = $match[2];
            $tables[$tableName] = true;

            collectColumnMeta($tableName, $block, $nullableCols, $uniqueCols);

            // Track kolom FK type untuk inferensi ringan
            if (preg_match_all("/\$table->(foreignUuid|foreignId)\('([^']+)'\)/", $block, $fkColMatches, PREG_SET_ORDER)) {
                foreach ($fkColMatches as $fkCol) {
                    $colTypeMap[$tableName . '::' . $fkCol[2]] = $fkCol[1];
                }
            }

            // Pattern A: foreignUuid/foreignId(...)->constrained(...)
            if (preg_match_all('/\$table->foreign(?:Uuid|Id)\(\'([^\']+)\'\)([^;]*);/', $block, $fkMatches, PREG_SET_ORDER)) {
                foreach ($fkMatches as $fk) {
                    $childColumn = $fk[1];
                    $chain = $fk[2];
                    $parentTable = null;
                    $parentColumn = 'id';

                    if (preg_match("/->constrained\('([^']+)'\)/", $chain, $mParent)) {
                        $parentTable = $mParent[1];
                    } elseif (str_contains($chain, '->constrained()')) {
                        $parentTable = deriveParentFromColumn($childColumn);
                    }

                    if ($parentTable === null) {
                        continue;
                    }

                    [$updateRule, $deleteRule] = parseRulesFromChain($chain);

                    $foreignKeys[keyForFk($tableName, $childColumn)] = [
                        'child_table' => $tableName,
                        'child_column' => $childColumn,
                        'parent_table' => $parentTable,
                        'parent_column' => $parentColumn,
                        'nullable' => hasColumnFlag($nullableCols, $tableName, $childColumn),
                        'is_unique' => hasColumnFlag($uniqueCols, $tableName, $childColumn),
                        'update_rule' => $updateRule,
                        'delete_rule' => $deleteRule,
                    ];
                }
            }

            // Pattern B: foreign('x')->references('id')->on('table')
            if (preg_match_all('/\$table->foreign\(\'([^\']+)\'\)\s*->references\(\'([^\']+)\'\)\s*->on\(\'([^\']+)\'\)([^;]*);/', $block, $fk2Matches, PREG_SET_ORDER)) {
                foreach ($fk2Matches as $fk2) {
                    $childColumn = $fk2[1];
                    $parentColumn = $fk2[2];
                    $parentTable = $fk2[3];
                    $chain = $fk2[4];
                    [$updateRule, $deleteRule] = parseRulesFromChain($chain);

                    $foreignKeys[keyForFk($tableName, $childColumn)] = [
                        'child_table' => $tableName,
                        'child_column' => $childColumn,
                        'parent_table' => $parentTable,
                        'parent_column' => $parentColumn,
                        'nullable' => hasColumnFlag($nullableCols, $tableName, $childColumn),
                        'is_unique' => hasColumnFlag($uniqueCols, $tableName, $childColumn),
                        'update_rule' => $updateRule,
                        'delete_rule' => $deleteRule,
                    ];
                }
            }
        }
    }

    // Schema::dropIfExists('table')
    if (preg_match_all("/Schema::dropIfExists\(\s*'([^']+)'\s*\)/", $upContent, $drops, PREG_SET_ORDER)) {
        foreach ($drops as $drop) {
            $dropTable = $drop[1];
            unset($tables[$dropTable]);
            foreach ($foreignKeys as $key => $fk) {
                if ($fk['child_table'] === $dropTable || $fk['parent_table'] === $dropTable) {
                    unset($foreignKeys[$key]);
                }
            }
        }
    }

    // Schema::table('table', function (...) { ... }); (handle add/drop FK penting)
    if (preg_match_all('/Schema::table\(\s*\'([^\']+)\'\s*,\s*function\s*\(Blueprint \$table\)\s*\{(.*?)\}\s*\);/s', $upContent, $tableOps, PREG_SET_ORDER)) {
        foreach ($tableOps as $op) {
            $tableName = $op[1];
            $block = $op[2];
            $tables[$tableName] = true;

            collectColumnMeta($tableName, $block, $nullableCols, $uniqueCols);

            // Drop by dropConstrainedForeignId('col')
            if (preg_match_all('/\$table->dropConstrainedForeignId\(\'([^\']+)\'\)/', $block, $dropFkCols, PREG_SET_ORDER)) {
                foreach ($dropFkCols as $dropFk) {
                    unset($foreignKeys[keyForFk($tableName, $dropFk[1])]);
                }
            }

            // Drop by dropForeign(['col'])
            if (preg_match_all('/\$table->dropForeign\(\[\s*\'([^\']+)\'\s*\]\)/', $block, $dropForeignArr, PREG_SET_ORDER)) {
                foreach ($dropForeignArr as $dropFk) {
                    unset($foreignKeys[keyForFk($tableName, $dropFk[1])]);
                }
            }

            // Drop by dropColumn('col')
            if (preg_match_all('/\$table->dropColumn\(\'([^\']+)\'\)/', $block, $dropCols, PREG_SET_ORDER)) {
                foreach ($dropCols as $dropCol) {
                    unset($foreignKeys[keyForFk($tableName, $dropCol[1])]);
                }
            }

            // Add by foreignUuid/foreignId + constrained in Schema::table
            if (preg_match_all('/\$table->foreign(?:Uuid|Id)\(\'([^\']+)\'\)([^;]*);/', $block, $addFkCols, PREG_SET_ORDER)) {
                foreach ($addFkCols as $fk) {
                    $childColumn = $fk[1];
                    $chain = $fk[2];
                    $parentTable = null;
                    $parentColumn = 'id';

                    if (preg_match("/->constrained\('([^']+)'\)/", $chain, $mParent)) {
                        $parentTable = $mParent[1];
                    } elseif (str_contains($chain, '->constrained()')) {
                        $parentTable = deriveParentFromColumn($childColumn);
                    }

                    if ($parentTable !== null) {
                        [$updateRule, $deleteRule] = parseRulesFromChain($chain);
                        $foreignKeys[keyForFk($tableName, $childColumn)] = [
                            'child_table' => $tableName,
                            'child_column' => $childColumn,
                            'parent_table' => $parentTable,
                            'parent_column' => $parentColumn,
                            'nullable' => hasColumnFlag($nullableCols, $tableName, $childColumn),
                            'is_unique' => hasColumnFlag($uniqueCols, $tableName, $childColumn),
                            'update_rule' => $updateRule,
                            'delete_rule' => $deleteRule,
                        ];
                    }
                }
            }
        }
    }
}

$tableNames = array_keys($tables);
sort($tableNames);

$incoming = [];
$outgoing = [];
$incomingForHierarchy = [];
foreach ($tableNames as $tableName) {
    $incoming[$tableName] = 0;
    $outgoing[$tableName] = 0;
    $incomingForHierarchy[$tableName] = 0;
}

$edges = [];
foreach ($foreignKeys as $fk) {
    $child = $fk['child_table'];
    $parent = $fk['parent_table'];

    if (!isset($incoming[$child])) {
        $incoming[$child] = 0;
        $outgoing[$child] = 0;
        $tableNames[] = $child;
    }
    if (!isset($incoming[$parent])) {
        $incoming[$parent] = 0;
        $outgoing[$parent] = 0;
        $tableNames[] = $parent;
    }

    $incoming[$child]++;
    $outgoing[$parent]++;

    // Untuk hierarki, abaikan self reference agar tabel induk tetap bisa jadi root.
    if ($parent !== $child) {
        $incomingForHierarchy[$child] = ($incomingForHierarchy[$child] ?? 0) + 1;
        $edges[$parent][] = $child;
    }
}

$tableNames = array_values(array_unique($tableNames));
sort($tableNames);

// Hierarki BFS dari root (tidak punya incoming FK)
$level = [];
$queue = [];
foreach ($tableNames as $tableName) {
    if (($incomingForHierarchy[$tableName] ?? 0) === 0) {
        $level[$tableName] = 0;
        $queue[] = $tableName;
    }
}

while (!empty($queue)) {
    $current = array_shift($queue);
    $nextLevel = $level[$current] + 1;
    foreach (($edges[$current] ?? []) as $child) {
        if (!isset($level[$child]) || $nextLevel > $level[$child]) {
            $level[$child] = $nextLevel;
            $queue[] = $child;
        }
    }
}

foreach ($tableNames as $tableName) {
    if (!isset($level[$tableName])) {
        $level[$tableName] = 0;
    }
}

// Sort FK untuk output stabil
$fkList = array_values($foreignKeys);
usort($fkList, function ($a, $b) {
    $cmp = strcmp($a['child_table'], $b['child_table']);
    if ($cmp !== 0) {
        return $cmp;
    }
    return strcmp($a['child_column'], $b['child_column']);
});

$lines = [];
$lines[] = 'RELASI ANTAR TABEL UNTUK ERD';
$lines[] = 'Tanggal generate: ' . date('Y-m-d H:i:s');
$lines[] = 'Total tabel: ' . count($tableNames);
$lines[] = 'Total relasi FK: ' . count($fkList);
$lines[] = '';
$lines[] = 'Format: Tabel_Asal.fk -> Tabel_Tujuan.pk | Kardinalitas | Penjelasan';
$lines[] = '';

$fkCountByChild = [];
foreach ($fkList as $fk) {
    $child = $fk['child_table'];
    $fkCountByChild[$child] = ($fkCountByChild[$child] ?? 0) + 1;
}

function detectCardinality(array $fk): string
{
    $nullable = (bool) ($fk['nullable'] ?? false);
    $isUnique = (bool) ($fk['is_unique'] ?? false);

    if ($isUnique && !$nullable) {
        return '1:1';
    }
    if ($isUnique && $nullable) {
        return '0..1:1';
    }
    if (!$isUnique && !$nullable) {
        return 'N:1';
    }

    return '0..N:1';
}

function detectRelationLabel(array $fk, array $fkCountByChild): string
{
    $child = $fk['child_table'];
    $parent = $fk['parent_table'];
    $col = $fk['child_column'];
    $deleteRule = strtoupper((string) ($fk['delete_rule'] ?? '-'));

    if ($child === $parent) {
        return 'hirarki';
    }

    if (str_contains($child, 'audit') || str_contains($child, 'log')) {
        return 'audit';
    }

    if (str_contains($child, 'history')) {
        return 'riwayat';
    }

    $actorCols = ['approved_by', 'verified_by', 'created_by', 'changed_by', 'executed_by', 'manager_id'];
    if (in_array($col, $actorCols, true)) {
        return 'aktor';
    }

    if (($fkCountByChild[$child] ?? 0) === 2 && substr_count($child, '_') >= 2 && $deleteRule === 'CASCADE') {
        return 'pivot';
    }

    if ($deleteRule === 'SET NULL') {
        return 'opsional';
    }

    return 'referensi';
}

$no = 1;
foreach ($fkList as $fk) {
    $child = $fk['child_table'];
    $parent = $fk['parent_table'];
    $fkCol = $fk['child_column'];
    $parentCol = $fk['parent_column'];

    $arah = $child . '.' . $fkCol . ' -> ' . $parent . '.' . $parentCol;
    $kardinalitas = detectCardinality($fk) . ' (dari ' . $child . ' ke ' . $parent . ')';
    $penjelasan = detectRelationLabel($fk, $fkCountByChild);

    $lines[] = $no++ . '. ' . $arah . ' | ' . $kardinalitas . ' | ' . $penjelasan;
}

$lines[] = '';
$lines[] = 'HIRARKI RINGKAS TABEL (opsional untuk layout ERD)';
$lines[] = 'Format: Level X: tabel1, tabel2, ...';

$grouped = [];
foreach ($tableNames as $tableName) {
    $grouped[$level[$tableName]][] = $tableName;
}
ksort($grouped);

foreach ($grouped as $lvl => $tablesAtLevel) {
    sort($tablesAtLevel);
    $lines[] = 'Level ' . $lvl . ': ' . implode(', ', $tablesAtLevel);
}

$txtPath = __DIR__ . '/../docs/RELASI-ANTAR-TABEL.txt';
file_put_contents($txtPath, implode(PHP_EOL, $lines) . PHP_EOL);

echo "Generated docs/RELASI-ANTAR-TABEL.txt\n";
