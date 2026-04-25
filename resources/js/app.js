import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const filterForm = document.getElementById('form-filter');

    if (searchInput && filterForm) {
        let timeout = null;

        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);

            timeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
        if (searchInput.value !== "") {
            searchInput.focus();
            const length = searchInput.value.length;
            searchInput.setSelectionRange(length, length);
        }
    }
});