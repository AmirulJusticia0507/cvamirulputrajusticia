<?php
// =======================
// HANDLE ADD PORTFOLIO
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_portfolio') {

    $title      = trim($_POST['title'] ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $stack      = trim($_POST['tech_stack'] ?? '');
    $repo_url   = trim($_POST['repo_url'] ?? '');
    $demo_url   = trim($_POST['demo_url'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if ($title === '') {
        die('Title is required');
    }

    $sql = "INSERT INTO portfolio (title, description, tech_stack, repo_url, demo_url, sort_order)
            VALUES ($1,$2,$3,$4,$5,$6)";

    $result = pg_query_params($conn, $sql, [$title, $desc, $stack, $repo_url, $demo_url, $sort_order]);

    if ($result) {
        header("Location: index.php");
        exit();
    } else {
        die("Error inserting portfolio: " . pg_last_error($conn));
    }
}

// =======================
// HANDLE EDIT PORTFOLIO
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_portfolio') {

    $id         = (int)($_POST['id'] ?? 0);
    $title      = trim($_POST['title'] ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $stack      = trim($_POST['tech_stack'] ?? '');
    $repo_url   = trim($_POST['repo_url'] ?? '');
    $demo_url   = trim($_POST['demo_url'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if ($title === '') {
        die('Title is required');
    }

    $sql = "UPDATE portfolio SET
                title = $1,
                description = $2,
                tech_stack = $3,
                repo_url = $4,
                demo_url = $5,
                sort_order = $6
            WHERE id = $7";

    $result = pg_query_params($conn, $sql, [$title, $desc, $stack, $repo_url, $demo_url, $sort_order, $id]);

    if ($result) {
        header("Location: index.php");
        exit();
    } else {
        die("Error updating portfolio: " . pg_last_error($conn));
    }
}

// =======================
// HANDLE DELETE PORTFOLIO
// =======================
if (isset($_GET['delete_portfolio'])) {
    $id = (int) $_GET['delete_portfolio'];
    pg_query($conn, "DELETE FROM portfolio WHERE id=$id");
    header("Location: index.php");
    exit();
}
