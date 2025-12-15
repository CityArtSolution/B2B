<template>
    <div :class="colorClass">
        <svg width="23px" height="24px"  viewBox="0 0 24.00 24.00" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#000000" transform="rotate(0)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.048"></g><g id="SVGRepo_iconCarrier"> <path d="M6.29977 5H21L19 12H7.37671M20 16H8L6 3H3M9 20C9 20.5523 8.55228 21 8 21C7.44772 21 7 20.5523 7 20C7 19.4477 7.44772 19 8 19C8.55228 19 9 19.4477 9 20ZM20 20C20 20.5523 19.5523 21 19 21C18.4477 21 18 20.5523 18 20C18 19.4477 18.4477 19 19 19C19.5523 19 20 19.4477 20 20Z" stroke="#78a656" stroke-width="1.392" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
    </div>
</template>

<script setup>

const props = defineProps({
    colorClass: {
        type: String,
        default: 'text-primary'
    },
    width: {
        type: String,
        default: '20'
    },
    height: {
        type: String,
        default: '20'
    }
});
</script>
