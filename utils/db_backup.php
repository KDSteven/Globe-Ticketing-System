<?php
// utils/db_backup.php
require_once __DIR__ . '/../config/db.php';

// ====== CONFIG ======
define('BACKUP_DIR', __DIR__ . '/../backups');          // create /backups
define('META_FILE', BACKUP_DIR . '/backup_meta.json');  // stores last milestone
define('BACKUP_EVERY', 50);                             // every 50 tickets

function ensure_backup_dir(): void {
    if (!is_dir(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }
    // Best-effort protection (mainly for Apache)
    $ht = BACKUP_DIR . '/.htaccess';
    if (!file_exists($ht)) {
        @file_put_contents($ht, "Deny from all\n");
    }
}

function read_meta(): array {
    ensure_backup_dir();
    if (!file_exists(META_FILE)) {
        return ['last_milestone' => 0, 'last_file' => null, 'last_time' => null];
    }
    $raw = @file_get_contents(META_FILE);
    $json = json_decode($raw, true);
    if (!is_array($json)) {
        return ['last_milestone' => 0, 'last_file' => null, 'last_time' => null];
    }
    return array_merge(['last_milestone' => 0, 'last_file' => null, 'last_time' => null], $json);
}

function write_meta(array $meta): void {
    ensure_backup_dir();
    @file_put_contents(META_FILE, json_encode($meta, JSON_PRETTY_PRINT));
}

function get_current_db_name(mysqli $conn): string {
    $res = $conn->query("SELECT DATABASE() AS db");
    if ($res && ($row = $res->fetch_assoc()) && !empty($row['db'])) {
        return $row['db'];
    }
    return 'database';
}

function backup_filename(mysqli $conn): string {
    $db = preg_replace('/[^a-zA-Z0-9_\-]/', '_', get_current_db_name($conn));
    $ts = date('Y-m-d_H-i-s');
    return "backup_{$db}_{$ts}.sql";
}

/**
 * Pure PHP export (reliable everywhere).
 */
function create_backup_sql(mysqli $conn): array {
    ensure_backup_dir();

    if ($conn->connect_error) {
        return ['ok' => false, 'file' => null, 'method' => 'php', 'error' => 'DB connection failed: ' . $conn->connect_error];
    }

    $conn->set_charset('utf8mb4');

    $file = BACKUP_DIR . '/' . backup_filename($conn);

    $dbName = get_current_db_name($conn);

    $sql = "-- Backup of {$dbName}\n";
    $sql .= "-- Generated: " . date('c') . "\n\n";
    $sql .= "SET foreign_key_checks = 0;\n\n";

    $tables = [];
    $res = $conn->query("SHOW TABLES");
    if (!$res) {
        return ['ok' => false, 'file' => null, 'method' => 'php', 'error' => 'SHOW TABLES failed: ' . $conn->error];
    }

    while ($row = $res->fetch_array()) {
        $tables[] = $row[0];
    }

    foreach ($tables as $table) {
        // Create statement
        $rCreate = $conn->query("SHOW CREATE TABLE `{$table}`");
        if (!$rCreate) {
            return ['ok' => false, 'file' => null, 'method' => 'php', 'error' => "SHOW CREATE TABLE failed for {$table}: " . $conn->error];
        }

        $createRow = $rCreate->fetch_assoc();
        $createSQL = $createRow['Create Table'] ?? null;
        if (!$createSQL) {
            return ['ok' => false, 'file' => null, 'method' => 'php', 'error' => "Could not read CREATE TABLE for {$table}."];
        }

        $sql .= "\n-- ----------------------------\n";
        $sql .= "-- Table structure for `{$table}`\n";
        $sql .= "-- ----------------------------\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $sql .= $createSQL . ";\n\n";

        // Data
        $sql .= "-- ----------------------------\n";
        $sql .= "-- Records of `{$table}`\n";
        $sql .= "-- ----------------------------\n";

        $rData = $conn->query("SELECT * FROM `{$table}`");
        if (!$rData) {
            return ['ok' => false, 'file' => null, 'method' => 'php', 'error' => "SELECT failed for {$table}: " . $conn->error];
        }

        while ($dataRow = $rData->fetch_assoc()) {
            $cols = array_map(fn($c) => "`" . str_replace("`", "``", $c) . "`", array_keys($dataRow));
            $vals = array_map(function ($v) use ($conn) {
                if ($v === null) return "NULL";
                return "'" . $conn->real_escape_string($v) . "'";
            }, array_values($dataRow));

            $sql .= "INSERT INTO `{$table}` (" . implode(",", $cols) . ") VALUES (" . implode(",", $vals) . ");\n";
        }
        $sql .= "\n";
    }

    $sql .= "SET foreign_key_checks = 1;\n";

    if (@file_put_contents($file, $sql) === false) {
        return ['ok' => false, 'file' => null, 'method' => 'php', 'error' => 'Failed to write backup file. Check folder permissions.'];
    }

    return ['ok' => true, 'file' => $file, 'method' => 'php', 'error' => null];
}

function download_file(string $path, string $downloadName): void {
    if (!file_exists($path)) {
        http_response_code(404);
        echo "Backup not found.";
        exit;
    }
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

function run_sql_file(mysqli $conn, string $sqlPath): array {
    if (!file_exists($sqlPath)) return ['ok' => false, 'error' => 'SQL file not found.'];

    $sql = file_get_contents($sqlPath);
    if ($sql === false || trim($sql) === '') return ['ok' => false, 'error' => 'SQL file is empty or unreadable.'];

    if (!$conn->multi_query($sql)) {
        return ['ok' => false, 'error' => 'Restore failed: ' . $conn->error];
    }

    // flush results
    do {
        if ($result = $conn->store_result()) $result->free();
    } while ($conn->more_results() && $conn->next_result());

    return ['ok' => true, 'error' => null];
}

function get_ticket_count(mysqli $conn): int {
    // Change table name if needed
    $res = $conn->query("SELECT COUNT(*) AS c FROM tickets");
    if (!$res) return 0;
    $row = $res->fetch_assoc();
    return (int)($row['c'] ?? 0);
}

function auto_backup_if_needed(mysqli $conn): array {
    $meta = read_meta();
    $count = get_ticket_count($conn);

    $currentMilestone = intdiv($count, BACKUP_EVERY) * BACKUP_EVERY;

    if ($currentMilestone >= BACKUP_EVERY && $currentMilestone > (int)$meta['last_milestone']) {
        $backup = create_backup_sql($conn);

        if ($backup['ok']) {
            $meta['last_milestone'] = $currentMilestone;
            $meta['last_file'] = basename($backup['file']);
            $meta['last_time'] = date('c');
            write_meta($meta);

            return [
                'did_backup' => true,
                'count' => $count,
                'milestone' => $currentMilestone,
                'file' => $backup['file'],
                'method' => $backup['method'],
                'error' => null
            ];
        }

        return [
            'did_backup' => false,
            'count' => $count,
            'milestone' => $currentMilestone,
            'file' => null,
            'method' => null,
            'error' => $backup['error']
        ];
    }

    return ['did_backup' => false, 'count' => $count, 'milestone' => $currentMilestone, 'file' => null, 'method' => null, 'error' => null];
}
