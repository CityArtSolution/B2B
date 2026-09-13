<template>
    <div class="main-container">

        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 overflow-hidden pt-4">
            <router-link to="/" class="w-6 h-6">
                <HomeIcon class="w-5 h-5 text-slate-600" />
            </router-link>

            <div class="grow w-full overflow-hidden">
                <div class="space-x-1 text-slate-600 text-sm font-normal truncate">
                    <span>{{ $t('Home') }}</span>
                    <span>/</span>
                    <span>{{ $t('Cart') }}</span>
                    <span>/</span>
                    <span>{{ $t('Checkout') }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 my-3 gap-8">

            <div class="col-span-1 xl:col-span-2">

                <div class="py-4 border-b tran" :class="showProductItems ? 'border-primary' : 'border-slate-200'">
                    <!-- checkout -->
                    <div class="flex gap-2 justify-between items-center">
                        <div class="text-slate-950 text-lg sm:text-3xl font-medium leading-10">{{ $t('Checkout') }}
                        </div>
                        <div class="flex items-center gap-2 cursor-pointer"
                            @click="showProductItems = !showProductItems">
                            <div class="text-primary-600 text-lg font-medium leading-normal tracking-tight">
                                ({{ basketStore.checkoutTotalItems }} {{ $t('items') }})
                            </div>
                            <ChevronDownIcon class="w-5 h-5 text-primary-600 transition duration-300"
                                :class="showProductItems ? 'rotate-180' : ''" />
                        </div>
                    </div>

                    <!-- Product items -->
                    <div v-if="showProductItems">
                        <checkoutProducts />
                    </div>
                </div>

                <!-- Shipping Address -->
                <ShippingAddress />
                
                <!-- Shipping Method -->
                <div v-if="!isDigitalProduct" class="p-6 mt-6 bg-white rounded-2xl border border-slate-200">
                    <div class="text-slate-950 text-xl font-medium leading-7">
                        {{ $t('Shipping Method') }}
                        <span class="text-red-500">*</span>
                    </div>
                
                    <div class="mt-4 flex flex-col gap-4">
                
                        <!-- Company Delivery -->
                        <label class="flex items-center gap-4 cursor-pointer">
                            <input type="radio"
                                v-model="shippingType"
                                value="company"
                                class="radioBtn2" />
                            <span class="text-slate-600 text-base">
                                {{ $t('Delivery by the company') }}
                            </span>
                        </label>
                
                        <!-- Private Car -->
                        <label class="flex items-center gap-4 cursor-pointer">
                            <input type="radio"
                                v-model="shippingType"
                                value="private"
                                class="radioBtn2" />
                            <span class="text-slate-600 text-base">
                                {{ $t('Private car') }}
                            </span>
                        </label>
                
                        <!-- Shipping Company -->
                        <label class="flex items-center gap-4 cursor-pointer">
                            <input type="radio"
                                v-model="shippingType"
                                value="courier"
                                class="radioBtn2" />
                            <span class="text-slate-600 text-base">
                                {{ $t('Shipping company') }}
                            </span>
                        </label>
                    </div>
                
                    <!-- Shipping Companies -->
                    <div v-if="shippingType == 'courier'" class="mt-4 border-t pt-4">
                        <div class="text-slate-700 text-base font-medium mb-2">
                            {{ $t('Select Shipping Company') }}
                        </div>
                    
                        <Combobox v-model="shippingCompany" v-slot="{ open }">
                            <div class="relative mt-1">
                    
                                <ComboboxInput
                                    class="form-input pe-10"
                                    :displayValue="(company) => company?.name || ''"
                                    @change="shippingCompanyQuery = $event.target.value"
                                    @input="shippingCompanyQuery = $event.target.value"
                                    @focus="openShippingDropdown(open)"
                                    @click="openShippingDropdown(open)"
                                    :placeholder="$t('Search company')"
                                />

                                <ComboboxButton
                                    ref="comboboxButtonRef"
                                    class="absolute inset-y-0 end-0 flex items-center pe-3 text-slate-400 hover:text-slate-600 cursor-pointer">
                                    <ChevronUpDownIcon class="w-5 h-5" aria-hidden="true" />
                                </ComboboxButton>
                    
                                <ComboboxOptions
                                    class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white border border-slate-200 shadow-lg py-1">
                    
                                    <ComboboxOption
                                        v-for="company in filteredShippingCompanies"
                                        :key="company.id"
                                        :value="company"
                                        v-slot="{ selected, active }"
                                        as="template">
                                        <li :class="[
                                            active ? 'bg-primary-50 text-primary-900' : 'text-slate-900',
                                            'relative cursor-pointer select-none py-2.5 ps-10 pe-4 flex items-center justify-between text-base'
                                        ]">
                                            <span :class="[selected ? 'font-semibold text-primary' : 'font-normal', 'block truncate']">
                                                {{ company.name }}
                                            </span>
                                            <span v-if="selected" class="absolute inset-y-0 start-0 flex items-center ps-3 text-primary">
                                                <CheckIcon class="w-5 h-5" aria-hidden="true" />
                                            </span>
                                        </li>
                                    </ComboboxOption>
                    
                                    <div
                                        v-if="filteredShippingCompanies.length === 0"
                                        class="p-3 text-slate-400 text-sm text-center">
                                        {{ $t('No results found') }}
                                    </div>
                    
                                </ComboboxOptions>
                            </div>
                        </Combobox>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="mb-1">
                        <span class="text-slate-950 text-xl font-medium leading-7">{{ $t('Note') }}</span>
                        <span class="text-slate-500 text-lg font-normal leading-7 tracking-tight">
                            ({{ $t('Optional') }})
                        </span>
                    </div>
                    <textarea v-model="note" rows="3" class="form-input"
                        :placeholder="$t('Write your note') + '...'"></textarea>
                </div>

                <!-- Payment Method -->
                <div class="p-6 mt-4 bg-white rounded-2xl border border-slate-200 w-full">
                    <div class="text-slate-950 text-xl font-medium leading-7">
                        {{ $t('Payment Method') }}
                    </div>

                    <div class="mt-4 flex flex-wrap gap-4">

                        <label v-if="master.cashOnDelivery" for="Offer_Price" class="flex items-center gap-4 xl:min-w-80">
                            <input v-model="paymentType" id="Offer_Price" name="payment" type="radio" class="radioBtn2"
                                value="Offer_Price" />
                            <div class="p-2 bg-white rounded-xl border border-slate-200">
                                <img :src="'/assets/icons/money-2.svg'" alt="" class="w-7 h-7">
                            </div>
                            <span class="text-slate-500 text-base font-normal leading-normal">{{ $t('Offer Price') }}</span>
                        </label>

                        <label v-if="master.cashOnDelivery && !isDigitalProduct && confirmationData.payment_status == 'Previous_client'" class="flex items-center gap-4 xl:min-w-80">
                            <input v-model="paymentType" type="radio" class="radioBtn2" name="payment" value="Previous_client"/>
                            <div class="p-2 bg-white rounded-xl border border-slate-200">
                                <img :src="'/assets/icons/money-2.svg'" alt="" class="w-7 h-7">
                            </div>
                            <span class="text-slate-500">{{ $t('Previous client') }}</span>
                        </label>

                        <label v-if="master.onlinePayment" for="card" class="flex items-center gap-4 xl:min-w-80">
                            <input v-model="paymentType" id="card" name="payment" type="radio" class="radioBtn2"
                                value="card"  />
                            <div class="p-2 bg-white rounded-xl border border-slate-200">
                                <img :src="'/assets/icons/card.svg'" alt="" class="w-7 h-7">
                            </div>
                            <span class="text-slate-500 text-base font-normal leading-normal">
                                {{ $t('Credit or Debit Card') }}
                            </span>
                        </label>

                    </div>
                    <!-- Payment Gateways -->
                    <Transition leave-active-class="transition ease-in duration-300"
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">

                        <div v-if="paymentType === 'card'" class="mt-5 border-t border-slate-200">
                            <span class="text-slate-600 pt-2 block text-md font-medium leading-7">
                                {{ $t('Available Payment Gateways') }}
                            </span>
                            <div class="mt-3 flex flex-wrap gap-4">
                                <label v-for="gateway in master.paymentGateways" :key="gateway.id" :for="gateway.name"
                                    class="flex items-center gap-4 border relative has-[:checked]:border-primary has-[:checked]:shadow-lg p-2 rounded-md border-slate-200 cursor-pointer">
                                    <input v-model="paymentGateway" :id="gateway.name" name="paymentGateway"
                                        type="radio" class="sr-only" :value="gateway.name" />
                                    <div class="">
                                        <img :src="gateway.logo" alt="" class="w-32 h-16 object-contain">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </Transition>

                </div>

            </div>

            <!-- Order Summary -->
            <CheckoutOrderSummary :note="note" :paymentMethod="paymentMethod" :shippingType="shippingType" :shippingCompany="shippingCompany" :isDigitalProduct="isDigitalProduct" :maxInvoiceLimit="confirmationData.max_invoice_limit" :maxInvoiceNow="confirmationData.max_invoice_now" />

        </div>

    </div>
