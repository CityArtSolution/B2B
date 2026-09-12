<template>
  <div>
    <!-- Support Widget (Floating Action Button & Quick Menu) -->
    <div
      v-if="!isHiddenRoute"
      class="fixed z-40 bottom-6 select-none flex flex-col items-center"
      :class="masterStore.langDirection === 'rtl' ? 'left-4 sm:left-6' : 'right-4 sm:right-6'"
    >
      <!-- Quick Support Popup Menu -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform scale-95 opacity-0 translate-y-2"
        enter-to-class="transform scale-100 opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform scale-100 opacity-100 translate-y-0"
        leave-to-class="transform scale-95 opacity-0 translate-y-2"
      >
        <div
          v-if="masterStore.supportMenuOpen && !masterStore.chatCanvas"
          class="absolute bottom-16 mb-2 w-72 sm:w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50"
          :class="masterStore.langDirection === 'rtl' ? 'left-0' : 'right-0'"
        >
          <!-- Header -->
          <div class="p-4 bg-primary text-white">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-ping"></span>
                <h3 class="font-bold text-base">{{ $t('24/7 Customer Support') }}</h3>
              </div>
              <button
                @click="masterStore.closeSupportMenu()"
                class="text-white/80 hover:text-white p-1 rounded transition-colors"
                aria-label="Close"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>
            <p class="text-xs text-white/90 mt-1 leading-relaxed">
              {{ masterStore.langDirection === 'rtl' ? 'فريق الدعم متواجد لخدمتك دائماً، اختر وسيلة التواصل المناسبة:' : 'Our support team is available 24/7. Choose your preferred channel:' }}
            </p>
          </div>

          <!-- Channel Options -->
          <div class="p-3 space-y-2 bg-slate-50/50">
            <!-- WhatsApp Option -->
            <a
              :href="whatsappLink"
              target="_blank"
              rel="noopener noreferrer"
              @click="masterStore.closeSupportMenu()"
              class="flex items-center gap-3 p-3 rounded-xl border border-emerald-200 bg-white hover:bg-emerald-50/70 transition-all shadow-sm group"
            >
              <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                <FontAwesomeIcon :icon="faWhatsapp" class="w-5 h-5" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <div class="text-sm font-bold text-slate-800">
                    {{ masterStore.langDirection === 'rtl' ? 'محادثة عبر واتساب' : 'WhatsApp Support' }}
                  </div>
                  <span class="text-[10px] font-semibold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                    {{ masterStore.langDirection === 'rtl' ? 'فوري' : 'Instant' }}
                  </span>
                </div>
                <p class="text-xs text-slate-500 truncate mt-0.5">
                  {{ masterStore.langDirection === 'rtl' ? 'رد سريع ومباشر 24/7' : 'Direct & fast response 24/7' }}
                </p>
              </div>
            </a>

            <!-- Live Web Chat Option -->
            <button
              type="button"
              @click="openLiveChat"
              class="w-full flex items-center gap-3 p-3 rounded-xl border border-primary/20 bg-white hover:bg-primary/5 transition-all shadow-sm group text-start"
            >
              <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                <FontAwesomeIcon :icon="faCommentDots" class="w-5 h-5" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <div class="text-sm font-bold text-slate-800">
                    {{ masterStore.langDirection === 'rtl' ? 'شات الموقع المباشر' : 'Live Web Chat' }}
                  </div>
                  <span
                    v-if="unreadCount > 0"
                    class="text-[10px] font-bold bg-red-500 text-white px-2 py-0.5 rounded-full"
                  >
                    {{ unreadCount }}
                  </span>
                </div>
                <p class="text-xs text-slate-500 truncate mt-0.5">
                  {{ masterStore.langDirection === 'rtl' ? 'تحدث مباشرة مع فريق الخدمة' : 'Chat directly with support' }}
                </p>
              </div>
            </button>

            <!-- Phone Call Option (if available) -->
            <a
              v-if="contactPhone"
              :href="'tel:' + contactPhone"
              @click="masterStore.closeSupportMenu()"
              class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition-all shadow-sm group"
            >
              <div class="w-10 h-10 rounded-full bg-slate-700 text-white flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                <FontAwesomeIcon :icon="faPhone" class="w-4 h-4" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-bold text-slate-800">
                  {{ masterStore.langDirection === 'rtl' ? 'اتصال هاتفي مباشر' : 'Phone Call' }}
                </div>
                <p class="text-xs text-slate-500 truncate mt-0.5" dir="ltr">
                  {{ contactPhone }}
                </p>
              </div>
            </a>
          </div>
        </div>
      </transition>

      <!-- Floating Action Button (FAB) -->
      <button
        type="button"
        @click="handleTriggerClick"
        class="relative w-14 h-14 rounded-full bg-primary text-white shadow-xl shadow-primary/30 flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95 group focus:outline-none focus:ring-4 focus:ring-primary/20"
        :title="$t('24/7 Customer Support')"
        aria-label="Support button"
      >
        <!-- Unread Messages Badge -->
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 bg-red-500 text-white text-[11px] min-w-[20px] h-[20px] px-1 rounded-full flex items-center justify-center font-bold border-2 border-white shadow z-10"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>

        <!-- Online Green Status Indicator -->
        <span
          v-else
          class="absolute top-0 end-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full animate-pulse"
        ></span>

        <!-- Dynamic Icon (X when menu is open, Headset otherwise) -->
        <XMarkIcon
          v-if="masterStore.supportMenuOpen && !masterStore.chatCanvas"
          class="w-7 h-7 transition-transform duration-200 rotate-90"
        />
        <FontAwesomeIcon
          v-else
          :icon="faHeadset"
          class="w-6 h-6 transition-transform duration-200 group-hover:scale-110"
        />
      </button>
    </div>

    <!-- Backdrop when Quick Menu is Open (click to close) -->
    <div
      v-if="masterStore.supportMenuOpen && !masterStore.chatCanvas"
      class="fixed inset-0 z-30 bg-transparent"
      @click="masterStore.closeSupportMenu()"
    ></div>

    <!-- Chat Canvas / Drawer -->
    <TransitionRoot as="template" :show="masterStore.chatCanvas">
      <Dialog as="div" class="relative z-50" @close="closeChatCanvas">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" />
        <div class="fixed inset-0 overflow-hidden">
          <div class="absolute inset-0 overflow-hidden">
            <div
              class="pointer-events-none fixed inset-y-0 flex max-w-full"
              :class="masterStore.langDirection === 'rtl' ? 'left-0' : 'right-0'"
            >
              <TransitionChild
                as="template"
                enter="transform transition ease-in-out duration-300 sm:duration-500"
                :enter-from="masterStore.langDirection === 'rtl' ? '-translate-x-full' : 'translate-x-full'"
                enter-to="translate-x-0"
                leave="transform transition ease-in-out duration-300 sm:duration-500"
                leave-from="translate-x-0"
                :leave-to="masterStore.langDirection === 'rtl' ? '-translate-x-full' : 'translate-x-full'"
              >
                <DialogPanel class="pointer-events-auto relative w-screen max-w-md">
                  <div class="flex h-full flex-col bg-white shadow-2xl">
                    
                    <!-- Header -->
                    <div class="p-4 sm:p-5 border-b border-white/10 flex justify-between items-center bg-primary text-white">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center text-white">
                          <FontAwesomeIcon :icon="faHeadset" class="w-5 h-5" />
                        </div>
                        <div>
                          <DialogTitle class="text-base sm:text-lg font-bold leading-tight">
                            {{ $t('24/7 Customer Support') }}
                          </DialogTitle>
                          <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span class="text-xs text-white/80">
                              {{ masterStore.langDirection === 'rtl' ? 'متاحون للرد الآن' : 'Online & ready to help' }}
                            </span>
                          </div>
                        </div>
                      </div>

                      <div class="flex items-center gap-2">
                        <!-- Refresh Button -->
                        <button
                          v-if="authStore.token"
                          @click="refreshMessages"
                          class="p-2 rounded-full hover:bg-white/15 text-white/90 hover:text-white transition-colors"
                          :title="masterStore.langDirection === 'rtl' ? 'تحديث الرسائل' : 'Refresh messages'"
                        >
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            :class="{ 'animate-spin': isRefreshing }"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M4 10a8 8 0 0116 0m-8-8a8 8 0 010 16"/>
                          </svg>
                        </button>
                        <!-- Close Button -->
                        <button
                          class="p-2 rounded-full hover:bg-white/15 text-white/90 hover:text-white transition-colors"
                          @click="closeChatCanvas"
                          aria-label="Close"
                        >
                          <XMarkIcon class="h-6 w-6" />
                        </button>
                      </div>
                    </div>

                    <!-- Guest Notification Banner (if not logged in) -->
                    <div v-if="!authStore.token" class="p-4 bg-amber-50 border-b border-amber-200 text-amber-950 text-xs">
                      <div class="font-bold flex items-center gap-1.5 mb-1.5 text-sm">
                        <span>👋</span>
                        <span>{{ masterStore.langDirection === 'rtl' ? 'أهلاً بك في الدعم المباشر!' : 'Welcome to Support!' }}</span>
                      </div>
                      <p class="leading-relaxed mb-3 text-slate-700">
                        {{ masterStore.langDirection === 'rtl' ? 'لحفظ محادثاتك وسجل تواصلك يرجى تسجيل الدخول، أو يمكنك التواصل الفوري والمباشر معنا عبر الواتساب بدون تسجيل:' : 'Please login to save your chat history, or contact us directly on WhatsApp without signing in:' }}
                      </p>
                      <div class="flex items-center gap-2">
                        <a
                          :href="whatsappLink"
                          target="_blank"
                          class="flex-1 inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-3 rounded-lg text-xs shadow-sm transition-colors"
                        >
                          <FontAwesomeIcon :icon="faWhatsapp" class="w-4 h-4" />
                          <span>{{ masterStore.langDirection === 'rtl' ? 'واتساب مباشر' : 'WhatsApp Chat' }}</span>
                        </a>
                        <button
                          type="button"
                          @click="handleGuestLogin"
                          class="inline-flex items-center justify-center bg-primary hover:bg-primary/90 text-white font-semibold py-2 px-3 rounded-lg text-xs shadow-sm transition-colors"
                        >
                          <span>{{ masterStore.langDirection === 'rtl' ? 'تسجيل الدخول' : 'Login' }}</span>
                        </button>
                      </div>
                    </div>

                    <!-- Messages Container -->
                    <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50">
                      <!-- Empty State -->
                      <div
                        v-if="chats.length === 0"
                        class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400"
                      >
                        <div class="w-16 h-16 rounded-full bg-slate-200/70 flex items-center justify-center text-slate-500 mb-3">
                          <FontAwesomeIcon :icon="faCommentDots" class="w-8 h-8" />
                        </div>
                        <p class="text-sm font-medium text-slate-600">
                          {{ masterStore.langDirection === 'rtl' ? 'لا توجد رسائل سابقة' : 'No messages yet' }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1 max-w-xs">
                          {{ masterStore.langDirection === 'rtl' ? 'اكتب استفسارك وسيقوم فريق الدعم بالرد عليك في أقرب وقت.' : 'Type your question below and our team will answer you shortly.' }}
                        </p>
                      </div>

                      <!-- Message Bubbles -->
                      <div
                        v-for="(msg, index) in chats"
                        :key="msg.id || index"
                        :class="msg.type === 'user' ? 'text-right' : 'text-left'"
                        class="flex flex-col"
                      >
                        <div
                          :class="[
                            'max-w-[80%] inline-block px-4 py-2.5 rounded-2xl text-sm leading-relaxed shadow-sm',
                            msg.type === 'user'
                              ? 'bg-primary text-white self-end rounded-br-none'
                              : 'bg-white text-slate-800 border border-slate-200 self-start rounded-bl-none'
                          ]"
                        >
                          {{ msg.message }}
                        </div>
                        <span
                          :class="msg.type === 'user' ? 'self-end' : 'self-start'"
                          class="text-[10px] text-slate-400 mt-1 px-1"
                        >
                          {{ formatDate(msg.created_at) }}
                        </span>
                      </div>
                    </div>

                    <!-- Input Area -->
                    <div class="p-3 border-t border-slate-200 bg-white">
                      <div v-if="authStore.token" class="flex items-center gap-2">
                        <textarea
                          v-model="message"
                          rows="1"
                          class="flex-grow border border-slate-200 rounded-xl px-3 py-2.5 text-sm resize-none focus:outline-none focus:border-primary placeholder:text-slate-400"
                          :placeholder="masterStore.langDirection === 'rtl' ? 'اكتب رسالتك هنا...' : 'Type your message...'"
                          @keydown.enter.exact.prevent="sendMessage"
                        ></textarea>
                        <button
                          type="button"
                          class="bg-primary hover:bg-primary/90 text-white w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors disabled:opacity-50"
                          :disabled="!message.trim()"
                          @click="sendMessage"
                          aria-label="Send"
                        >
                          <FontAwesomeIcon :icon="faPaperPlane" class="w-4 h-4" />
                        </button>
                      </div>

                      <!-- Guest Prompt at bottom -->
                      <div v-else class="flex items-center justify-between gap-2">
                        <button
                          type="button"
                          @click="handleGuestLogin"
                          class="flex-1 py-2.5 px-3 bg-primary text-white text-xs font-semibold rounded-xl hover:bg-primary/90 text-center transition-colors shadow-sm"
                        >
                          {{ masterStore.langDirection === 'rtl' ? 'تسجيل الدخول للمحادثة' : 'Login to Chat' }}
                        </button>
                        <a
                          :href="whatsappLink"
                          target="_blank"
                          class="flex-1 py-2.5 px-3 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 text-center flex items-center justify-center gap-1.5 transition-colors shadow-sm"
                        >
                          <FontAwesomeIcon :icon="faWhatsapp" class="w-4 h-4" />
                          <span>{{ masterStore.langDirection === 'rtl' ? 'واتساب مباشر' : 'WhatsApp' }}</span>
                        </a>
                      </div>
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
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { useAuth } from '@/stores/AuthStore';
import { useChat } from '@/stores/ChatStore';
import { useMaster } from '@/stores/MasterStore';
import Pusher from 'pusher-js';
import axios from 'axios';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/solid';
import { faCommentDots, faHeadset, faPhone, faPaperPlane } from '@fortawesome/free-solid-svg-icons';
import { faWhatsapp } from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import moment from 'moment';

