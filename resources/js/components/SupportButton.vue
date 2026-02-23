<template>
  <button class="support-btn" @click="goToSupport" :title="$t('Customer Service')">
    <i class="bi bi-headset"></i>
  </button>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const goToSupport = () => {
  router.push({ name: 'support' })
}
</script>

<style scoped>
.support-btn {
  position: fixed;
  bottom: 20px;
  left: 20px; /* RTL */
  width: 58px;
  height: 58px;
  background-color: #198754;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
  z-index: 9999;
}

.support-btn:hover {
  background-color: #157347;
}
</style>
