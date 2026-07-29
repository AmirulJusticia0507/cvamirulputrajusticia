<?php
include 'config.php';

function e($s){
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

$id = $_GET['id'] ?? null;
$data = null;

if ($id) {
    $res = pg_query_params(
        $conn,
        "SELECT * FROM motivation_letters WHERE id=$1",
        [$id]
    );
    $data = pg_fetch_assoc($res);
}

/**
 * Default templates (seed-level)
 */
$templates = [
    'JP_ja' => "拝啓  

貴重なお時間をいただき、誠にありがとうございます。  
私は◯◯と申します。これまで◯◯分野において、◯年間の実務経験を積んでまいりました。

貴社の◯◯という理念・事業内容に深く共感し、ぜひその一員として貢献したいと考え、本書を提出いたしました。

何卒ご高配を賜りますよう、よろしくお願い申し上げます。

敬具",

    'EU_en' => "Dear Hiring Committee,

I am writing to express my strong motivation to apply for a position within your organization. With several years of experience in software development, I have developed a solid foundation in building reliable systems.

I am highly motivated to contribute in a structured and international working environment.

Sincerely,",

    'US_en' => "Dear Hiring Manager,

I am writing to express my interest in opportunities within your organization. I have experience building scalable web applications and solving complex technical problems.

I would welcome the opportunity to further discuss how I could contribute to your team.

Best regards,"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $id ? 'Edit' : 'Create' ?> Motivation Letter</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
textarea { font-size: 14px; line-height: 1.6; }
</style>
</head>
<body class="bg-light">

<div class="container py-4" style="max-width:900px">
<h3 class="mb-3"><?= $id ? '✏ Edit' : '➕ Create' ?> Motivation Letter</h3>

<form method="post" action="motivation_letter_save.php">
<input type="hidden" name="id" value="<?= e($data['id'] ?? '') ?>">

<!-- TITLE -->
<div class="mb-3">
<label class="form-label">Title</label>
<input name="title" class="form-control" required
value="<?= e($data['title'] ?? '') ?>"
placeholder="Motivation Letter – US – EN">
</div>

<!-- COUNTRY & LANGUAGE -->
<div class="row mb-3">
<div class="col-md-6">
<label class="form-label">Target Market</label>
<select name="country_code" id="country_code" class="form-select" required>
<option value="">-- Select --</option>
<option value="JP" <?= ($data['country_code'] ?? '')==='JP'?'selected':'' ?>>Japan</option>
<option value="EU" <?= ($data['country_code'] ?? '')==='EU'?'selected':'' ?>>Europe</option>
<option value="US" <?= ($data['country_code'] ?? '')==='US'?'selected':'' ?>>United States</option>
</select>
</div>

<div class="col-md-6">
<label class="form-label">Language</label>
<select name="language_code" id="language_code" class="form-select" required>
<option value="">-- Select --</option>
<option value="ja" <?= ($data['language_code'] ?? '')==='ja'?'selected':'' ?>>Japanese</option>
<option value="en" <?= ($data['language_code'] ?? '')==='en'?'selected':'' ?>>English</option>
<option value="fr" <?= ($data['language_code'] ?? '')==='fr'?'selected':'' ?>>French</option>
</select>
</div>
</div>

<!-- CONTENT -->
<div class="mb-3">
<label class="form-label">Content</label>
<textarea name="content" id="content" rows="14" class="form-control" required><?= e($data['content'] ?? '') ?></textarea>
</div>

<div class="d-flex gap-2">
<button class="btn btn-success">💾 Save</button>
<a href="motivation_letter_list.php" class="btn btn-secondary">⬅ Back</a>
</div>
</form>
</div>

<script>
const templates = <?= json_encode($templates) ?>;
const country = document.getElementById('country_code');
const lang = document.getElementById('language_code');
const content = document.getElementById('content');
const titleInput = document.querySelector('input[name="title"]');
const isEdit = <?= $id ? 'true' : 'false' ?>;

// auto template
function tryLoadTemplate(){
    if (content.value.trim() !== '') return;
    const key = country.value + '_' + lang.value;
    if (templates[key]) content.value = templates[key];
}

// auto title
function autoTitle(){
    if (isEdit) return;
    if (!country.value || !lang.value) return;
    if (titleInput.value.trim() !== '') return;
    titleInput.value = `Motivation Letter – ${country.value} – ${lang.value.toUpperCase()}`;
}

country.addEventListener('change', () => {
    tryLoadTemplate();
    autoTitle();
});
lang.addEventListener('change', () => {
    tryLoadTemplate();
    autoTitle();
});
</script>
</body>
</html>