const route = useRoute();
const isHiddenRoute = computed(() => {
  return ['checkout', 'blogs', 'blog-details'].includes(route.name);
});

let refreshInterval = null;
const authStore = useAuth();
const chatStore = useChat();
const masterStore = useMaster();

const chats = ref([]);
const message = ref('');
const unreadCount = ref(0);
const isRefreshing = ref(false);

const SUPPORT_SHOP_ID = 1;
let pusher = null;
let channel = null;

// Formatted phone number
const contactPhone = computed(() => masterStore.mobile || '0550136556');

// Formatted WhatsApp URL with Saudi number prefix logic & friendly text
const whatsappLink = computed(() => {
  const rawNumber = masterStore.whatsapp || masterStore.mobile || '0550136556';
  let cleaned = String(rawNumber).replace(/[^0-9]/g, '');
  if (cleaned.startsWith('05') && cleaned.length === 10) {
    cleaned = '966' + cleaned.substring(1);
  } else if (cleaned.startsWith('5') && cleaned.length === 9) {
    cleaned = '966' + cleaned;
  }
  const greeting = encodeURIComponent(
    masterStore.langDirection === 'rtl'
      ? 'مرحباً، أود الاستفسار بخصوص المتجر وخدماتكم.'
      : 'Hello, I have an inquiry regarding your store products.'
  );
  return `https://wa.me/${cleaned}?text=${greeting}`;
});

