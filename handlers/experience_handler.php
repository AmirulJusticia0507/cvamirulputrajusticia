<?php
// =======================
// HANDLE ADD WORK EXPERIENCE (FIXED)
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {

    $company      = trim($_POST['company']);
    $position     = trim($_POST['position']);
    $start_date   = $_POST['start_date'];
    $location     = trim($_POST['location'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $status_kerja = trim($_POST['status_kerja'] ?? 'Full-time');
    $project      = trim($_POST['project'] ?? '');
    $stack        = trim($_POST['stack'] ?? '');

    $present = isset($_POST['present']) ? 't' : 'f';

    if (!$start_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        die('Invalid start date format');
    }

    $end_date = null;
    if ($present === 'f') {
        if (!empty($_POST['end_date'])) {

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['end_date'])) {
                die('Invalid end date format');
            }

            $end_date = $_POST['end_date'];

            if ($end_date < $start_date) {
                die('End date cannot be earlier than start date');
            }
        }
    }

    $sql = "
        INSERT INTO work_experience
        (company, position, start_date, end_date, present, description, location, status_kerja, project, stack)
        VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10)
    ";

    $params = [
        $company,
        $position,
        $start_date,
        $end_date,
        $present,
        $description,
        $location,
        $status_kerja,
        $project,
        $stack
    ];

    $result = pg_query_params($conn, $sql, $params);

    if ($result) {
        header("Location: index.php");
        exit();
    } else {
        die("Error inserting work experience: " . pg_last_error($conn));
    }
}

// =======================
// HANDLE EDIT WORK EXPERIENCE (FIXED)
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {

    $id           = (int) $_POST['id'];
    $company      = trim($_POST['company']);
    $position     = trim($_POST['position']);
    $start_date   = $_POST['start_date'];
    $location     = trim($_POST['location'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $status_kerja = trim($_POST['status_kerja'] ?? 'Full-time');
    $project      = trim($_POST['project'] ?? '');
    $stack        = trim($_POST['stack'] ?? '');

    $present = (isset($_POST['present']) && $_POST['present'] === '1') ? 't' : 'f';

    if (!$start_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        die('Invalid start date format');
    }

    $end_date = null;
    if ($present === 'f') {
        if (!empty($_POST['end_date'])) {

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['end_date'])) {
                die('Invalid end date format');
            }

            $end_date = $_POST['end_date'];

            if ($end_date < $start_date) {
                die('End date cannot be earlier than start date');
            }
        }
    }

    $sql = "
        UPDATE work_experience
        SET company = $1,
            position = $2,
            start_date = $3,
            end_date = $4,
            present = $5,
            description = $6,
            location = $7,
            status_kerja = $8,
            project = $9,
            stack = $10
        WHERE id = $11
    ";

    $params = [
        $company,
        $position,
        $start_date,
        $end_date,
        $present,
        $description,
        $location,
        $status_kerja,
        $project,
        $stack,
        $id
    ];

    $result = pg_query_params($conn, $sql, $params);

    if ($result) {
        header("Location: index.php");
        exit();
    } else {
        die("Error updating work experience: " . pg_last_error($conn));
    }
}

// =======================
// HANDLE DELETE WORK EXPERIENCE
// =======================
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    pg_query($conn, "DELETE FROM work_experience WHERE id=$id");
    header("Location: index.php");
    exit();
} 