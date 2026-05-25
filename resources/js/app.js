import './bootstrap';
import Alpine from 'alpinejs';

// Import our new Core Architecture
import { SikapApp } from './core/App';

window.Alpine = Alpine;
Alpine.start();

// Initialize our Enterprise Frontend Engine
document.addEventListener('DOMContentLoaded', function () {
    SikapApp.init();

    // Existing search functionality with debounce
    const searchInput = document.getElementById('search-input');
    const filterForm = document.getElementById('form-filter');

    if (searchInput && filterForm) {
        let timeout = null;

        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);

            timeout = setTimeout(() => {
                // Determine if we should submit via AJAX or normal form submit
                if(filterForm.hasAttribute('data-ajax')) {
                    // Trigger jQuery submit if data-ajax exists so AjaxCrud handles it
                    $(filterForm).trigger('submit');
                } else {
                    filterForm.submit();
                }
            }, 500);
        });
        
        if (searchInput.value !== "") {
            searchInput.focus();
            const length = searchInput.value.length;
            searchInput.setSelectionRange(length, length);
        }
    }
});