// Trigger button click handler
const handleTriggerClick = () => {
  if (masterStore.chatCanvas) {
    masterStore.closeChatCanvas();
    masterStore.closeSupportMenu();
  } else {
    masterStore.toggleSupportMenu();
  }
};

// Open live chat
const openLiveChat = async () => {
  masterStore.openChatCanvas();
  if (authStore.token) {
    await markMessagesAsRead();
    unreadCount.value = 0;
    getMessages();
  }
};

// Close chat canvas
const closeChatCanvas = () => {
  masterStore.closeChatCanvas();
};

// Handle guest login click
const handleGuestLogin = () => {
  masterStore.closeChatCanvas();
  masterStore.closeSupportMenu();
  authStore.showLoginModal();
};

// Watch chat canvas state
watch(() => masterStore.chatCanvas, async (isOpen) => {
  if (isOpen && authStore.token) {
    await markMessagesAsRead();
    unreadCount.value = 0;
    getMessages();
  }
});

const scrollToBottom = () => {
  nextTick(() => {
    const container = document.getElementById('chat-messages');
    if (container) container.scrollTop = container.scrollHeight;
  });
};

const getMessages = async () => {
  if (!authStore.token) return;
  try {
    const response = await axios.get('/get-message', {
      params: {
        shop_id: SUPPORT_SHOP_ID,
        page: 1,
        per_page: 50
      },
      headers: { Authorization: authStore.token }
    });
    const msgs = response.data.data?.data || [];
    chats.value = msgs.reverse();
    scrollToBottom();
  } catch (err) {
    console.error('Failed to get messages', err);
  }
};

