<!-- Product form helpers: carton price = piece price x carton pack, the review step, and an auto-saved draft (add form only). -->
<script>
    // runs after select2, the editors and the step engine are ready
    document.addEventListener('DOMContentLoaded', () => setTimeout(function() {
        const form = document.querySelector('form.form-steps');
        if (!form) return;

        const t = {
            notSet: @json(__('Not entered')),
            edit: @json(__('Edit')),
            names: @json(__('Product Names')),
            details: @json(__('Category and Details')),
            pricing: @json(__('Price and Stock')),
            images: @json(__('Images')),
            nameAr: @json(__('Product Name (AR)')),
            nameEn: @json(__('Product Name (EN)')),
            shortAr: @json(__('Short Description (AR)')),
            shortEn: @json(__('Short Description (EN)')),
            branches: @json(__('Branches')),
            category: @json(__('Category')),
            subCategories: @json(__('Sub Categories')),
            brand: @json(__('Brand')),
            code: @json(__('Product SKU')),
            colors: @json(__('Colors')),
            sizes: @json(__('Sizes')),
            buyPrice: @json(__('Buying Price')),
            price: @json(__('Selling Price')),
            discount: @json(__('Carton price after discount')),
            pack: @json(__('Units Per Carton')),
            cartonPrice: @json(__('Carton Price')),
            minOrder: @json(__('Minimum Order Quantity')),
            cartons: @json(__('cartons')),
            pieces: @json(__('pieces')),
            thumbnail: @json(__('Thumbnail')),
            extraImages: @json(__('Additional images')),
            cartonHint: @json(__('Calculated automatically: piece price × carton pack. You can change it.')),
            draftFound: @json(__('You have an unsaved draft of this product from')),
            restore: @json(__('Restore draft')),
            discard: @json(__('Discard')),
            draftSaved: @json(__('Draft saved')),
        };

        const $field = (name) => form.querySelector(`[name="${name}"]`);
        const val = (name) => ($field(name)?.value ?? '').trim();
        const selectedText = (select) => select ? [...select.selectedOptions].filter((o) => o.value).map((o) => o.textContent.trim()) : [];
        const num = (v) => parseFloat(v) || 0;

        // ---------- 1. carton price = piece price x pack ----------
        const priceInput = $field('price');
        const discountInput = $field('discount_price');
        const packInput = $field('carton_units_count');
        const cartonInput = $field('carton_price');

        if (priceInput && packInput && cartonInput) {
            const hint = document.createElement('small');
            hint.className = 'text-muted d-block mt-1 carton-hint';
            hint.textContent = t.cartonHint;
            cartonInput.insertAdjacentElement('afterend', hint);

            // keep the user's own carton price once they type one that differs from the calculation
            const calculated = () => {
                const piece = num(priceInput.value);
                const pack = parseInt(packInput.value) || 0;
                return pack > 0 && piece > 0 ? (piece * pack).toFixed(2) : '';
            };
            let manual = cartonInput.value !== '' && cartonInput.value !== calculated();

            cartonInput.addEventListener('input', () => manual = cartonInput.value !== '');
            [priceInput, packInput].forEach((input) => input?.addEventListener('input', () => {
                if (manual) return;
                cartonInput.value = calculated();
                cartonInput.dispatchEvent(new Event('change', { bubbles: true }));
            }));
        }

        // ---------- 2. review step ----------
        const reviewBox = form.querySelector('[data-product-review]');

        const escape = (value) => {
            const div = document.createElement('div');
            div.textContent = value;
            return div.innerHTML;
        };

        const row = (label, value, required = false) => {
            const empty = value === '' || value == null || (Array.isArray(value) && !value.length);
            const shown = empty ? `<span class="${required ? 'text-danger' : 'text-muted'}">${t.notSet}</span>` :
                escape(Array.isArray(value) ? value.join('، ') : value);
            return `<div class="review-row"><span class="review-label">${escape(label)}</span><span class="review-value">${shown}</span></div>`;
        };

        const section = (title, anchorName, rows) => {
            const target = $field(anchorName) || form.querySelector(`[data-error-for="${anchorName}"]`);
            const stepIndex = target && form.formSteps ? form.formSteps.stepOf(target) : -1;
            const editBtn = stepIndex >= 0 ? `<button type="button" class="btn btn-sm btn-outline-primary review-edit" data-step="${stepIndex}"><i class="bi bi-pencil"></i> ${t.edit}</button>` : '';
            return `<div class="review-section"><div class="review-section-head"><h6>${escape(title)}</h6>${editBtn}</div>${rows.join('')}</div>`;
        };

        const renderReview = () => {
            const branchSelect = form.querySelector('.BranchSelect');
            const branches = selectedText(branchSelect).map((name, i) => {
                const id = [...branchSelect.selectedOptions].filter((o) => o.value)[i].value;
                return `${name}: ${val(`branches[${id}]`) || 0} ${t.cartons}`;
            });
            const pack = val('carton_units_count');
            const thumb = form.querySelector('#preview, label[for="thumbnail"] img');
            const hasThumb = thumb && !/placehold\.co|upload\.png/.test(thumb.getAttribute('src') || '');
            const extra = [...form.querySelectorAll('input[name="additionThumbnail[]"]')].filter((i) => i.files?.length).length +
                form.querySelectorAll('input[name^="previousThumbnail"][name$="[id]"]').length;

            reviewBox.innerHTML = `
                <div class="review-grid">
                    ${section(t.names, 'name', [row(t.nameAr, val('name_ar'), true), row(t.nameEn, val('name'), true), row(t.shortAr, val('short_description_ar')), row(t.shortEn, val('short_description'), true)])}
                    ${section(t.details, 'category', [row(t.branches, branches, true), row(t.category, selectedText($field('category')), true), row(t.subCategories, selectedText($field('sub_category[]'))), row(t.brand, selectedText($field('brand'))), row(t.code, val('code'), true), row(t.colors, selectedText($field('colorIds[]'))), row(t.sizes, selectedText($field('sizeIds[]')))])}
                    ${section(t.pricing, 'price', [row(t.buyPrice, val('buy_price'), true), row(t.price, val('price'), true), row(t.discount, num(val('discount_price')) > 0 ? val('discount_price') : ''), row(t.pack, pack ? `${pack} ${t.pieces}` : ''), row(t.cartonPrice, val('carton_price')), row(t.minOrder, val('min_order_quantity') ? `${val('min_order_quantity')} ${t.cartons}` : '')])}
                    <div class="review-section">
                        <div class="review-section-head"><h6>${escape(t.images)}</h6></div>
                        <div class="d-flex align-items-center gap-3">
                            ${hasThumb ? `<img src="${thumb.getAttribute('src')}" class="review-thumb" alt="">` : `<span class="text-danger">${t.thumbnail}: ${t.notSet}</span>`}
                            <span class="text-muted">${t.extraImages}: ${extra}</span>
                        </div>
                    </div>
                </div>`;
        };

        if (reviewBox) {
            form.addEventListener('form-step:shown', (e) => {
                if (e.detail.step.contains(reviewBox)) renderReview();
            });
            reviewBox.addEventListener('click', (e) => {
                const btn = e.target.closest('.review-edit');
                if (btn) form.formSteps.goTo(parseInt(btn.dataset.step));
            });
        }

        // ---------- 3. auto-saved draft (add product only) ----------
        if (!form.hasAttribute('data-draft')) return;
        const key = 'product-draft:' + form.getAttribute('data-draft');
        const skip = (el) => !el.name || ['_token', '_method'].includes(el.name) || ['file', 'password', 'submit', 'button'].includes(el.type);
        const storage = {
            get: () => { try { return JSON.parse(localStorage.getItem(key)); } catch (e) { return null; } },
            set: (v) => { try { localStorage.setItem(key, JSON.stringify(v)); } catch (e) {} },
            clear: () => { try { localStorage.removeItem(key); } catch (e) {} },
        };

        const collect = () => {
            const data = {};
            [...form.elements].forEach((el) => {
                if (skip(el)) return;
                if (el.type === 'checkbox' || el.type === 'radio') {
                    if (el.checked) data[el.name] = el.value;
                } else if (el.multiple) {
                    data[el.name] = [...el.selectedOptions].map((o) => o.value);
                } else {
                    data[el.name] = el.value;
                }
            });
            // the branch select has no name; keep its choice too
            const branchSelect = form.querySelector('.BranchSelect');
            if (branchSelect) data.__branches = [...branchSelect.selectedOptions].map((o) => o.value);
            return data;
        };

        let saveTimer;
        const save = () => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => storage.set({ savedAt: Date.now(), data: collect() }), 800);
        };

        const setSelect = (select, values) => {
            const list = Array.isArray(values) ? values : [values];
            [...select.options].forEach((o) => o.selected = list.includes(o.value));
            window.jQuery ? jQuery(select).trigger('change') : select.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const restore = (data) => {
            const branchSelect = form.querySelector('.BranchSelect');
            if (branchSelect && data.__branches) setSelect(branchSelect, data.__branches);
            // selects first: they create the quantity / color / size rows
            Object.entries(data).forEach(([name, value]) => {
                const el = $field(name);
                if (el && el.tagName === 'SELECT' && name !== 'sub_category[]') setSelect(el, value);
            });
            // sub categories load by ajax after the category changes
            setTimeout(() => {
                const sub = $field('sub_category[]');
                if (sub && data['sub_category[]']) setSelect(sub, data['sub_category[]']);
            }, 1200);
            setTimeout(() => {
                Object.entries(data).forEach(([name, value]) => {
                    const el = $field(name);
                    if (!el || el.tagName === 'SELECT') return;
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        form.querySelectorAll(`[name="${name}"]`).forEach((i) => i.checked = i.value === value);
                        return;
                    }
                    el.value = value;
                    // rich text editors keep their html in a hidden input placed right after them
                    const editor = el.previousElementSibling;
                    if (el.type === 'hidden' && editor?.classList.contains('ql-container') && window.Quill) {
                        Quill.find(editor)?.clipboard.dangerouslyPasteHTML(value || '');
                    }
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                });
            }, 50);
        };

        const draft = storage.get();
        const hasServerErrors = !!document.querySelector('.field-error, .is-invalid');
        if (draft?.data && !hasServerErrors) {
            const bar = document.createElement('div');
            bar.className = 'alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2 draft-bar';
            bar.innerHTML = `<span><i class="bi bi-clock-history"></i> ${t.draftFound} <strong></strong></span>
                <span class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary" data-restore>${t.restore}</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-discard>${t.discard}</button>
                </span>`;
            bar.querySelector('strong').textContent = new Date(draft.savedAt).toLocaleString(document.documentElement.lang || undefined);
            form.prepend(bar);
            bar.querySelector('[data-restore]').addEventListener('click', () => {
                restore(draft.data);
                bar.remove();
            });
            bar.querySelector('[data-discard]').addEventListener('click', () => {
                storage.clear();
                bar.remove();
            });
        }

        ['input', 'change'].forEach((type) => form.addEventListener(type, save));
        window.jQuery && jQuery(form).on('change', 'select', save);
        // quill does not fire input events on the form
        form.querySelectorAll('.ql-editor').forEach((el) => new MutationObserver(save).observe(el, { childList: true, subtree: true, characterData: true }));
        form.addEventListener('submit', () => storage.clear());
    }, 20));
</script>
