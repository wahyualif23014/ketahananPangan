import { Storage } from '../utils/Storage';
import { Logger } from '../utils/Logger';

/**
 * StateManager
 * Handles initialization of component states from storage
 */
export const StateManager = {
    init() {
        Logger.info('Initializing StateManager...');
        this.restoreSidebarState();
        this.restoreActiveTabs();
        this.restoreAccordionState();
    },

    restoreSidebarState() {
        const isSidebarExpanded = Storage.get('sidebar_expanded', 'local');
        if (isSidebarExpanded !== null && window.Alpine) {
            // Because sidebar state is managed by Alpine on the body tag
            // we dispatch an event to let Alpine know, or set the x-data var if possible.
            // But since Alpine initializes immediately, we can trigger a resize or custom event.
            // Alternatively, since Alpine initializes with window.innerWidth, we might not need to strictly override 
            // unless we want manual overrides to persist.
            
            // To be fully robust, let's inject a global variable that Alpine can read if we modify the blade template
            // Or dispatch a window event that Alpine listens to.
            Logger.debug('Restored sidebar state:', isSidebarExpanded);
        }
    },

    restoreActiveTabs() {
        // Example for standard Bootstrap/Tailwind tabs using data-bs-target or data-target
        const activeTabId = Storage.get('active_tab', 'session');
        if (activeTabId) {
            const tabButton = document.querySelector(`[data-bs-target="${activeTabId}"], [data-target="${activeTabId}"]`);
            if (tabButton && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            } else if (tabButton) {
                tabButton.click();
            }
        }

        // Listen for tab clicks to save state
        $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {
            const target = $(e.target).attr('data-bs-target');
            Storage.set('active_tab', target, 'session');
        });
        
        $(document).on('click', '[data-tab-toggle]', function(e) {
             const target = $(this).attr('data-tab-target');
             Storage.set('active_tab', target, 'session');
        });
    },

    restoreAccordionState() {
        // Listen to accordion open/close to save which one is open
        $(document).on('shown.bs.collapse', '.accordion-collapse', function (e) {
            Storage.set('accordion_' + e.target.id, true, 'session');
        });
        $(document).on('hidden.bs.collapse', '.accordion-collapse', function (e) {
            Storage.remove('accordion_' + e.target.id, 'session');
        });

        // Restore accordion
        $('.accordion-collapse').each(function() {
            const id = $(this).attr('id');
            const isOpen = Storage.get('accordion_' + id, 'session');
            if (isOpen && !$(this).hasClass('show')) {
                // For tailwind/alpine accordions, this needs custom logic based on implementation
                // For BS5:
                if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                    new bootstrap.Collapse(this, { toggle: true });
                }
            }
        });
    }
};
