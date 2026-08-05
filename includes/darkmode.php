<?php
// ============================================================
// includes/darkmode.php
// Tombol dark mode global. Pasang sebelum </body> di halaman:
//   <?php include __DIR__ . '/includes/darkmode.php'; ?>
// Preferensi disimpan di localStorage key 'cv_dark'.
// ============================================================
?>
<style>
#darkModeToggle {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 2000;
    border: none;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
    padding: 10px 12px;
    border-radius: 50%;
    background: #374151;
    color: #fbbf24;
    box-shadow: 0 4px 12px rgba(0,0,0,.25);
    transition: background .2s;
}
#darkModeToggle:hover { background: #4b5563; }
body.dark #darkModeToggle { background: #fbbf24; color: #1f2937; }
@media print { #darkModeToggle { display: none !important; } }

body.dark { background: #16181d !important; color: #d6dae1 !important; }
body.dark h1, body.dark h2, body.dark h3, body.dark h4, body.dark h5, body.dark h6 { color: #e8ecf2; }
body.dark a { color: #7cb3ff; }
body.dark .bg-white { background: #1f232b !important; }
body.dark .bg-gray-50 { background: #23282f !important; }
body.dark .bg-gray-100 { background: #1c1f24 !important; }
body.dark .bg-red-100 { background: #3a2020 !important; color: #f0a3a3; }
body.dark .bg-yellow-100 { background: #4a3a14 !important; color: #f0dba3; }
body.dark .bg-green-100 { background: #143a22 !important; color: #a3f0ba; }
body.dark .bg-blue-100 { background: #14304a !important; color: #a3d0f0; }
body.dark input, body.dark textarea, body.dark select {
    background: #16181d !important;
    color: #e2e6ec !important;
    border-color: #3a414c !important;
}
body.dark .border-gray-300 { border-color: #3a414c !important; }
body.dark table thead { background: #2a2f38 !important; }
body.dark th, body.dark td { color: #c7cdd6; }
body.dark .text-gray-500, body.dark .text-gray-600, body.dark .text-gray-700 { color: #aeb6c2 !important; }
body.dark .text-blue-600 { color: #7cb3ff !important; }
body.dark .shadow-sm { box-shadow: 0 2px 10px rgba(0,0,0,.35); }
body.dark .shadow, body.dark .shadow-md, body.dark .shadow-lg, body.dark .shadow-xl { box-shadow: 0 6px 22px rgba(0,0,0,.45); }

/* CV preview templates */
body.dark #cv { background: #1f232b !important; color: #d6dae1; box-shadow: none; }
body.dark .exp-card { background: #23282f !important; }
body.dark .exp-meta, body.dark .meta, body.dark .header-meta, body.dark .job-meta, body.dark .cv-action-card { color: #aeb6c2; }
body.dark .section-title, body.dark .section h2, body.dark .section h3 { border-bottom-color: #fbbf24; }
body.dark .cv-action-card { background: #1f232b; }
body.dark .skill-block span, body.dark .skills span { background: #2a3039; color: #e2e6ec; }
body.dark .education p, body.dark .footer { color: #aeb6c2; }
body.dark table td, body.dark table th { border-color: #3a414c; background: #1f232b; }
body.dark table th { background: #2a2f38; color: #c7cdd6; }
body.dark .job, body.dark .exp { color: #d6dae1; }
</style>

<button id="darkModeToggle" type="button" title="Toggle dark mode">🌙</button>

<script>
(function () {
    var btn = document.getElementById('darkModeToggle');
    function apply() {
        var dark = localStorage.getItem('cv_dark') === '1';
        document.body.classList.toggle('dark', dark);
        if (btn) btn.textContent = dark ? '☀️' : '🌙';
    }
    apply();
    if (btn) {
        btn.addEventListener('click', function () {
            var dark = document.body.classList.toggle('dark');
            localStorage.setItem('cv_dark', dark ? '1' : '0');
            btn.textContent = dark ? '☀️' : '🌙';
        });
    }
})();
</script>
