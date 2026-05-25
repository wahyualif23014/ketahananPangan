import { Logger } from './utils/Logger';
import { StateManager } from './engine/StateManager';
import { Navigation } from './engine/Navigation';
import { FormDraft } from './engine/FormDraft';
import { DataTablePersist } from './engine/DataTablePersist';
import { AjaxCrud } from './engine/AjaxCrud';

/**
 * Main Application Hub
 * Initializes all core modules
 */
export const SikapApp = {
    init() {
        Logger.info('SikapApp Architecture Initializing...');

        // 1. Navigation & Scroll (Do this early to prevent jumping)
        Navigation.init();

        // 2. Component State Restoration
        StateManager.init();

        // 3. Form Drafts Autosave
        FormDraft.init();

        // 4. DataTable Persistence
        DataTablePersist.init();

        // 5. AJAX Interception for Partial Rendering
        AjaxCrud.init();

        Logger.info('SikapApp Initialization Complete.');
    }
};
