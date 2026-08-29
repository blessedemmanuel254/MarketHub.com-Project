<?php
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "makethub";
  
  $conn = new mysqli($servername, $username, $password, $dbname);
  
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

/*
 * =========================================================
 * MARKET HUB - USER LOCAL TIME SYSTEM
 * =========================================================
 *
 * DATABASE:
 *     Store timestamps in UTC.
 *
 * USER:
 *     Store their IANA timezone in users.timezone.
 *
 * DISPLAY:
 *     Convert UTC -> user's local timezone.
 *
 * REPORTS:
 *     Calculate local-day boundaries and convert them
 *     back to UTC before querying MySQL.
 * ========================================================= */


/*
 * ---------------------------------------------------------
 * DEFAULT TIMEZONE
 * ---------------------------------------------------------
 *
 * Used when the user has no timezone saved.
 *
 * Kenya:
 * Africa/Nairobi
 */

$defaultTimezone = 'Africa/Nairobi';


/*
 * ---------------------------------------------------------
 * GET USER TIMEZONE
 * ---------------------------------------------------------
 */

$userTimezoneName = $defaultTimezone;


/*
 * If the user is logged in, get their saved timezone.
 */

if (!empty($user_id)) {

    $timezoneStmt = $conn->prepare("
        SELECT timezone
        FROM users
        WHERE user_id = ?
        LIMIT 1
    ");

    if ($timezoneStmt) {

        $timezoneStmt->bind_param(
            "i",
            $user_id
        );

        $timezoneStmt->execute();

        $timezoneResult =
            $timezoneStmt->get_result();

        if ($timezoneResult) {

            $timezoneRow =
                $timezoneResult->fetch_assoc();

            if (
                !empty($timezoneRow['timezone']) &&
                in_array(
                    $timezoneRow['timezone'],
                    DateTimeZone::listIdentifiers(),
                    true
                )
            ) {

                $userTimezoneName =
                    $timezoneRow['timezone'];

            }

        }

        $timezoneStmt->close();

    }

}


/*
 * ---------------------------------------------------------
 * CREATE USER TIMEZONE OBJECT
 * ---------------------------------------------------------
 */

$userTimezone = new DateTimeZone(
    $userTimezoneName
);


/*
 * ---------------------------------------------------------
 * DATABASE TIMEZONE
 * ---------------------------------------------------------
 *
 * MarketHub stores timestamps in UTC.
 */

$databaseTimezone = new DateTimeZone(
    'UTC'
);


/*
 * ---------------------------------------------------------
 * CURRENT USER LOCAL TIME
 * ---------------------------------------------------------
 */

$userNow = new DateTime(
    'now',
    $userTimezone
);


/*
 * ---------------------------------------------------------
 * HELPER:
 * UTC DATABASE TIMESTAMP -> USER LOCAL TIME
 * ---------------------------------------------------------
 */

function userLocalTime(
    ?string $utcTimestamp,
    ?DateTimeZone $userTimezone = null
): ?DateTime {

    if (
        empty($utcTimestamp)
    ) {
        return null;
    }


    if (
        $userTimezone === null
    ) {

        $userTimezone =
            new DateTimeZone(
                'Africa/Nairobi'
            );

    }


    $date = new DateTime(
        $utcTimestamp,
        new DateTimeZone('UTC')
    );


    $date->setTimezone(
        $userTimezone
    );


    return $date;

}


/*
 * ---------------------------------------------------------
 * HELPER:
 * FORMAT DATABASE TIME FOR USER
 * ---------------------------------------------------------
 */

function formatUserDateTime(
    ?string $utcTimestamp,
    ?DateTimeZone $userTimezone = null,
    string $format = 'd M Y, H:i'
): string {

    $date =
        userLocalTime(
            $utcTimestamp,
            $userTimezone
        );


    if ($date === null) {
        return '';
    }


    return $date->format(
        $format
    );

}


/*
 * ---------------------------------------------------------
 * HELPER:
 * GET TODAY'S UTC RANGE FOR USER
 * ---------------------------------------------------------
 *
 * This is extremely important for:
 *
 *     Today's sales
 *     Today's orders
 *     Today's transactions
 *     Daily dashboard statistics
 *
 * The user may be in any timezone.
 * ---------------------------------------------------------
 */

function getUserTodayUtcRange(
    DateTimeZone $userTimezone
): array {


    /*
     * Start with the user's local date.
     */

    $localStart =
        new DateTime(
            'now',
            $userTimezone
        );


    /*
     * Midnight in user's timezone.
     */

    $localStart->setTime(
        0,
        0,
        0
    );


    /*
     * Tomorrow at midnight in user's timezone.
     */

    $localEnd =
        clone $localStart;

    $localEnd->modify(
        '+1 day'
    );


    /*
     * Convert both boundaries to UTC.
     */

    $localStart->setTimezone(
        new DateTimeZone('UTC')
    );


    $localEnd->setTimezone(
        new DateTimeZone('UTC')
    );


    return [

        'start' =>
            $localStart->format(
                'Y-m-d H:i:s'
            ),

        'end' =>
            $localEnd->format(
                'Y-m-d H:i:s'
            )

    ];

}
?>