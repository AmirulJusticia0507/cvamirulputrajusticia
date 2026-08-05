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
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<style>
textarea { font-size: 14px; line-height: 1.6; }
</style>
</head>
<body class="bg-gray-100">

<div class="max-w-6xl mx-auto px-4 py-4" style="max-width:900px">
<h3 class="mb-3"><?= $id ? '✏ Edit' : '➕ Create' ?> Motivation Letter</h3>

<form method="post" action="motivation_letter_save.php">
<input type="hidden" name="id" value="<?= e($data['id'] ?? '') ?>">

<!-- TITLE -->
<div class="mb-3">
<label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
<input name="title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required
value="<?= e($data['title'] ?? '') ?>"
placeholder="Motivation Letter – US – EN">
</div>

<!-- COUNTRY & LANGUAGE -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-3">
<div class="md:col-span-6">
<label class="block text-sm font-medium text-gray-700 mb-1">Target Market</label>
<select name="country_code" id="country_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
<option value="">-- Select --</option>
<option value="JP" <?= ($data['country_code'] ?? '')==='JP'?'selected':'' ?>>Japan</option>
<option value="EU" <?= ($data['country_code'] ?? '')==='EU'?'selected':'' ?>>Europe</option>
<option value="US" <?= ($data['country_code'] ?? '')==='US'?'selected':'' ?>>United States</option>
</select>
</div>

<div class="md:col-span-6">
<label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
<select name="language_code" id="language_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
<option value="">-- Select --</option>
<option value="ja" <?= ($data['language_code'] ?? '')==='ja'?'selected':'' ?>>Japanese</option>
<option value="en" <?= ($data['language_code'] ?? '')==='en'?'selected':'' ?>>English</option>
<option value="fr" <?= ($data['language_code'] ?? '')==='fr'?'selected':'' ?>>French</option>
</select>
</div>
</div>

<!-- CONTENT -->
<div class="mb-3">
<label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
<textarea name="content" id="content" rows="14" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required><?= e($data['content'] ?? '') ?></textarea>
</div>

<div class="flex gap-2">
<button class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-green-600 text-white hover:bg-green-700">💾 Save</button>
<a href="motivation_letter_list.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-gray-500 text-white hover:bg-gray-600">⬅ Back</a>
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
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
