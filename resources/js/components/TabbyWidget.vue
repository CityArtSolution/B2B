<template>
    <div v-if="config && amount > 0" ref="holder" class="tabby-widget my-3"></div>
</template>

<script setup>
// Tabby's snippets require only public data. The secret key is never sent to
// the browser and remains exclusively in the server-side payment flow.
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useMaster } from '../stores/MasterStore';

const props = defineProps({
    amount: { type: [Number, String], default: 0 },
    source: { type: String, default: 'product' },
    card: { type: Boolean, default: false },
});

const master = useMaster();
const config = computed(() => master.tabbyWidget);
const amount = computed(() => Math.round((parseFloat(props.amount) || 0) * 100) / 100);
const holder = ref(null);
const scriptUrl = computed(() => props.card
    ? 'https://checkout.tabby.ai/tabby-card.js'
    : 'https://checkout.tabby.ai/tabby-promo.js');

const loadScript = () => new Promise((resolve, reject) => {
    const existing = document.querySelector(`script[src="${scriptUrl.value}"]`);
    if (existing) {
        if (existing.dataset.loaded === 'true') resolve();
        else existing.addEventListener('load', resolve, { once: true });
        return;
    }

    const script = document.createElement('script');
    script.src = scriptUrl.value;
    script.async = true;
    script.addEventListener('load', () => {
        script.dataset.loaded = 'true';
        resolve();
    }, { once: true });
    script.addEventListener('error', reject, { once: true });
    document.head.appendChild(script);
});

const render = async () => {
    await nextTick();
    if (!config.value || !holder.value || amount.value <= 0) return;

    try {
        await loadScript();
        // Use a generated class selector, never an ID named TabbyPromo/Card.
        // Named IDs would shadow the global constructor in some browsers.
        const selector = `.tabby-${props.card ? 'card' : 'promo'}-${Math.random().toString(36).slice(2)}`;
        holder.value.replaceChildren();
        const target = document.createElement('div');
        target.className = selector.slice(1);
        holder.value.appendChild(target);

        const options = {
            selector,
            currency: config.value.currency,
            price: amount.value.toFixed(2),
            lang: master.locale === 'en' ? 'en' : 'ar',
            shouldInheritBg: false,
            publicKey: config.value.public_key,
            merchantCode: config.value.merchant_code,
        };
        if (!props.card) options.source = props.source;

        const Constructor = props.card ? window.TabbyCard : window.TabbyPromo;
        if (typeof Constructor === 'function') new Constructor(options);
    } catch (_) {
        // A blocked optional promo script must not interrupt shopping or checkout.
    }
};

onMounted(render);
watch([amount, config, () => master.locale, () => props.card, () => props.source], render, { deep: true });
</script>

<style scoped>
.tabby-widget { min-width: 0; width: 100%; }
</style>
