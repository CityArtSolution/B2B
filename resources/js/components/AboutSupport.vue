<template>
    <div class="main-container mt-8 sm:mt-12">
        <div v-if="!isLoading" class="p-6 bg-white rounded-xl shadow-lg border border-dashed border-slate-300 grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-8 lg:gap-16">
            <div class="flex justify-start items-start gap-4 p-2">
                <img :src="'/assets/icons/secure-payment.svg'" class="w-11 h-11 shrink-0" loading="lazy" alt="icon"/>
                <div class="text-slate-600 text-sm font-medium leading-tight">
                    <div class="text-slate-950 sm:text-base font-bold leading-normal">
                        {{ $t('Secure Payment Gateways') }}
                    </div>
                    <div class="text-slate-700 sm:text-base font-normal leading-normal mt-0 sm:mt-1">
                        {{ $t('48+ gateways to ensure your security') }}.
                    </div>
                </div>
            </div>

            <div class="flex justify-start items-start gap-4 p-2">
                <img :src="'/assets/icons/customer-review.svg'" class="w-11 h-11 shrink-0" loading="lazy" alt="icon"/>
                <div class="text-slate-600 text-sm font-medium leading-tight">
                    <div class="text-slate-950 sm:text-base font-bold leading-normal">
                        {{ $t('Genuine Customer Reviews') }}
                    </div>
                    <div class="text-slate-700 sm:text-base font-normal leading-normal mt-0 sm:mt-1">
                        {{ $t('Find verified reviews showcased on our platforms') }}.
                    </div>
                </div>
            </div>

            <!-- Interactive 24/7 Customer Support Card -->
            <div
                class="flex justify-start items-start gap-4 p-2 rounded-xl cursor-pointer transition-all duration-200 hover:bg-primary/5 hover:shadow-sm border border-transparent hover:border-primary/20 group"
                @click="openSupport"
                :title="masterStore.langDirection === 'rtl' ? 'اضغط للتواصل المباشر مع خدمة العملاء' : 'Click to reach customer support'"
            >
                <div class="relative shrink-0">
                    <img :src="'/assets/icons/support.svg'" class="w-11 h-11 transition-transform group-hover:scale-110" loading="lazy" alt="icon"/>
                    <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full animate-pulse"></span>
                </div>
                <div class="text-slate-600 text-sm font-medium leading-tight flex-1">
                    <div class="text-slate-950 sm:text-base font-bold leading-normal flex items-center justify-between">
                       <span>{{ $t('24/7 Customer Support') }}</span>
                       <span class="text-xs text-primary font-semibold inline-flex items-center gap-1 group-hover:underline">
                           {{ masterStore.langDirection === 'rtl' ? 'تواصل الآن ←' : 'Contact now →' }}
                       </span>
                    </div>
                    <div class="text-slate-700 sm:text-base font-normal leading-normal mt-0 sm:mt-1">
                        {{ $t('Always our support team is available for you') }}.
                    </div>
                </div>
            </div>

            <div class="flex justify-start items-start gap-4 p-2">
                <img :src="'/assets/icons/easy-return.svg'" class="w-11 h-11 shrink-0" loading="lazy" alt="icon"/>
                <div class="text-slate-600 text-sm font-medium leading-tight">
                    <div class="text-slate-950 sm:text-base font-bold leading-normal">
                        {{ $t('Easy Return Policy') }}
                    </div>
                    <div class="text-slate-700 sm:text-base font-normal leading-normal mt-0 sm:mt-1">
                        {{ $t("If you're not satisfied, return it hassle-free") }}.
                    </div>
                </div>
            </div>
        </div>
        <!-- loader -->
        <div v-else class="p-4 bg-white rounded-xl border border-dashed border-slate-300 grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-8">
            <SkeletonLoader v-for="i in 4" :key="i" class="w-full h-[200px] sm:h-[140px] rounded-lg" />
        </div>
    </div>
</template>

<script setup>
import SkeletonLoader from './SkeletonLoader.vue';
import { useMaster } from '../stores/MasterStore';

const props = defineProps({
    isLoading: {
        type: Boolean,
        default: true
    }
});

const masterStore = useMaster();

const openSupport = () => {
    masterStore.openSupportMenu();
};
</script>
