import './bootstrap';
import ApexCharts from 'apexcharts';
import * as Turbo from '@hotwired/turbo';

window.ApexCharts = ApexCharts;
window.Turbo = Turbo;

// Turbo: tell forms how to behave. Forms with `data-turbo="false"` fall back to
// native browser submission. Everything else is handled by Turbo Drive.
Turbo.session.drive = true;

/**
 * Native <dialog> + Turbo: an open modal or its backdrop can stick around after
 * form submit / morph / bfcache and block all clicks. Reset before caching the
 * page and after every visit.
 */
function resetBlockingOverlays() {
    document.querySelectorAll('dialog[open]').forEach((el) => el.close());
    document.getElementById('ai-loading-overlay')?.classList.add('hidden');
}

document.addEventListener('turbo:before-cache', resetBlockingOverlays);

// Apply the persisted theme on every Turbo visit (head is cached so the inline
// head script only runs on cold loads).
document.addEventListener('turbo:load', () => {
    resetBlockingOverlays();

    const theme = localStorage.getItem('theme');
    if (theme) {
        document.documentElement.setAttribute('data-theme', theme);
    }

    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        document.getElementById('app-drawer')?.classList.add('sidebar-collapsed');
    }
});

// Close the mobile drawer whenever we navigate to a new page.
document.addEventListener('turbo:before-visit', () => {
    const drawer = document.getElementById('main-drawer');
    if (drawer && drawer.checked) {
        drawer.checked = false;
    }
});

// Show a top progress bar immediately on slow navigations.
Turbo.setProgressBarDelay(150);
