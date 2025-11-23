<template>
    <div>
        <!-- Branch Selection Modal -->
        <TransitionRoot as="template" :show="showModal">
            <Dialog as="div" class="relative z-10" :static="props.required" @close="closeModal()">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                    enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity" />
                </TransitionChild>
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full justify-center p-4 text-center items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all my-8 md:my-0 w-full sm:max-w-lg md:max-w-xl">
                                <div class="bg-white p-5 sm:p-8 relative" :class="master.langDirection==='rtl' ? 'text-right' : 'text-left'">
                                    <!-- close button -->
                                    <div v-if="!props.required" class="w-9 h-9 bg-slate-100 rounded-[32px] absolute top-4 flex justify-center items-center cursor-pointer" :class="master.langDirection==='rtl' ? 'left-4' : 'right-4'" @click="closeModal()">
                                        <XMarkIcon class="w-6 h-6 text-slate-600" />
                                    </div>
                                    <!-- end close button -->

                                    <div class="text-slate-950 text-lg sm:text-2xl font-medium leading-loose">
                                        {{ $t('Select Branch') }}
                                    </div>

                                    <div class="text-slate-950 text-lg font-normal leading-7 tracking-tight mt-3">
                                        <span v-if="props.required" class="text-red-600">*</span>
                                        {{ props.required ? $t('Branch selection is required to continue shopping') : $t('Please select a branch to continue shopping') }}
                                    </div>

                                    <!-- Loading state -->
                                    <div v-if="loading" class="mt-6 text-center">
                                        <LoadingSpin />
                                        <p class="mt-2 text-gray-600">{{ $t('Loading branches...') }}</p>
                                    </div>

                                    <!-- Branches list -->
                                    <div v-else-if="branches.length > 0" class="mt-6">
                                        <div class="space-y-3">
                                            <div
                                                v-for="branch in branches"
                                                :key="branch.id"
                                                @click="selectBranch(branch)"
                                                class="border rounded-lg p-4 cursor-pointer transition-all duration-200 hover:border-primary hover:bg-primary/5"
                                                :class="selectedBranchId === branch.id ? 'border-primary bg-primary/10' : 'border-gray-200'"
                                            >
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <h3 class="font-medium text-gray-900">{{ branch.name }}</h3>
                                                        <p class="text-sm text-gray-600 mt-1">{{ branch.address }}</p>
                                                        <p v-if="branch.phone" class="text-sm text-gray-500 mt-1">{{ branch.phone }}</p>
                                                    </div>
                                                    <div v-if="selectedBranchId === branch.id" class="ml-3">
                                                        <CheckCircleIcon class="w-6 h-6 text-primary" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button
                                            @click="confirmSelection()"
                                            :disabled="!selectedBranchId"
                                            class="w-full mt-6 px-4 py-3 bg-primary text-white rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary/90 transition-colors"
                                        >
                                            {{ $t('Continue Shopping') }}
                                        </button>
                                    </div>

                                    <!-- No branches message -->
                                    <div v-else class="mt-6 text-center">
                                        <p class="text-gray-600">{{ $t('No branches available at the moment.') }}</p>
                                        <button
                                            v-if="!props.required"
                                            @click="closeModal()"
                                            class="mt-4 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
                                        >
                                            {{ $t('Close') }}
                                        </button>
                                        <p v-else class="mt-4 text-sm text-gray-500">
                                            {{ $t('Please contact support if no branches are available.') }}
                                        </p>
                                    </div>

                                    <!-- Error message -->
                                    <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                        <p class="text-red-600 text-sm">{{ error }}</p>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { XMarkIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import { useAuth } from '../stores/AuthStore';
import { useMaster } from '../stores/MasterStore';
import LoadingSpin from './LoadingSpin.vue';

const AuthStore = useAuth();
const master = useMaster();

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    required: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'branch-selected']);

const showModal = ref(false);
const loading = ref(false);
const branches = ref([]);
const selectedBranchId = ref(null);
const error = ref(null);

// Watch for prop changes
watch(() => props.show, (newValue) => {
    showModal.value = newValue;
    if (newValue) {
        fetchBranches();
    }
});

const fetchBranches = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get('/branches');
        branches.value = response.data.data.branches || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load branches';
        console.error('Error fetching branches:', err);
    } finally {
        loading.value = false;
    }
};

const selectBranch = (branch) => {
    selectedBranchId.value = branch.id;
};

const confirmSelection = async () => {
    if (!selectedBranchId.value) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post('/select-branch', {
            branch_id: selectedBranchId.value
        }, {
            headers: {
                Authorization: AuthStore.token
            }
        });

        const selectedBranch = branches.value.find(b => b.id === selectedBranchId.value);
        emit('branch-selected', selectedBranch);
        closeModal();
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to select branch';
        console.error('Error selecting branch:', err);
    } finally {
        loading.value = false;
    }
};

const closeModal = () => {
    // Only allow closing if not required or if a branch has been selected
    if (!props.required || selectedBranchId.value) {
        showModal.value = false;
        emit('close');
    }
};

// Initialize
onMounted(() => {
    showModal.value = props.show;
    if (props.show) {
        fetchBranches();
    }
});
</script>

<style scoped>
/* Additional styles if needed */
</style>
