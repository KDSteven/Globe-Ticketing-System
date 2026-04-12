<?php
/**
 * utils/helpers.php
 * Shared utility functions — no side-effects, no globals, no DB access.
 *
 * Include with: require_once __DIR__ . '/utils/helpers.php';  (from project root)
 *           or: require_once __DIR__ . '/helpers.php';         (from utils/)
 *           or: require_once __DIR__ . '/../utils/helpers.php'; (from api/)
 */

/**
 * HTML-escape a value for safe output.
 */
function h($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/**
 * Build a validated date-condition fragment for use in SQL WHERE clauses.
 *
 * All inputs are validated against whitelists or regex before use,
 * so the returned string is safe to interpolate directly into a query.
 *
 * @return string  A SQL boolean expression (never empty)
 */
function dateCondition(string $period, string $date, string $month, string $quarter, string $year): string
{
    // Whitelist / format validation — reject anything unexpected
    $period  = in_array($period,  ['all', 'daily', 'monthly', 'quarterly', 'yearly'], true) ? $period  : 'all';
    $quarter = in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'], true)                           ? $quarter : 'Q1';
    $date    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)  ? $date  : date('Y-m-d');
    $month   = preg_match('/^\d{4}-\d{2}$/',       $month) ? $month : date('Y-m');
    $year    = preg_match('/^\d{4}$/',             $year)  ? $year  : date('Y');

    switch ($period) {
        case 'daily':
            return "DATE(created_at) = '$date'";

        case 'monthly':
            return "DATE_FORMAT(created_at,'%Y-%m') = '$month'";

        case 'quarterly':
            $ranges = [
                'Q1' => ['01-01', '03-31'],
                'Q2' => ['04-01', '06-30'],
                'Q3' => ['07-01', '09-30'],
                'Q4' => ['10-01', '12-31'],
            ];
            [$s, $e] = $ranges[$quarter];
            return "created_at BETWEEN '$year-$s' AND '$year-$e'";

        case 'yearly':
            return "YEAR(created_at) = '$year'";

        default: // 'all'
            return '1';
    }
}