</template>

<script setup>
import { ChevronDownIcon, HomeIcon, ChevronUpDownIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { Combobox, ComboboxButton, ComboboxInput, ComboboxOptions, ComboboxOption } from '@headlessui/vue'

import { onMounted, computed , ref, watch } from 'vue';

import CheckoutOrderSummary from '../components/CheckoutOrderSummary.vue';
import checkoutProducts from '../components/checkoutProducts.vue';
import ShippingAddress from '../components/CheckoutShippingAddress.vue';

import axios from 'axios';

import { useAuth } from '../stores/AuthStore';
const AuthStore = useAuth();

import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';

import { useRouter } from 'vue-router';
const router = new useRouter();

const master = useMaster();
const basketStore = useBasketStore();

const showProductItems = ref(false);

const note = ref("");

const shippingType = ref(null); 
const shippingCompany = ref(null);

const shippingCompanies = ref([]);
const shippingCompanyQuery = ref('');

const isDigitalProduct = computed(() => {
    if (!basketStore.checkoutProducts || basketStore.checkoutProducts.length === 0) return false;
    return basketStore.checkoutProducts.every(shop =>
        shop.products && shop.products.length > 0 && shop.products.every(product => Boolean(product.is_digital))
    );
});

const filteredShippingCompanies = computed(() => {
    if (!shippingCompanyQuery.value) return shippingCompanies.value;

    return shippingCompanies.value.filter(company =>
        company.name
            .toLowerCase()
            .includes(shippingCompanyQuery.value.toLowerCase())
    );
});

const comboboxButtonRef = ref(null);
let lastOpenDropdownTime = 0;

const openShippingDropdown = (open) => {
    shippingCompanyQuery.value = '';
    const now = Date.now();
    if (now - lastOpenDropdownTime < 300) return;
    lastOpenDropdownTime = now;

    if (!open) {
        const btn = comboboxButtonRef.value?.$el || comboboxButtonRef.value;
        if (btn && typeof btn.click === 'function') {
            btn.click();
        }
    }
};

const checkPayment = master.cashOnDelivery ? 'Offer_Price' : master.onlinePayment ? 'card' : 'Previous_client' ;
const paymentType = ref(checkPayment);
const paymentMethod = ref(null);

const paymentGateway = ref(null);

const confirmationData = ref({
    max_invoice_limit: null,
    max_invoice_now: 0,
    payment_status: null,
});

const fetchRiders = async () => {
    try {
        const res = await axios.get('/riders');

        if (res.data.success) {
            shippingCompanies.value = res.data.data;
        }
    } catch (error) {
        console.error('Error fetching riders', error);
    }
};

const fetchConfirmationData = async () => {
    try {
        const res = await axios.get('/confirmation-data', {
            headers: {
                Authorization: AuthStore.token,
            }
        });

        if (res.data.success) {
            confirmationData.value.max_invoice_limit = res.data.data.max_invoice_limit != null ? parseFloat(res.data.data.max_invoice_limit) : null;
            confirmationData.value.max_invoice_now = res.data.data.max_invoice_now != null ? parseFloat(res.data.data.max_invoice_now) : 0;
            confirmationData.value.payment_status = res.data.data.payment_status;
        }
    } catch (error) {
        console.error('Error fetching confirmation data:', error);
    }
};

onMounted(() => {
    window.scrollTo(0, 0);
    basketStore.coupon_code = "";
    if (paymentType.value === 'card') {
        if (!paymentGateway.value && master.paymentGateways && master.paymentGateways.length > 0) {
            paymentGateway.value = master.paymentGateways[0].name;
        }
        paymentMethod.value = paymentGateway.value;
    } else {
        paymentMethod.value = paymentType.value;
    }
    if (!AuthStore.user) {
        router.push({ name: 'home' });
    }
    AuthStore.showAddressModal = false;
    AuthStore.showChangeAddressModal = false;
    fetchRiders();
    fetchConfirmationData(); 
});

watch(shippingCompany, () => {
    shippingCompanyQuery.value = '';
});

watch(shippingType, (val) => {
    if (val != 'courier') {
        shippingCompany.value = null;
        shippingCompanyQuery.value = '';
    }
});

watch(() => [master.cashOnDelivery, master.onlinePayment], () => {
    if (!paymentType.value || (paymentType.value === 'Previous_client' && confirmationData.value.payment_status !== 'Previous_client')) {
        paymentType.value = master.cashOnDelivery ? 'Offer_Price' : master.onlinePayment ? 'card' : 'Previous_client';
    }
});

watch(paymentType, (newType) => {
    if (newType === 'card') {
        if (!paymentGateway.value && master.paymentGateways && master.paymentGateways.length > 0) {
            paymentGateway.value = master.paymentGateways[0].name;
        }
        paymentMethod.value = paymentGateway.value;
    } else {
        paymentMethod.value = paymentType.value;
    }
});

watch(() => master.paymentGateways, (gateways) => {
    if (paymentType.value === 'card' && !paymentGateway.value && gateways && gateways.length > 0) {
        paymentGateway.value = gateways[0].name;
        paymentMethod.value = paymentGateway.value;
    }
}, { deep: true, immediate: true });

watch(paymentGateway, () => {
    if (paymentType.value === 'card') {
        paymentMethod.value = paymentGateway.value;
    }
});
</script>
<style scoped>
.form-label {
    @apply text-slate-700 text-base font-normal leading-normal;
}

.form-input {
    @apply p-3 rounded-lg border border-slate-200 focus:border-primary w-full outline-none text-base font-normal leading-normal placeholder:text-slate-400;
}

.formInputCoupon {
    @apply rounded-lg border border-slate-200 focus:border-primary w-full outline-none text-base font-normal leading-normal placeholder:text-slate-400;
}

.radio-btn {
    @apply w-5 h-5 border appearance-none border-slate-300 rounded-full checked:bg-primary ring-primary checked:outline-1 outline-offset-1 checked:outline-primary checked:outline transition duration-100 ease-in-out m-0;
}

.radioBtn2 {
    @apply w-4 h-4 border appearance-none border-slate-300 rounded-full checked:bg-primary ring-primary checked:outline-1 outline-offset-1 checked:outline-primary checked:outline transition duration-100 ease-in-out m-0;
}
</style>
