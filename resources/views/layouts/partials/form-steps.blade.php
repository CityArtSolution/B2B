<!-- Turns long forms into steps: <form class="form-steps"> with <div class="form-step" data-title data-hint data-icon> sections
     and an actions bar marked .form-steps-actions. Fields are checked before moving forward. -->
<script>
    (function() {
        const text = {
            next: @json(__('Next')),
            back: @json(__('Back')),
            step: @json(__('Step')),
            of: @json(__('of')),
        };

        const errorSelector = '.is-invalid, .field-error, p.text-danger';

        const setup = (form) => {
            const steps = [...form.querySelectorAll('.form-step')];
            if (steps.length < 2) return;
            const actions = form.querySelector('.form-steps-actions');
            let current = 0;

            // header inside each step
            steps.forEach((step, i) => {
                const head = document.createElement('div');
                head.className = 'step-head';
                head.innerHTML = `<span class="step-icon"><i class="bi"></i></span><div><span class="step-count"></span><h5></h5><p></p></div>`;
                head.querySelector('i').classList.add(step.dataset.icon || 'bi-pencil');
                head.querySelector('.step-count').textContent = `${text.step} ${i + 1} ${text.of} ${steps.length}`;
                head.querySelector('h5').textContent = step.dataset.title || '';
                head.querySelector('p').textContent = step.dataset.hint || '';
                step.prepend(head);
            });

            // top navigation with progress
            const nav = document.createElement('div');
            nav.className = 'steps-nav';
            nav.innerHTML = '<div class="steps-progress"><span></span></div><div class="steps-list"></div>';
            steps.forEach((step, i) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'steps-item';
                item.innerHTML = '<span class="steps-num"></span><span class="steps-label"></span>';
                item.querySelector('.steps-num').textContent = i + 1;
                item.querySelector('.steps-label').textContent = step.dataset.title || '';
                item.addEventListener('click', () => goTo(i));
                nav.querySelector('.steps-list').appendChild(item);
            });
            const firstCard = steps[0].closest('.card') || steps[0];
            firstCard.parentElement.insertBefore(nav, firstCard);

            // back / next buttons next to the existing submit and reset buttons
            const backBtn = document.createElement('button');
            backBtn.type = 'button';
            backBtn.className = 'btn btn-lg btn-outline-secondary rounded py-2 steps-back';
            const rtl = getComputedStyle(form).direction === 'rtl';
            backBtn.innerHTML = `<i class="bi ${rtl ? 'bi-arrow-right' : 'bi-arrow-left'}"></i> ${text.back}`;
            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'btn btn-lg btn-primary rounded py-2 px-5 steps-next';
            nextBtn.innerHTML = `${text.next} <i class="bi ${rtl ? 'bi-arrow-left' : 'bi-arrow-right'}"></i>`;
            const finalButtons = actions ? [...actions.querySelectorAll('button[type="submit"], button[type="reset"]')] : [];
            if (actions) {
                actions.classList.add('steps-actions');
                actions.prepend(backBtn);
                actions.appendChild(nextBtn);
            }
            backBtn.addEventListener('click', () => goTo(current - 1));
            nextBtn.addEventListener('click', () => goTo(current + 1));

            const stepIsValid = (step) => {
                let valid = true;
                step.querySelectorAll('input, select, textarea').forEach((field) => {
                    if (!field.disabled && field.willValidate && !field.checkValidity()) valid = false;
                });
                return valid;
            };

            const show = (index) => {
                current = index;
                steps.forEach((step, i) => step.classList.toggle('active', i === index));
                nav.querySelectorAll('.steps-item').forEach((item, i) => {
                    item.classList.toggle('active', i === index);
                    item.classList.toggle('done', i < index);
                    item.classList.toggle('has-error', !!steps[i].querySelector(errorSelector));
                });
                nav.querySelector('.steps-progress span').style.width = `${(index / (steps.length - 1)) * 100}%`;
                const last = index === steps.length - 1;
                backBtn.style.display = index === 0 ? 'none' : '';
                nextBtn.style.display = last ? 'none' : '';
                finalButtons.forEach((btn) => btn.style.display = last ? '' : 'none');
                window.jQuery && jQuery(steps[index]).find('select.select2-hidden-accessible').trigger('change.select2');
                form.dispatchEvent(new CustomEvent('form-step:shown', { detail: { index, step: steps[index] } }));
            };

            const goTo = (index) => {
                if (index < 0 || index >= steps.length || index === current) return;
                // moving forward checks every step on the way
                for (let i = current; i < index; i++) {
                    if (!stepIsValid(steps[i])) {
                        show(i);
                        return;
                    }
                }
                show(index);
                nav.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            // lets page scripts jump to a step, e.g. "edit" links on a review step
            form.formSteps = { goTo, stepOf: (el) => steps.findIndex((step) => step.contains(el)) };

            // a field in another step failed the browser check on submit: open that step
            form.addEventListener('invalid', (e) => {
                const index = steps.findIndex((step) => step.contains(e.target));
                if (index !== -1 && index !== current) show(index);
            }, true);

            // Enter moves to the next step instead of submitting early
            form.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' || e.target.tagName === 'TEXTAREA' || e.target.closest('.ql-editor')) return;
                if (current < steps.length - 1 && e.target.closest('.form-step')) {
                    e.preventDefault();
                    goTo(current + 1);
                }
            });

            // after a failed save, start on the first step with an error
            const errorStep = steps.findIndex((step) => step.querySelector(errorSelector));
            show(errorStep === -1 ? 0 : errorStep);
        };

        // run after the inline error messages are placed (form-errors partial)
        document.addEventListener('DOMContentLoaded', () => setTimeout(() => {
            document.querySelectorAll('form.form-steps').forEach(setup);
        }, 0));
    })();
</script>
