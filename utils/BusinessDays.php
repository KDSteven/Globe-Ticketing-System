<?php
/**
 * utils/BusinessDays.php
 * Philippine business day and holiday calculation utilities.
 *
 * All functions are stateless. Pass $conn only to functions that query the DB.
 *
 * Include with: require_once __DIR__ . '/utils/BusinessDays.php';   (from project root)
 *           or: require_once __DIR__ . '/../utils/BusinessDays.php'; (from api/)
 */

/**
 * Returns true if the given date falls on a Saturday or Sunday.
 */
function isWeekend(DateTime $d): bool
{
    return in_array((int)$d->format('N'), [6, 7], true); // 6=Sat, 7=Sun
}

/**
 * Returns true if the given date is a Philippine public holiday.
 * Checks fixed annual holidays, Holy Week dates (computed from Easter),
 * and any additional holidays stored in the `holidays` DB table.
 */
function isPhilippineHoliday(DateTime $date, mysqli $conn): bool
{
    $year = (int)$date->format('Y');
    $ymd  = $date->format('Y-m-d');

    // Regular yearly holidays
    $regular = [
        "$year-01-01",
        "$year-05-01",
        "$year-06-12",
        "$year-11-30",
        "$year-12-25",
        "$year-12-30",
    ];

    // Special holidays
    $special = [
        "$year-02-25",
        "$year-11-01",
        "$year-11-02",
        "$year-12-08",
    ];

    // Holy Week (auto computed from Easter)
    $easter = new DateTime('@' . easter_date($year));
    $easter->setTimezone(new DateTimeZone('Asia/Manila'));

    $maundy   = clone $easter; $maundy->modify('-3 days');
    $goodFri  = clone $easter; $goodFri->modify('-2 days');
    $blackSat = clone $easter; $blackSat->modify('-1 day');

    $holyweek = [
        $maundy->format('Y-m-d'),
        $goodFri->format('Y-m-d'),
        $blackSat->format('Y-m-d'),
    ];

    if (in_array($ymd, $regular) || in_array($ymd, $special) || in_array($ymd, $holyweek)) {
        return true;
    }

    // Check database for sudden/proclamation holidays
    $stmt = $conn->prepare("SELECT 1 FROM holidays WHERE date = ? LIMIT 1");
    $stmt->bind_param("s", $ymd);
    $stmt->execute();
    $rs = $stmt->get_result();
    $stmt->close();
    return ($rs && $rs->num_rows > 0);
}

/**
 * Returns a new DateTime set to the next business day at 8:00 AM.
 * Skips weekends and Philippine holidays.
 */
function nextBusinessDayAt8(DateTime $date, mysqli $conn): DateTime
{
    $d = clone $date;
    $d->setTime(8, 0, 0);
    do {
        $d->modify('+1 day');
        $d->setTime(8, 0, 0);
    } while (isWeekend($d) || isPhilippineHoliday($d, $conn));
    return $d;
}

/**
 * Returns the "effective" createdAt based on business hours (8 AM – 4 PM):
 * - Weekend or holiday          → next business day 8:00 AM
 * - After 4:00 PM               → next business day 8:00 AM
 * - Before 8:00 AM              → same day 8:00 AM
 * - Within business hours       → unchanged
 */
function applyBusinessHours(DateTime $createdAt, mysqli $conn): DateTime
{
    $d = clone $createdAt;

    if (isWeekend($d) || isPhilippineHoliday($d, $conn)) {
        while (isWeekend($d) || isPhilippineHoliday($d, $conn)) {
            $d->modify('+1 day');
        }
        $d->setTime(8, 0, 0);
        return $d;
    }

    $t = $d->format('H:i:s');

    if ($t > '16:00:00') {
        return nextBusinessDayAt8($d, $conn);
    }

    if ($t < '08:00:00') {
        $d->setTime(8, 0, 0);
        return $d;
    }

    return $d;
}

/**
 * Adds the specified number of business days to a date, skipping weekends and
 * Philippine holidays. Returns the resulting DateTime.
 */
function addBusinessDays(DateTime $date, int $days, mysqli $conn): DateTime
{
    $d = clone $date;

    while ($days > 0) {
        $d->modify('+1 day');

        if (in_array($d->format('N'), [6, 7])) {
            continue; // skip weekend
        }

        if (isPhilippineHoliday($d, $conn)) {
            continue; // skip holiday
        }

        $days--;
    }

    return $d;
}
