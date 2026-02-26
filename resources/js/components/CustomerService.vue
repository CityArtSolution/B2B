<template>
  <div>
    <!-- Floating Button -->
    <div
      v-if="routerName !== 'checkout' && routerName !== 'blogs' && routerName !== 'blog-details'"
      class="w-[72px] bg-white shadow border-t border-b border-primary flex-col justify-start items-center gap-1 fixed z-10 overflow-hidden cursor-pointer hidden sm:flex bottom-20 left-0 border-r rounded-r-[10px]"
      @click="toggleChatCanvas"
    >
      <div class="pt-2 pb-0.5 flex flex-col items-center gap-1 justify-center relative">
        
        <!-- Icon + Unread Badge -->
        <div class="relative">
          <font-awesome-icon :icon="faCommentDots" class="w-6 h-6 text-primary" />
    
          <!-- Unread Messages Badge -->
          <span
            v-if="unreadCount > 0"
            class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center font-bold"
          >
            {{ unreadCount > 9 ? '9+' : unreadCount }}
          </span>
        </div>
    
        <!-- Label -->
        <div class="text-center text-slate-600 text-xs font-normal">
          {{ $t('Support') }}
        </div>
      </div>
    </div>

    <!-- Chat Canvas -->
    <TransitionRoot as="template" :show="chatCanvas">
      <Dialog as="div" class="relative z-50" @close="toggleChatCanvas">
        <div class="fixed inset-0 bg-black/30 transition-opacity" aria-hidden="true" />
        <div class="fixed inset-0 overflow-hidden">
          <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 flex max-w-full left-0">
              <TransitionChild
                as="template"
                enter="transform transition ease-in-out duration-500 sm:duration-700"
                enter-from="-translate-x-full"
                enter-to="translate-x-0"
                leave="transform transition ease-in-out duration-500 sm:duration-700"
                leave-from="translate-x-0"
                leave-to="-translate-x-full"
              >
                <DialogPanel class="pointer-events-auto relative w-screen max-w-md">
                  <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                    
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-primary text-white">
                      <DialogTitle class="text-lg font-bold">{{ $t('24/7 Customer Support') }}</DialogTitle>
                      <div class="flex items-center gap-2">
                        <!-- Refresh Button -->
                        <button @click="refreshMessages" class="outline-none border-0 mr-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white hover:text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M4 10a8 8 0 0116 0m-8-8a8 8 0 010 16"/>
                          </svg>
                        </button>
                        <button class="outline-none border-0" @click="toggleChatCanvas">
                          <XMarkIcon class="h-6 w-6" />
                        </button>
                      </div>
                    </div>

                    <!-- Messages -->
                    <div id="chat-messages" class="flex-1 overflow-auto p-4 space-y-3">
                      <div
                        v-for="msg in chats"
                        :key="msg.id || msg.created_at"
                        :class="msg.type === 'user' ? 'text-right' : 'text-left'"
                      >
                        <div
                          :class="msg.type === 'user' ? 'inline-block bg-primary text-white px-3 py-2 rounded-xl' : 'inline-block bg-gray-200 px-3 py-2 rounded-xl'"
                        >
                          {{ msg.message }}
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                          {{ formatDate(msg.created_at) }}
                        </p>
                      </div>
                    </div>

                    <!-- Input -->
                    <div class="flex gap-2 p-3 border-t">
                      <textarea
                        v-model="message"
                        rows="1"
                        class="flex-grow border rounded px-3 py-2 resize-none"
                        placeholder="Type your message"
                        @keydown.enter.exact.prevent="sendMessage"
                        @keydown.shift.enter=""
                      ></textarea>
                      <button class="bg-primary text-white px-4 rounded" @click="sendMessage">{{ $t('Send') }}</button>
                    </div>

                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { useAuth } from '@/stores/AuthStore';
import { useChat } from '@/stores/ChatStore';
import { useMaster } from '@/stores/MasterStore';
import Pusher from 'pusher-js';
import axios from 'axios';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/solid';
import { faCommentDots } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import moment from 'moment';
import { onUnmounted } from 'vue';

const router = useRoute();
const routerName = router.name;
let refreshInterval;

const authStore = useAuth();
const chatStore = useChat();
const masterStore = useMaster();

