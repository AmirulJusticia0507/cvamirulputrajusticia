<?php
include 'config.php';

function post($key) {
    return $_POST[$key] ?? null;
}

$id            = post('id');
$title         = post('title');
$country_code  = post('country_code');
$language_code = post('language_code');
$content       = post('content');

/**
 * Basic validation (jangan sok optimis)
 */
if (!$title || !$country_code || !$language_code || !$content) {
    die('Invalid input');
}

if ($id) {
    // UPDATE
    pg_query_params(
        $conn,
        "UPDATE motivation_letters
         SET title = $1,
             country_code = $2,
             language_code = $3,
             content = $4,
             updated_at = NOW()
         WHERE id = $5",
        [$title, $country_code, $language_code, $content, $id]
    );
} else {
    // INSERT
    pg_query_params(
        $conn,
        "INSERT INTO motivation_letters
            (title, country_code, language_code, content, created_at)
         VALUES ($1, $2, $3, $4, NOW())",
        [$title, $country_code, $language_code, $content]
    );
}

header("Location: motivation_letter_list.php");
exit;
