document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent    = document.querySelector('.main-content');
    const topHeader      = document.querySelector('.top-header');

    const menuToggle = document.getElementById('btnMenuToggle')
                    || document.getElementById('menuToggle');

    const btnClose = document.getElementById('btnCloseSidebar')
                  || document.getElementById('closeSidebar');

    if (!sidebar || !mainContent || !topHeader) return;

    const STORAGE_KEY = 'app-sidebar';

    function closeAllSubmenus() {
        sidebar.querySelectorAll('.collapse.show')
               .forEach(s => s.classList.remove('show'));
        sidebar.querySelectorAll('[aria-expanded="true"]')
               .forEach(l => l.setAttribute('aria-expanded', 'false'));
    }

    function setCollapsed(collapsed) {
        if (collapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
            topHeader.classList.add('sidebar-collapsed');
            closeAllSubmenus();
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
            topHeader.classList.remove('sidebar-collapsed');
        }
        localStorage.setItem(STORAGE_KEY, collapsed ? 'collapsed' : 'expanded');
    }

    function openMobile() {
        sidebar.classList.add('show');
        sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeMobile() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            if (window.innerWidth >= 992) {
                setCollapsed(!sidebar.classList.contains('collapsed'));
            } else {
                sidebar.classList.contains('show') ? closeMobile() : openMobile();
            }
        });
    }

    if (btnClose) btnClose.addEventListener('click', closeMobile);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeMobile);

    document.querySelectorAll('.sidebar-nav .nav-link:not([data-bs-toggle])')
            .forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 992) closeMobile();
                });
            });

    sidebar.addEventListener('mouseleave', () => {
        if (sidebar.classList.contains('collapsed')) closeAllSubmenus();
    });

    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (window.innerWidth >= 992) {
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
                topHeader.classList.remove('sidebar-collapsed');
                if (!sidebar.classList.contains('show')) {
                    sidebarOverlay.classList.remove('show');
                }
            }
        }, 250);
    });

    if (window.innerWidth >= 992) {
        const saved          = localStorage.getItem(STORAGE_KEY);
        const startCollapsed = saved === 'collapsed';
        setCollapsed(startCollapsed);
    }
});