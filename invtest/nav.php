<?php
// Highlight the current page in the sidebar (detail pages map to their parent section)
$navCurrent = basename($_SERVER['PHP_SELF']);
$navParents = [
    'item.php'   => 'category.php',
    'master.php' => 'category.php',
    'ritem.php'  => 'rcategory.php',
    'rmaster.php'=> 'rcategory.php',
];
$navActive = $navParents[$navCurrent] ?? $navCurrent;
$navLinks = [
    'dashboard.php' => 'Dashboard',
    'category.php'  => 'Raw Ingredients',
    'finishg.php'   => 'Finished Goods',
    'rcategory.php' => 'Recipe Categories',
    'prodform.php'  => 'Production Form',
    'history.php'   => 'Stock History',
    'waste.php'     => 'Waste Log',
];
?>
    <script>
    // Apply saved theme as early as possible (falls back to the OS setting)
    (function () {
        let t = null;
        try { t = localStorage.getItem('theme'); } catch (e) {}
        if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', t);
    })();
    </script>
    <nav>
        <div class="nav-brand">
            <div class="nav-brand-mark">I</div>
            <div class="nav-brand-text">
                <div class="nav-brand-name">Inventory</div>
                <div class="nav-brand-sub">POS &amp; Stock Control</div>
            </div>
            <button type="button" class="theme-toggle" id="themeToggle" aria-label="Switch to dark mode" title="Switch to dark mode">
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
            </button>
        </div>
        <div>
            <p class="nav-label">Menu</p>
            <ul>
                <?php foreach ($navLinks as $navFile => $navText): ?>
                <li><a href="/cl-test/invtest/<?php echo $navFile; ?>"<?php echo $navActive === $navFile ? ' class="active" aria-current="page"' : ''; ?>><?php echo $navText; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
    <script>
    // Shared UX helpers (loaded on every page through nav.php)
    document.addEventListener('DOMContentLoaded', () => {
        // 0) Light / dark theme toggle
        const themeBtn = document.getElementById('themeToggle');
        const syncThemeLabel = () => {
            const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            themeBtn.setAttribute('aria-label', 'Switch to ' + next + ' mode');
            themeBtn.title = 'Switch to ' + next + ' mode';
        };
        syncThemeLabel();
        themeBtn.addEventListener('click', () => {
            const t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', t);
            try { localStorage.setItem('theme', t); } catch (e) {}
            syncThemeLabel();
        });

        // 1) Search box above any card grid
        document.querySelectorAll('.container').forEach(grid => {
            const cards = grid.querySelectorAll('.card');
            if (cards.length < 2) return;
            const wrap = document.createElement('div');
            wrap.className = 'search-bar';
            wrap.innerHTML = '<input type="text" placeholder="Search…" aria-label="Search">';
            grid.parentNode.insertBefore(wrap, grid);
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'No matches.';
            empty.hidden = true;
            grid.after(empty);
            wrap.querySelector('input').addEventListener('input', e => {
                const q = e.target.value.trim().toLowerCase();
                let shown = 0;
                cards.forEach(c => { const hit = c.textContent.toLowerCase().includes(q); c.style.display = hit ? '' : 'none'; if (hit) shown++; });
                empty.hidden = shown > 0;
            });
        });

        // 2) Modals: Esc closes, first field gets focus when opened
        const modals = document.querySelectorAll('.modal');
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') modals.forEach(m => { m.style.display = 'none'; });
        });
        const observer = new MutationObserver(list => list.forEach(m => {
            const el = m.target;
            if (el.style.display === 'flex') {
                const first = el.querySelector('input:not([type=hidden]):not([disabled]), select:not([disabled])');
                if (first) setTimeout(() => first.focus(), 50);
            }
        }));
        modals.forEach(m => observer.observe(m, { attributes: true, attributeFilter: ['style'] }));
    });
    </script>