const sendMessage = async () => {
  if (!message.value.trim() || !authStore.token) return;
  const sendableMessage = message.value.trim();
  message.value = '';

  chats.value.push({
    shop_id: SUPPORT_SHOP_ID,
    message: sendableMessage,
    type: 'user',
    created_at: new Date(),
    user: { profile_photo: authStore.user?.profile_photo }
  });
  scrollToBottom();

  try {
    await axios.post('/send-message', {
      shop_id: SUPPORT_SHOP_ID,
      message: sendableMessage,
      type: 'user'
    }, {
      headers: { Authorization: authStore.token }
    });
  } catch (err) {
    console.error('Failed to send message', err);
  }
};

const refreshMessages = async () => {
  if (!authStore.token) return;
  isRefreshing.value = true;
  try {
    const response = await axios.get('/get-message', {
      params: {
        shop_id: SUPPORT_SHOP_ID,
        page: 1,
        per_page: 50
      },
      headers: { Authorization: authStore.token }
    });
    const msgs = response.data.data?.data || [];
    chats.value = msgs.reverse();
    scrollToBottom();
  } catch (err) {
    console.error('Failed to refresh messages', err);
  } finally {
    setTimeout(() => {
      isRefreshing.value = false;
    }, 400);
  }
};

const fetchUnreadMessages = async () => {
  if (!authStore.token || !authStore.user?.id) return;
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
    console.error('Failed to fetch unread count', e);
  }
};

