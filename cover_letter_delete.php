<?php
include 'config.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Invalid ID');
}

pg_query_params(
    $conn,
    "DELETE FROM cover_letters WHERE id=$1",
    [$id]
);

header("Location: cover_letter_list.php");
exit;