const chatCanvas = ref(false);
const toggleChatCanvas = async () => {
  chatCanvas.value = !chatCanvas.value;

  if (chatCanvas.value) {
    await markMessagesAsRead();
    unreadCount.value = 0;
    getMessages();
  }
};

const chats = ref([]);
const message = ref('');
const unreadCount = ref(0);

const SUPPORT_SHOP_ID = 1;

let pusher = null;
let channel = null;

const startAutoRefresh = () => {
  refreshInterval = setInterval(async () => {
    if (!chatCanvas.value) {
      fetchUnreadMessages();
      return;
    }

    await refreshMessages();
  }, 15000);
};


const stopAutoRefresh = () => {
  clearInterval(refreshInterval);
};


onMounted(() => {
  chatStore.activeShop = {
    id: SUPPORT_SHOP_ID,
    name: 'Customer Support',
    logo: '/support-logo.png'
  };
  getMessages();
  handlePusherChannel();
  scrollToBottom();
  fetchUnreadMessages();
  startAutoRefresh();
});

onUnmounted(() => {
  if (channel) {
    channel.unbind_all();
  }

  if (pusher) {
    pusher.disconnect();
  }

  stopAutoRefresh();
});

const scrollToBottom = () => {
  nextTick(() => {
    const container = document.getElementById('chat-messages');
    if (container) container.scrollTop = container.scrollHeight;
  });
};

const getMessages = async () => {
  try {
    const response = await axios.get('/get-message', {
      params: {
        shop_id: 1,
        page: 1,
        per_page: 50
      },
      headers: { Authorization: authStore.token }
    });
    const msgs = response.data.data?.data || [];
    chats.value = msgs.reverse();
    scrollToBottom();
  } catch (err) {
    console.error(err);
  }
};

const sendMessage = async () => {
  if (!message.value) return;
  const sendableMessage = message.value;
  message.value = '';

  chats.value = [
    ...chats.value,
    {
        
      shop_id: 1,
      message: sendableMessage,
      type: 'user',
      created_at: new Date(),
      user: { profile_photo: authStore.user.profile_photo }
    }
  ];
  scrollToBottom();

  try {
    await axios.post('/send-message', {
      shop_id: 1,
      message: sendableMessage,
      type: 'user'
    }, {
      headers: { Authorization: authStore.token }
    });
  } catch (err) {
    console.error(err);
  }
};

const refreshMessages = async () => {
  try {
    const response = await axios.get('/get-message', {
      params: {
        shop_id: 1,
        page: 1,
        per_page: 50
      },
      headers: { Authorization: authStore.token }
    });
    const msgs = response.data.data?.data || [];
    chats.value = msgs.reverse();
    scrollToBottom();
  } catch (err) {
    console.error(err);
  }
};

const fetchUnreadMessages = async () => {
  try {
    const res = await axios.get('/unread-messages', {
      params: {
        user_id: authStore.user.id,
        shop_id: SUPPORT_SHOP_ID
      },
      headers: { Authorization: authStore.token }
    });

    unreadCount.value = res.data.data?.unread_messages || 0;
  } catch (e) {
    console.error(e);
  }
};

const markMessagesAsRead = async () => {
  try {
    await axios.get('/mark-messages-read', {
      params: { shop_id: SUPPORT_SHOP_ID },
      headers: { Authorization: authStore.token }
    });
  } catch (e) {
    console.error(e);
  }
};


const handlePusherChannel = () => {
  if (!masterStore.pusher_app_key) return;
  if (!authStore.user || !authStore.user.id) return;

  pusher = new Pusher(masterStore.pusher_app_key, {
    cluster: masterStore.pusher_app_cluster,
    encrypted: true
  });

  channel = pusher.subscribe('chat_user_' + authStore.user.id);

  channel.bind('send-message-to-user', () => {
    if (chatCanvas.value) {
      getMessages();
    } else {
      fetchUnreadMessages();
    }
  });
};

// Format date
const formatDate = (date) => {
  return moment(date).format('hh:mm a, DD MMM, YYYY');
};
</script>

<style scoped>
/* Scrollbar صغير للشات */
#chat-messages::-webkit-scrollbar {
  width: 6px;
}
#chat-messages::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.2);
  border-radius: 3px;
}
#chat-messages::-webkit-scrollbar-track {
  background-color: transparent;
}
</style>
