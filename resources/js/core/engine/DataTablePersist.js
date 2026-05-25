import { Storage } from '../utils/Storage';
import { Logger } from '../utils/Logger';

/**
 * DataTablePersist Engine
 * Enhances DataTables with global state persistence configurations
 */
export const DataTablePersist = {
    init() {
        Logger.info('Initializing DataTablePersist Engine...');
        
        // Check if jQuery DataTables is loaded
        if ($.fn.dataTable) {
            this.setupGlobalConfig();
        } else {
            Logger.debug('DataTables not detected on page load. Waiting or skipping...');
        }
    },

    setupGlobalConfig() {
        // Extend default DataTables configuration to always use state saving
        $.extend(true, $.fn.dataTable.defaults, {
            stateSave: true,
            stateSaveCallback: function (settings, data) {
                // Determine a unique key for the table
                // Format: dt_state_[table_id]_[pathname]
                const tableId = settings.sTableId;
                const path = window.location.pathname;
                const key = `dt_state_${tableId}_${path}`;
                
                Storage.set(key, data, 'local'); // Use localStorage for permanent persistence
            },
            stateLoadCallback: function (settings) {
                const tableId = settings.sTableId;
                const path = window.location.pathname;
                const key = `dt_state_${tableId}_${path}`;
                
                return Storage.get(key, 'local', null);
            },
            // Maintain layout classes to not conflict with Tailwind if used
            language: {
                search: "",
                searchPlaceholder: "Cari data...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        // Trigger custom event so other scripts know it's ready
        $(document).trigger('sikap:datatable:ready');
    }
};
