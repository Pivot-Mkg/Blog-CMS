    </div>
</div>
<script>
    (function() {
        const root = document.documentElement;
        const body = document.body;
        const themeToggle = document.getElementById('theme-toggle');
        const collapseToggle = document.getElementById('sidebar-collapse');

        const applyTheme = (theme) => {
            if (theme === 'dark') {
                root.classList.add('theme-dark');
            } else {
                root.classList.remove('theme-dark');
            }
            localStorage.setItem('admin-theme', theme);
            if (themeToggle) {
                themeToggle.setAttribute('data-theme', theme);
            }
        };

        const currentTheme = localStorage.getItem('admin-theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(currentTheme || (prefersDark ? 'dark' : 'light'));

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const nextTheme = root.classList.contains('theme-dark') ? 'light' : 'dark';
                applyTheme(nextTheme);
            });
        }

        const applySidebarState = (collapsed) => {
            if (collapsed) {
                body.classList.add('sidebar-collapsed');
                root.classList.add('sidebar-collapsed');
            } else {
                body.classList.remove('sidebar-collapsed');
                root.classList.remove('sidebar-collapsed');
            }
            localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0');
            const icon = document.querySelector('.collapse-icon');
            if (icon) {
                const openClass = icon.getAttribute('data-open') || 'fa-arrow-left';
                const closedClass = icon.getAttribute('data-closed') || 'fa-arrow-right';
                icon.classList.remove(openClass, closedClass);
                icon.classList.add(collapsed ? closedClass : openClass);
            }
        };

        const savedCollapse = localStorage.getItem('sidebar-collapsed');
        applySidebarState(savedCollapse === '1');

        if (collapseToggle) {
            collapseToggle.addEventListener('click', () => {
                const willCollapse = !body.classList.contains('sidebar-collapsed');
                applySidebarState(willCollapse);
            });
        }
    })();
</script>
</body>
</html>
