<template>
    <svg
        :class="colorClass"
        :width="width"
        :height="height"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            d="M12 5V19M5 12H19"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>

<script setup>
defineProps({
    colorClass: {
        type: String,
        default: 'text-primary'
    },
    width: {
        type: [String, Number],
        default: 20
    },
    height: {
        type: [String, Number],
        default: 20
    }
});
</script>
