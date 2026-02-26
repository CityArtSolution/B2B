<template>
    <transition name="zoom-fade">
        <div v-if="visible" class="overlay">
            <div class="modal-card">
                
                <!-- Product Image -->
                <div class="image-side">
                    <img :src="product.image" :alt="product.name" />
                </div>

                <!-- Content -->
                <div class="content-side">
                    <span class="discount-badge">
                        {{ $t('Special Discount') }}
                    </span>

                    <h2>{{ product.name }}</h2>

                    <div class="coupon-box">
                        <span>{{ couponCode }}</span>
                        <button @click="copyCode">
                            {{ copied ? $t('Copied') : $t('Copy Code') }}
                        </button>
                    </div>

                    <small class="hint">
                        {{ $t('Use this code before it expires') }}
                    </small>
                </div>

            </div>
        </div>
    </transition>
</template>

<script setup>
import { onMounted, ref } from "vue";

const props = defineProps({
    couponCode: String,
    recordId: Number,
    product: Object
});

const visible = ref(false);
const copied = ref(false);

onMounted(() => {
        const key = `big_coupon_shown_${props.recordId}`;
    if (!localStorage.getItem(key)) {
        visible.value = true;
        localStorage.setItem(key, '1');

        setTimeout(() => {
            visible.value = false;
        }, 7000);
    }
});

const copyCode = () => {
    if (!props.couponCode) return;
    navigator.clipboard.writeText(props.couponCode)
        .then(() => {
            copied.value = true;
            setTimeout(() => copied.value = false, 2000); 
        });
};

</script>

<style scoped>
/* Overlay */
.overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

/* Card */
.modal-card {
    width: 70%;
    max-width: 900px;
    background: #fff;
    border-radius: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,.4);
}

/* Image */
.image-side img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Content */
.content-side {
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.discount-badge {
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: #fff;
    padding: 6px 14px;
    border-radius: 30px;
    width: fit-content;
    margin-bottom: 15px;
    font-weight: bold;
}

.content-side h2 {
    font-size: 26px;
    margin-bottom: 10px;
}

.desc {
    color: #555;
    margin-bottom: 25px;
    line-height: 1.6;
}

/* Coupon */
.coupon-box {
    display: flex;
    gap: 15px;
    align-items: center;
    background: #f4fdf7;
    border: 2px dashed #22c55e;
    padding: 15px;
    border-radius: 12px;
}

.coupon-box span {
    font-size: 20px;
    font-weight: bold;
    letter-spacing: 2px;
}

.coupon-box button {
    background: #22c55e;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
}

.hint {
    margin-top: 12px;
    color: #777;
}

/* Animation */
.zoom-fade-enter-active,
.zoom-fade-leave-active {
    transition: all .4s ease;
}

.zoom-fade-enter-from,
.zoom-fade-leave-to {
    opacity: 0;
    transform: scale(.85);
}

/* Responsive */
@media (max-width: 768px) {
    .modal-card {
        grid-template-columns: 1fr;
        width: 90%;
    }
}
</style>
