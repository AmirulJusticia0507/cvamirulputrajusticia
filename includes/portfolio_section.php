<?php
// ============================================================
// includes/portfolio_section.php
// Render "Featured Projects / Portfolio" yang bisa dipakai ulang
// di semua template CV (preview_cv*.php).
//
// Panggilan:
//   $pfTitle = 'Featured Projects';          // label section (sesuaikan bahasa)
//   $pfLinkLabel = 'View on GitHub';         // label link repo
//   include __DIR__ . '/includes/portfolio_section.php';
//
// Gaya dipakai inline agar konsisten di semua template yang CSS-nya berbeda-beda.
// Dark-mode overrides ditambahkan agar section ini tetap nyaman dibaca
// ketika pengguna mengaktifkan dark mode.
// ============================================================

if (!isset($pfTitle))    $pfTitle = 'Featured Projects';
if (!isset($pfLinkLabel)) $pfLinkLabel = 'View on GitHub';

if (!isset($preview_user_id)) {
    $preview_user_id = get_preview_user_id($conn);
}

if ($preview_user_id) {
    $pfItems = pg_query_params($conn, "SELECT * FROM portfolio WHERE user_id=$1 ORDER BY sort_order ASC, id ASC", [$preview_user_id]);
} else {
    $pfItems = pg_query($conn, "SELECT * FROM portfolio ORDER BY sort_order ASC, id ASC");
}

if ($pfItems && pg_num_rows($pfItems) > 0):
?>
<div class="featured-projects" style="margin-top:18px;">
    <div class="pf-header" style="font-size:18px;font-weight:700;border-bottom:2px solid #0d6efd;padding-bottom:5px;margin-bottom:8px;">
        <?= htmlspecialchars($pfTitle); ?>
    </div>
    <?php while ($pf = pg_fetch_assoc($pfItems)): ?>
        <div class="pf-item" style="margin-bottom:10px;page-break-inside:avoid;">
            <div class="pf-title" style="font-weight:600;font-size:14px;">
                <?= htmlspecialchars($pf['title'] ?? ''); ?>
            </div>
            <?php if (!empty($pf['description'])): ?>
                <div class="pf-desc" style="font-size:12.5px;color:#444;line-height:1.4;margin-top:2px;">
                    <?= htmlspecialchars($pf['description']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($pf['tech_stack'])): ?>
                <div class="pf-tech" style="font-size:12px;color:#666;margin-top:2px;">
                    <strong>Tech:</strong> <?= htmlspecialchars($pf['tech_stack']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($pf['demo_url'])): ?>
                <div style="font-size:12px;margin-top:2px;">
                    <a href="<?= htmlspecialchars($pf['demo_url']); ?>" class="pf-demo" style="color:#0d6efd;" target="_blank" rel="noopener"><strong>Live Demo</strong></a>
                </div>
            <?php endif; ?>
            <?php if (!empty($pf['repo_url'])): ?>
                <div style="font-size:12px;margin-top:2px;">
                    <a href="<?= htmlspecialchars($pf['repo_url']); ?>" class="pf-link" style="color:#0d6efd;"><strong><?= htmlspecialchars($pfLinkLabel); ?></strong></a>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<!-- Dark-mode overrides for Featured Projects / Portfolio -->
<style>
body.dark .featured-projects .pf-title  { color:#e8ecf2; }
body.dark .featured-projects .pf-desc  { color:#9ca3af !important; }
body.dark .featured-projects .pf-tech  { color:#9ca3af !important; }
body.dark .featured-projects .pf-link  { color:#60a5fa !important; }
body.dark .featured-projects .pf-demo { color:#60a5fa !important; }
body.dark .featured-projects .pf-header { border-color:#0d6efd; color:#e8ecf2; }
</style>