const markMessagesAsRead = async () => {
  if (!authStore.token) return;
  try {
    await axios.get('/mark-messages-read', {
      params: { shop_id: SUPPORT_SHOP_ID },
      headers: { Authorization: authStore.token }
    });
  } catch (e) {
    console.error('Failed to mark messages as read', e);
  }
};

const startAutoRefresh = () => {
  refreshInterval = setInterval(async () => {
    if (!masterStore.chatCanvas) {
      fetchUnreadMessages();
      return;
    }
    await refreshMessages();
  }, 15000);
};

const stopAutoRefresh = () => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
};

const handlePusherChannel = () => {
  if (!masterStore.pusher_app_key || !authStore.user?.id) return;

  try {
    pusher = new Pusher(masterStore.pusher_app_key, {
      cluster: masterStore.pusher_app_cluster,
      encrypted: true
    });

    channel = pusher.subscribe('chat_user_' + authStore.user.id);

    channel.bind('send-message-to-user', () => {
      if (masterStore.chatCanvas) {
        getMessages();
      } else {
        fetchUnreadMessages();
      }
    });
  } catch (err) {
    console.error('Pusher subscription failed', err);
  }
};

const formatDate = (date) => {
  return moment(date).format('hh:mm a, DD MMM, YYYY');
};

onMounted(() => {
  chatStore.activeShop = {
    id: SUPPORT_SHOP_ID,
    name: 'Customer Support',
    logo: '/support-logo.png'
  };

  if (authStore.token) {
    getMessages();
    handlePusherChannel();
    fetchUnreadMessages();
  }
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
</script>

<style scoped>
#chat-messages::-webkit-scrollbar {
  width: 5px;
}
#chat-messages::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.15);
  border-radius: 4px;
}
#chat-messages::-webkit-scrollbar-track {
  background-color: transparent;
}
</style>
