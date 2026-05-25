import { Storage } from '../utils/Storage';
import { debounce } from '../utils/Debounce';
import { Logger } from '../utils/Logger';

/**
 * Navigation Engine
 * Handles Scroll Restoration and Active Menus
 */
export const Navigation = {
    init() {
        Logger.info('Initializing Navigation Engine...');
        this.initScrollRestoration();
        this.initActiveMenuPersistence();
    },

    initScrollRestoration() {
        // Save scroll position before leaving the page
        window.addEventListener('beforeunload', () => {
            const scrollPos = $(window).scrollTop();
            // Create a key based on the current path so scroll is specific to the page
            const pathKey = 'scroll_' + window.location.pathname;
            Storage.set(pathKey, scrollPos, 'session');
        });

        // Restore scroll position when DOM is ready
        const restoreScroll = () => {
            const pathKey = 'scroll_' + window.location.pathname;
            const scrollPos = Storage.get(pathKey, 'session');
            if (scrollPos) {
                // Ensure DOM has fully painted (especially if loading async data)
                setTimeout(() => {
                    $(window).scrollTop(scrollPos);
                    // Clear after restore so it doesn't jump unnecessarily later
                    Storage.remove(pathKey, 'session');
                    Logger.debug('Restored scroll to', scrollPos);
                }, 100);
            }
        };

        // Call restore
        restoreScroll();
    },

    initActiveMenuPersistence() {
        // Often active menu is determined by backend (Blade request()->routeIs())
        // But for nested collapse menus in JS, we should ensure the parent stays open
        
        const currentPath = window.location.pathname;
        const currentSearch = window.location.search;
        
        // Find links matching current path
        const $activeLink = $(`a[href="${currentPath}${currentSearch}"], a[href="${currentPath}"]`);
        
        if ($activeLink.length) {
            // Find parent collapse containers and expand them
            const $parents = $activeLink.parents('.collapse');
            $parents.each(function() {
                // If it's a Bootstrap collapse
                if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                    const bsCollapse = bootstrap.Collapse.getInstance(this);
                    if (!bsCollapse && !$(this).hasClass('show')) {
                        new bootstrap.Collapse(this, { toggle: true });
                    }
                } else if (window.Alpine) {
                    // For Alpine, typically it's an x-data component
                    // We might need to dispatch an event to open parent dropdowns
                    // Usually handled by blade, but JS can add a class if needed
                }
            });
        }
    }
};
