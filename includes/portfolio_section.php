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
// ============================================================

if (!isset($pfTitle))    $pfTitle = 'Featured Projects';
if (!isset($pfLinkLabel)) $pfLinkLabel = 'View on GitHub';

$pfItems = pg_query($conn, "SELECT * FROM portfolio ORDER BY sort_order ASC, id ASC");

if ($pfItems && pg_num_rows($pfItems) > 0):
?>
<div style="margin-top:18px;">
    <div style="font-size:18px;font-weight:700;border-bottom:2px solid #0d6efd;padding-bottom:5px;margin-bottom:8px;">
        <?= htmlspecialchars($pfTitle); ?>
    </div>
    <?php while ($pf = pg_fetch_assoc($pfItems)): ?>
        <div style="margin-bottom:10px;page-break-inside:avoid;">
            <div style="font-weight:600;font-size:14px;">
                <?= htmlspecialchars($pf['title'] ?? ''); ?>
            </div>
            <?php if (!empty($pf['description'])): ?>
                <div style="font-size:12.5px;color:#444;line-height:1.4;margin-top:2px;">
                    <?= htmlspecialchars($pf['description']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($pf['tech_stack'])): ?>
                <div style="font-size:12px;color:#666;margin-top:2px;">
                    <strong>Tech:</strong> <?= htmlspecialchars($pf['tech_stack']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($pf['repo_url'])): ?>
                <div style="font-size:12px;margin-top:2px;">
                    <a href="<?= htmlspecialchars($pf['repo_url']); ?>" style="color:#0d6efd;"><?= htmlspecialchars($pfLinkLabel); ?></a>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>
