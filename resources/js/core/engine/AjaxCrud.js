import { Logger } from '../utils/Logger';
import { StateManager } from './StateManager';
import { Navigation } from './Navigation';

/**
 * AjaxCrud Engine
 * Handles generic AJAX submission for forms and click actions 
 * and performs partial DOM updates without full page reloads.
 */
export const AjaxCrud = {
    init() {
        Logger.info('Initializing AjaxCrud Engine...');
        this.bindAjaxForms();
        this.bindAjaxLinks();
    },

    bindAjaxForms() {
        // Forms that have data-ajax="true" will be intercepted
        $(document).off('submit', 'form[data-ajax="true"]').on('submit', 'form[data-ajax="true"]', function (e) {
            e.preventDefault();
            const $form = $(this);
            const url = $form.attr('action');
            const method = ($form.attr('method') || 'POST').toUpperCase();
            
            // Create FormData object (supports files)
            const formData = new FormData(this);

            const targetSelector = $form.data('target') || '#main-content'; // Default update target
            const $target = $(targetSelector);

            // Show skeleton loader or disable button
            const $submitBtn = $form.find('[type="submit"]');
            const originalBtnText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            
            // Use NProgress if available
            if (typeof NProgress !== 'undefined') NProgress.start();

            axios({
                method: method,
                url: url,
                data: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(response => {
                AjaxCrud.handleResponse(response, $target);
                
                // If it's a modal form, hide it
                const $modal = $form.closest('.modal');
                if ($modal.length && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = bootstrap.Modal.getInstance($modal[0]);
                    if (bsModal) bsModal.hide();
                }

                // Show success toast (using global Alpine $notify if present, or custom)
                if (window.$notify) {
                    window.$notify('success', 'Berhasil', response.data.message || 'Data berhasil disimpan.');
                }
            }).catch(error => {
                Logger.error('AJAX Form Submit Error', error);
                
                // Show error toast
                const errorMsg = error.response?.data?.message || 'Terjadi kesalahan sistem.';
                if (window.$notify) {
                    window.$notify('error', 'Gagal', errorMsg);
                }

                // Handle validation errors
                if (error.response?.status === 422) {
                    AjaxCrud.displayValidationErrors($form, error.response.data.errors);
                }
            }).finally(() => {
                // Restore button
                $submitBtn.prop('disabled', false).html(originalBtnText);
                if (typeof NProgress !== 'undefined') NProgress.done();
            });
        });
    },

    bindAjaxLinks() {
        // Links with data-ajax="true" (e.g. Delete, pagination, filter)
        $(document).off('click', 'a[data-ajax="true"]').on('click', 'a[data-ajax="true"]', function (e) {
            e.preventDefault();
            
            const url = $(this).attr('href');
            if (!url || url === '#') return;

            const targetSelector = $(this).data('target') || '#main-content';
            const $target = $(targetSelector);

            // Confirm dialog integration
            const confirmMsg = $(this).data('confirm');
            if (confirmMsg) {
                if (window.$confirm) {
                    window.$confirm({
                        title: 'Konfirmasi',
                        message: confirmMsg,
                        type: $(this).data('confirm-type') || 'warning'
                    }).then(ok => {
                        if (ok) AjaxCrud.executeGet(url, $target);
                    });
                    return;
                } else if (!confirm(confirmMsg)) {
                    return;
                }
            }

            AjaxCrud.executeGet(url, $target);
        });
    },

    executeGet(url, $target) {
        if (typeof NProgress !== 'undefined') NProgress.start();
        // Add a loading class to target
        $target.css('opacity', '0.5');

        axios.get(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                AjaxCrud.handleResponse(response, $target);
                // Update History API so URL changes
                window.history.pushState({ path: url }, '', url);
            })
            .catch(error => {
                Logger.error('AJAX Link Error', error);
                if (window.$notify) {
                    window.$notify('error', 'Gagal', 'Tidak dapat memuat data.');
                }
            })
            .finally(() => {
                $target.css('opacity', '1');
                if (typeof NProgress !== 'undefined') NProgress.done();
            });
    },

    handleResponse(response, $target) {
        // Determine if response is JSON with HTML payload or raw HTML
        let htmlContent = '';
        if (response.data && response.data.html) {
            htmlContent = response.data.html;
        } else if (typeof response.data === 'string') {
            htmlContent = response.data;
        }

        if (htmlContent) {
            // Check if we need to extract a specific part of the returned HTML
            // (e.g., if server returned full page instead of partial)
            if (htmlContent.indexOf('<html') !== -1) {
                const parsedHtml = $(htmlContent).find($target.selector);
                if (parsedHtml.length) {
                    htmlContent = parsedHtml.html();
                }
            }

            $target.html(htmlContent);
            
            // Re-initialize state and navigation to apply to new DOM
            StateManager.init();
            Navigation.initActiveMenuPersistence();
            
            // Trigger custom event for other scripts to re-bind (e.g. DataTables)
            $(document).trigger('sikap:ajax:updated', [$target]);
        }
    },

    displayValidationErrors($form, errors) {
        // Clear previous errors
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        for (const [field, messages] of Object.entries(errors)) {
            const $input = $form.find(`[name="${field}"]`);
            if ($input.length) {
                $input.addClass('is-invalid');
                $input.after(`<div class="invalid-feedback text-red-500 text-xs mt-1">${messages[0]}</div>`);
            }
        }
    }
};
