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
    <nav>
        <div class="nav-brand">
            <div class="nav-brand-mark">I</div>
            <div class="nav-brand-text">
                <div class="nav-brand-name">Inventory</div>
                <div class="nav-brand-sub">POS &amp; Stock Control</div>
            </div>
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
