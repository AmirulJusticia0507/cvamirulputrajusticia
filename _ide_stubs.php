<?php
// IDE Helper Stubs for PHP PostgreSQL extension functions when pgsql extension is not active in local PHP CLI environment.

if (!function_exists('pg_connect')) {
    /**
     * Open a PostgreSQL connection
     * @param string $connection_string
     * @param int $flags
     * @return resource|object|false
     */
    function pg_connect(string $connection_string, int $flags = 0) { return false; }
}

if (!function_exists('pg_query')) {
    /**
     * Execute a query
     * @param resource|object $connection
     * @param string $query
     * @return resource|object|false
     */
    function pg_query($connection, string $query = '') { return false; }
}

if (!function_exists('pg_query_params')) {
    /**
     * Submits a command to the server and waits for the result
     * @param resource|object $connection
     * @param string $query
     * @param array $params
     * @return resource|object|false
     */
    function pg_query_params($connection, string $query, array $params) { return false; }
}

if (!function_exists('pg_num_rows')) {
    /**
     * Returns the number of rows in a result resource
     * @param resource|object $result
     * @return int
     */
    function pg_num_rows($result): int { return 0; }
}

if (!function_exists('pg_fetch_assoc')) {
    /**
     * Fetch a row as an associative array
     * @param resource|object $result
     * @param int|null $row
     * @return array|false
     */
    function pg_fetch_assoc($result, ?int $row = null) { return false; }
}

if (!function_exists('pg_fetch_all')) {
    /**
     * Fetches all rows from a result resource as an array
     * @param resource|object $result
     * @param int $mode
     * @return array|false
     */
    function pg_fetch_all($result, int $mode = 1) { return []; }
}

if (!function_exists('pg_fetch_array')) {
    function pg_fetch_array($result, ?int $row = null, int $mode = 3) { return false; }
}

if (!function_exists('pg_fetch_row')) {
    function pg_fetch_row($result, ?int $row = null) { return false; }
}

if (!function_exists('pg_fetch_result')) {
    /**
     * Returns values from a result resource
     * @param resource|object $result
     * @param int $row
     * @param mixed $field
     * @return string|false|null
     */
    function pg_fetch_result($result, int $row = 0, $field = 0) { return false; }
}

if (!function_exists('pg_affected_rows')) {
    function pg_affected_rows($result): int { return 0; }
}

if (!function_exists('pg_last_error')) {
    function pg_last_error($connection = null): string { return ''; }
}

if (!function_exists('pg_close')) {
    function pg_close($connection = null): bool { return true; }
}

if (!function_exists('pg_escape_string')) {
    function pg_escape_string($connection, string $data = ''): string { return $data; }
}
