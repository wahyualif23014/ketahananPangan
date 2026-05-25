import { Storage } from '../utils/Storage';
import { debounce } from '../utils/Debounce';
import { Logger } from '../utils/Logger';

/**
 * FormDraft Engine
 * Automatically saves form drafts to sessionStorage while typing
 */
export const FormDraft = {
    init() {
        Logger.info('Initializing FormDraft Engine...');
        this.bindEvents();
        this.restoreDrafts();
    },

    bindEvents() {
        // Only target forms with data-draft="true" or specific forms if needed
        // Here we target forms inside the main-content that have data-draft attribute
        $(document).on('input change', 'form[data-draft="true"] input, form[data-draft="true"] textarea, form[data-draft="true"] select', debounce(function (e) {
            // Ignore hidden inputs like _token
            if (this.type === 'hidden' || this.type === 'password' || this.type === 'file') return;

            const $form = $(this).closest('form');
            const formId = $form.attr('id') || $form.attr('action'); // Fallback to action if no ID
            
            if (!formId) return;

            const name = $(this).attr('name');
            if (!name) return;

            const value = $(this).val();
            const draftKey = `draft_${formId}_${name}`;

            Storage.set(draftKey, value, 'session');
            Logger.debug(`Saved draft for ${name}`);
        }, 1000));

        // Clear draft on successful submit
        $(document).on('submit', 'form[data-draft="true"]', function() {
            const formId = $(this).attr('id') || $(this).attr('action');
            if (!formId) return;
            
            // Collect all inputs to clear their draft
            $(this).find('input, textarea, select').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    Storage.remove(`draft_${formId}_${name}`, 'session');
                }
            });
            Logger.debug(`Cleared draft for form ${formId}`);
        });
    },

    restoreDrafts() {
        $('form[data-draft="true"]').each(function() {
            const formId = $(this).attr('id') || $(this).attr('action');
            if (!formId) return;

            $(this).find('input, textarea, select').each(function() {
                if (this.type === 'hidden' || this.type === 'password' || this.type === 'file') return;

                const name = $(this).attr('name');
                if (!name) return;

                const draftKey = `draft_${formId}_${name}`;
                const savedValue = Storage.get(draftKey, 'session');

                // Only restore if current value is empty and savedValue exists
                if (savedValue !== null && savedValue !== undefined && !$(this).val()) {
                    $(this).val(savedValue);
                    // trigger change for any dependent UI like Alpine or Select2
                    $(this).trigger('change');
                }
            });
        });
    }
};
