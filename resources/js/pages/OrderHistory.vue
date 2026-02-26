<template>
    <div>

        <!-- Header -->
        <AuthPageHeader title="Order History" />

        <!-- Order Status -->
        <div class="bg-white p-4 overflow-x-auto">
          <div class="flex items-center min-w-max relative">
        
        
        <div
          v-for="(status, index) in statuses"
          :key="status.value"
          class="flex items-center"
        >
          <div
            class="flex flex-col items-center cursor-pointer"
            @click="orderStatus = status.value"
          >
        
            <!-- title -->
            <span class="mb-2 text-sm font-medium"
              :class="orderStatus === status.value ? 'text-primary' : 'text-slate-500'">
              {{ $t(status.label) }}
            </span>
        
            <!-- circle -->
            <div
              class="w-16 h-16 rounded-full flex items-center justify-center border-2 transition-all duration-300"
              :class="index <= activeIndex
                ? 'border-primary bg-primary/10'
                : 'border-slate-300 bg-white'"
            >
              <component
                :is="status.icon"
                class="w-7 h-7"
                :class="[
                  index === activeIndex ? 'text-primary' : 'text-slate-400',
                  index === activeIndex ? status.animate : ''
                ]"
              />
            </div>
        
            <!-- count -->
            <span class="mt-2 text-sm text-slate-600">
              {{ status.count }}
            </span>
          </div>
        
          <div
            v-if="index < statuses.length - 1"
            class="w-12 h-[2px] mx-2 transition-all duration-500"
            :class="index < activeIndex ? 'bg-primary' : 'bg-slate-300'"
          ></div>
        </div>
          </div>
        </div>

        <!-- Order History -->
        <div class="px-2 pt-2 md:px-4 md:pt-4 lg:px-6 lg:pt-6">
            <div class="p-4 lg:p-6 bg-white rounded-xl flex flex-col gap-3">

                <!-- Order Item -->
                <div v-for="order in orders" :key="order.id">
                    <OrderHistoryOrderItem :order="order" />
                </div>

                <!-- Order list empty -->
                <div v-if="orders.length == 0">
                    <p>{{ $t('No Order Found') }}</p>
                </div>

            </div>
        </div>

        <!-- pagination's -->
        <div v-if="totalItems > perPage" class="px-2 md:px-4 lg:px-6 mt-4">
            <div class="bg-white p-3 rounded-xl flex justify-between items-center w-full  gap-4 flex-wrap">
                <div class="text-slate-800 text-base font-normal leading-normal">
                    {{ $t('Showing') }} {{ perPage * (currentPage - 1) + 1 }} {{ $t('to') }} {{ perPage * (currentPage - 1) +
                    orders.length }} {{ $t('of') }} {{ totalItems }} {{ $t('results') }}
                </div>
                <div>

                    <vue-awesome-paginate :total-items="totalItems" :items-per-page="perPage" type="button"
                        :max-pages-shown="3" v-model="currentPage" :hide-prev-next-when-ends="true" @click="onClickHandler" />
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { onMounted, ref, watch, computed  } from 'vue';
import AuthPageHeader from '../components/AuthPageHeader.vue';
import OrderHistoryOrderItem from '../components/OrderHistoryOrderItem.vue';
import {
  ClockIcon,
  CheckCircleIcon,
  CogIcon,
  CubeIcon,
  TruckIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const statuses = computed(() => [
  { label: 'Pending'   , value: 'Pending'   ,icon: ClockIcon        ,animate: 'animate-spin-slow',count: statusWiseOrders.value.pending },
  { label: 'Confirm'   , value: 'Confirm'   ,icon: CheckCircleIcon  ,animate: 'animate-check'    ,count: statusWiseOrders.value.confirm },
  { label: 'Processing', value: 'Processing',icon: CogIcon          ,animate: 'animate-spin'     ,count: statusWiseOrders.value.processing },
  { label: 'Pickup'    , value: 'Pickup'    ,icon: CubeIcon         ,animate: 'animate-bounce'   ,count: statusWiseOrders.value.pickup },
  { label: 'On The Way', value: 'On The Way',icon: TruckIcon        ,animate: 'animate-truck'    ,count: statusWiseOrders.value.on_the_way },
  { label: 'Delivered' , value: 'Delivered' ,icon: CheckCircleIcon  ,animate: 'animate-ping-once',count: statusWiseOrders.value.delivered },
  { label: 'Cancelled' , value: 'cancelled' ,icon: XCircleIcon      ,animate: 'animate-shake'    ,count: statusWiseOrders.value.cancelled },
  { label: 'All'       , value: ''          ,icon: CheckCircleIcon  ,animate: ''                 ,count: statusWiseOrders.value.all },
])

import { useRouter } from 'vue-router';
const router = useRouter();

import { useAuth } from '../stores/AuthStore';
const authStore = useAuth();
const orderStatus = ref('Pending');

const orders = ref([]);

const totalItems = ref(20);
const currentPage = ref(1);
const perPage = ref(10);

const statusWiseOrders = ref({
    all: 0,
    pending: 0,
    confirm: 0,
    processing: 0,
    pickup: 0,
    on_the_way: 0,
    delivered: 0,
    cancelled: 0
});

const activeIndex = computed(() =>
  statuses.value.findIndex(s => s.value === orderStatus.value)
)

const activeLineWidth = computed(() => {
  const total = statuses.value.length - 1
  if (activeIndex.value <= 0) return '0%'
  return `${(activeIndex.value / total) * 100}%`
})

const onClickHandler = (page) => {
    currentPage.value = page;
    fetchOrders();
};

watch(orderStatus, () => {
    currentPage.value = 1;
    fetchOrders();
});

onMounted(() => {
    fetchOrders();
});

const fetchOrders = async () => {
    axios.get('/orders', {
        params: {
            order_status: orderStatus.value,
            page: currentPage.value,
            per_page: perPage.value
        },
        headers: {
            Authorization: authStore.token,
        }
    }).then((response) => {
        totalItems.value = response.data.data.total;
        orders.value = response.data.data.orders;
        statusWiseOrders.value = response.data.data.status_wise_orders;
    }).catch((error) => {
        if (error.response.status === 401) {
            authStore.token = null;
            authStore.user = null;
            authStore.addresses = [];
            authStore.favoriteProducts = 0;
            router.push('/');
        }
    });
};

</script>
<style scoped>
@keyframes draw {
  from {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
  }
  to {
    stroke-dashoffset: 0;
  }
}

.animate-icon {
  animation: bounce 1.5s infinite;
}

@keyframes bounce {
  0%,100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}
@keyframes spinSlow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin-slow {
  animation: spinSlow 3s linear infinite;
}

@keyframes truck {
  0% { transform: translateX(-4px); }
  50% { transform: translateX(4px); }
  100% { transform: translateX(-4px); }
}
.animate-truck {
  animation: truck 1.2s infinite ease-in-out;
}

@keyframes shake {
  0% { transform: translateX(0); }
  25% { transform: translateX(-3px); }
  50% { transform: translateX(3px); }
  75% { transform: translateX(-3px); }
  100% { transform: translateX(0); }
}
.animate-shake {
  animation: shake 0.6s;
}

</style>
