<style>
body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    margin: 30px;
    background-color: #f4f6f8;
}

.container {
    max-width: 800px;
    margin: auto;
    background: #fff;
    padding: 20px;
    border-radius: 6px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

h2, h4 {
    text-align: center;
    color: #333;
}

form textarea, form input[type="number"], form input[type="text"] {
    width: 100%;
    padding: 8px 10px;
    margin: 5px 0 15px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

form button {
    background-color: #007bff;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

form button:hover {
    background-color: #0056b3;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

table th, table td {
    border: 1px solid #ccc;
    padding: 8px 10px;
    text-align: left;
}

table th {
    background-color: #007bff;
    color: #fff;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.action-links a {
    color: #007bff;
    text-decoration: none;
    margin-right: 5px;
}

.action-links a:hover {
    text-decoration: underline;
}
</style>

<?php
include 'config.php';
$surat_id = (int)$_GET['surat_id'];

if ($_POST) {
    pg_query_params(
        $conn,
        "INSERT INTO uraian_tugas_splp (surat_id, uraian, urutan) VALUES ($1,$2,$3)",
        [$surat_id, $_POST['uraian'], $_POST['urutan']]
    );
    header("Location: surat_tugas_preview.php?id=$surat_id");
    exit;

}
?>

<div class="container">
<h2>Tambah Uraian Tugas</h2>
<form method="post">
    <label>Uraian Tugas:</label>
    <textarea name="uraian" rows="5" placeholder="Tulis uraian tugas di sini..." required></textarea>

    <label>Urutan:</label>
    <input type="number" name="urutan" value="1" min="1" required>

    <button type="submit">Simpan</button>
</form>
</div>
