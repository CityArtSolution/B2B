<template>
    <div v-if="config && amount > 0" ref="holder" class="tamara-widget-holder my-3"></div>
</template>

<script setup>
// Tamara promo widget ("split into 4 payments"), shown on the product and cart pages while Tamara is active.
// The <tamara-widget> custom element is created by hand so Vue does not try to compile it.
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useMaster } from '../stores/MasterStore';

const props = defineProps({
    amount: { type: [Number, String], default: 0 },
    inlineType: { type: String, default: '2' },
});

const master = useMaster();
const config = computed(() => master.tamaraWidget);
const amount = computed(() => Math.round((parseFloat(props.amount) || 0) * 100) / 100);
const holder = ref(null);

const loadScript = () => {
    const src = config.value.sandbox
        ? 'https://cdn-sandbox.tamara.co/widget-v2/tamara-widget.js'
        : 'https://cdn.tamara.co/widget-v2/tamara-widget.js';

    // The script reads this config once, when it loads
    window.tamaraWidgetConfig = {
        lang: master.locale === 'en' ? 'en' : 'ar',
        country: config.value.country,
        publicKey: config.value.public_key,
    };

    if (!document.querySelector(`script[src="${src}"]`)) {
        const script = document.createElement('script');
        script.src = src;
        script.defer = true;
        document.head.appendChild(script);
    }
};

const render = async () => {
    await nextTick();
    if (!config.value || !holder.value || amount.value <= 0) {
        return;
    }
    if (!window.tamaraWidgetConfig) {
        loadScript();
    }

    let widget = holder.value.querySelector('tamara-widget');
    if (!widget) {
        widget = document.createElement('tamara-widget');
        widget.setAttribute('type', 'tamara-summary');
        widget.setAttribute('inline-type', props.inlineType);
        holder.value.appendChild(widget);
    }
    widget.setAttribute('amount', String(amount.value));
    window.TamaraWidgetV2?.refresh();
};

onMounted(render);
watch([amount, config], render);
</script>
