<template>
    <div class="p-4 bg-gray-50 rounded-lg border">
        <h3 class="text-lg font-semibold mb-3">Branch Storage Test</h3>

        <div class="space-y-2 text-sm">
            <div>
                <strong>Selected Branch (Store):</strong>
                {{ authStore.selectedBranch ? `${authStore.selectedBranch.name} (ID: ${authStore.selectedBranch.id})` : 'None' }}
            </div>

            <div>
                <strong>SessionStorage:</strong>
                {{ sessionStorageData ? JSON.stringify(sessionStorageData, null, 2) : 'Empty' }}
            </div>

            <div>
                <strong>LocalStorage:</strong>
                {{ localStorageData ? JSON.stringify(localStorageData, null, 2) : 'Empty' }}
            </div>

            <div class="pt-2">
                <button
                    @click="clearSessionStorage"
                    class="px-3 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600"
                >
                    Clear SessionStorage
                </button>
                <button
                    @click="testPageRefresh"
                    class="ml-2 px-3 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600"
                >
                    Test Refresh
                </button>
            </div>

            <div class="text-xs text-gray-500 mt-2">
                <p>✅ Selected branch persists across page refreshes</p>
                <p>❌ Selected branch clears when browser tab/window is closed</p>
                <p>🔄 Selected branch does NOT persist across different browser tabs</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuth } from '../stores/AuthStore'

const authStore = useAuth()

const sessionStorageData = ref(null)
const localStorageData = ref(null)

const updateStorageData = () => {
    if (typeof window !== 'undefined') {
        const sessionData = sessionStorage.getItem('selectedBranch')
        const localData = localStorage.getItem('selectedBranch')

        sessionStorageData.value = sessionData ? JSON.parse(sessionData) : null
        localStorageData.value = localData ? JSON.parse(localData) : null
    }
}

const clearSessionStorage = () => {
    if (typeof window !== 'undefined') {
        sessionStorage.removeItem('selectedBranch')
        authStore.setSelectedBranch(null)
        updateStorageData()
    }
}

const testPageRefresh = () => {
    // This will test persistence across refresh
    console.log('Refreshing page to test sessionStorage persistence...')
    window.location.reload()
}

onMounted(() => {
    updateStorageData()

    // Update display when auth store changes
    watch(() => authStore.selectedBranch, updateStorageData, { immediate: true })
})
</script>
