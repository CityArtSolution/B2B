<!-- Shows validation messages under each field: server errors after a failed submit, and browser checks before submit. -->
<script>
    (function() {
        const serverErrors = @json($errors->toArray());
        const messages = {
            valueMissing: @json(__('This field is required.')),
            typeMismatch: @json(__('Please enter a valid value.')),
            patternMismatch: @json(__('Please enter a valid value.')),
            tooLong: @json(__('This value is too long.')),
            tooShort: @json(__('This value is too short.')),
            rangeUnderflow: @json(__('This value is too small.')),
            rangeOverflow: @json(__('This value is too large.')),
            stepMismatch: @json(__('Please enter a valid value.')),
            badInput: @json(__('Please enter a valid value.')),
        };

        // "color.new-1.code" -> "color[new-1][code]"
        const toFieldName = (key) => {
            const parts = key.split('.');
            return parts[0] + parts.slice(1).map((part) => `[${part}]`).join('');
        };

        const findField = (key) => {
            const name = toFieldName(key);
            const base = key.split('.')[0];
            return document.querySelector(`[data-error-for="${key}"]`) ||
                document.querySelector(`[name="${CSS.escape(name)}"]`) ||
                document.querySelector(`[name="${CSS.escape(name)}[]"]`) ||
                document.querySelector(`[data-error-for="${base}"]`);
        };

        // the element the message goes after (select2 hides the real select)
        const anchorOf = (field) => {
            const next = field.nextElementSibling;
            if (next && next.classList.contains('select2')) return next;
            if (field.closest('.input-group')) return field.closest('.input-group');
            return field;
        };

        const showError = (field, message) => {
            field.classList.add('is-invalid');
            const anchor = anchorOf(field);
            anchor.classList.add('is-invalid');

            const container = anchor.parentElement;
            const existing = [...container.querySelectorAll('.text-danger, .invalid-feedback, .field-error')]
                .find((el) => el.textContent.trim() === message);
            if (existing) return;

            let box = container.querySelector(`.field-error[data-for="${field.name}"]`);
            if (!box) {
                box = document.createElement('p');
                box.className = 'field-error text-danger m-0';
                box.dataset.for = field.name;
                anchor.insertAdjacentElement('afterend', box);
            }
            box.textContent = message;
        };

        const clearError = (field) => {
            field.classList.remove('is-invalid');
            anchorOf(field).classList.remove('is-invalid');
            anchorOf(field).parentElement?.querySelectorAll(`.field-error[data-for="${field.name}"]`).forEach((el) => el.remove());
        };

        document.addEventListener('DOMContentLoaded', function() {
            const unmatched = [];
            let first = null;

            Object.entries(serverErrors).forEach(([key, list]) => {
                const field = findField(key);
                if (!field) {
                    unmatched.push(list[0]);
                    return;
                }
                showError(field, list[0]);
                first ??= anchorOf(field);
            });

            if (unmatched.length && window.toastr) {
                unmatched.forEach((message) => toastr.error(message));
            }
            first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        // browser validation: replace the native bubble with a message under the field
        document.addEventListener('invalid', function(e) {
            const field = e.target;
            e.preventDefault();

            const reason = Object.keys(messages).find((key) => field.validity[key]);
            showError(field, reason ? messages[reason] : field.validationMessage);

            const form = field.form;
            if (form && form.querySelector(':invalid') === field) {
                anchorOf(field).scrollIntoView({ behavior: 'smooth', block: 'center' });
                field.focus({ preventScroll: true });
            }
        }, true);

        ['input', 'change'].forEach((type) => {
            document.addEventListener(type, function(e) {
                const field = e.target;
                if (field.classList?.contains('is-invalid') && field.checkValidity?.()) clearError(field);
            }, true);
        });
        // select2 fires change through jQuery
        window.jQuery && jQuery(document).on('change', 'select', function() {
            if (this.checkValidity()) clearError(this);
        });
    })();
</script>
