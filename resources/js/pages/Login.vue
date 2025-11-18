<template>
    <div class="login-page-wrapper" :style="{ backgroundImage: `url(${backgroundImage})` }">
        <section class="login-section">
            <!-- Left Side - Image/Illustration (Thumbnail) -->
            <div class="thumbnail position-relative text-primary">
                <img :src="loginIllustration" 
                     alt="thumbnail" 
                     width="100%"
                     loading="lazy" />
                
                <!-- Logo Overlay -->
                <div class="logo_right">
                    <img :src="master.logo || defaultFavicon" 
                         alt="logo"
                         loading="lazy" />
                    <span class="site_name">{{ master.appName }}</span>
                </div>
            </div>

            <!-- Right Side - Login Card -->
            <div class="card loginCard">
                <div class="card-body" 
                     :style="{ direction: master.langDirection }">
                    
                    <!-- Logo -->
                    <div class="text-center mt-4">
                        <img :src="master.logo || defaultLogo" 
                             alt="" 
                             height="80"
                             style="width: 82px;display: inline-block;"
                             loading="lazy" />
                    </div>

                    <!-- Welcome Message -->
                    <div class="page-content text-center mb-4">
                        <p class="pagePera my-3">
                            <template v-if="master.langDirection === 'rtl'">
                                مرحبا بكم في
                                <span class="fw-bold text-primary text-capitalize">
                                    {{ master.appName }}
                                </span>
                            </template>
                            <template v-else>
                                {{ $t('Welcome') }}
                                <span class="fw-bold text-primary text-capitalize">
                                    {{ master.appName }}
                                </span>
                            </template>
                        </p>
                    </div>

                    <hr>

                    <!-- Login Form -->
                    <form @submit.prevent="loginFormSubmit()">
                        <!-- Phone Field -->
                        <div class="mb-3">
                            <label for="phone">
                                <template v-if="master.langDirection === 'rtl'">رقم الجوال</template>
                                <template v-else>{{ $t('Phone Number') }}</template>
                            </label>
                            <input 
                                type="text" 
                                id="phone"
                                v-model="loginFormData.phone"
                                :placeholder="master.langDirection === 'rtl' ? 'Enter phone Address' : $t('Enter phone number')"
                                class="form-control"
                            />
                            <span v-if="errors?.phone" class="text text-danger" role="alert">
                                <span>{{ errors.phone[0] }}</span>
                            </span>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3" style="display: none;">
                            <label for="password">
                                <template v-if="master.langDirection === 'rtl'">كلمة المرور</template>
                                <template v-else>{{ $t('Password') }}</template>
                            </label>
                            <div class="position-relative passwordInput">
                                <input 
                                    :type="showPassword ? 'text' : 'password'"
                                    id="password"
                                    v-model="loginFormData.password"
                                    :placeholder="'Enter Password'"
                                    class="form-control"
                                />
                                <span 
                                    class="eye" 
                                    @click="showPassword = !showPassword"
                                    :style="master.langDirection === 'rtl' ? { right: 'unset', left: '10px' } : {}">
                                    <i class="fa" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'" id="togglePassword"></i>
                                </span>
                            </div>
                            <span v-if="errors?.password" class="text text-danger" role="alert">
                                <span>{{ errors.password[0] }}</span>
                            </span>
                        </div>

                        <!-- Login Button -->
                        <button 
                            v-if="!isLoading"
                            class="btn loginButton" 
                            type="submit">
                            <template v-if="master.langDirection === 'rtl'">تسجيل الدخول</template>
                            <template v-else>{{ $t('Log in') }}</template>
                        </button>
                        
                        <!-- Loading Button -->
                        <button 
                            v-else
                            type="button"
                            disabled
                            class="btn loginButton"
                            style="opacity: 0.7; display: flex; justify-content: center; align-items: center; gap: 8px;">
                            {{ $t('Processing') }}
                            <LoadingSpin />
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { faApple, faFacebook } from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/solid';
import { jwtDecode } from "jwt-decode";
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { googleSdkLoaded } from 'vue3-google-login';
import GoogleIcon from '../icons/Google.vue';
import LoadingSpin from '../components/LoadingSpin.vue';
import { useAuth } from '../stores/AuthStore';
import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';

const router = useRouter();
const toast = useToast();
const authStore = useAuth();
const basketStore = useBasketStore();
const master = useMaster();

// Refs
const showPassword = ref(false);
const isLoading = ref(false);
const errors = ref({});
const loginFormData = ref({
    phone: '',
    password: '123456789'
});

// Assets
const backgroundImage = ref('/assets/images/admin-bg.svg');
const loginIllustration = ref('/assets/images/login-f.png');
const defaultFavicon = ref('/assets/favicon.png');
const defaultLogo = ref('/assets/logo.png');

onMounted(async () => {
    // Redirect if already logged in
    if (authStore.token && authStore.user) {
        router.push('/dashboard');
        return;
    }

    // Auto-fill in development
    if (master.app_environment === 'local') {
        loginFormData.value.phone = 'user@readyecommerce.com';
        loginFormData.value.password = 'secret';
    }

    // Load social login SDKs
    await loadFacebookSDK();
    initializeFB();
});

/**
 * Handle login form submission
 */
const loginFormSubmit = () => {
    errors.value = {};
    isLoading.value = true;
    
    axios.post('/login', loginFormData.value)
        .then((response) => {
            authStore.setToken(response.data.data.access.token);
            authStore.setUser(response.data.data.user);
            basketStore.fetchCart();
            authStore.fetchFavoriteProducts();
            
            isLoading.value = false;
            
            toast.success('Login Successful', {
                position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
            
            // Redirect to dashboard or previous page
            const redirectTo = router.currentRoute.value.query.redirect || '/dashboard';
            router.push(redirectTo);
        })
        .catch((error) => {
            isLoading.value = false;
            toast.error(error.response?.data?.message || 'Login failed', {
                position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
            errors.value = error.response?.data?.errors || {};
        });
};

/**
 * Google Login
 */
const googleLogin = () => {
    googleSdkLoaded((google) => {
        google.accounts.oauth2.initCodeClient({
            client_id: master.socialAuths.google.client_id,
            scope: 'email profile openid',
            redirect_uri: 'postmessage',
            callback: (response) => {
                if (response.code) {
                    sendCodeToBackend(response.code, 'google');
                }
            },
        }).requestCode();
    });
};

/**
 * Facebook Login
 */
const loginWithFacebook = () => {
    FB.login((response) => {
        if (response.authResponse) {
            FB.api('/me', { fields: 'name,email' }, (userInfo) => {
                sendCodeToBackend(response.authResponse?.accessToken, 'facebook', userInfo);
            });
        } else {
            console.error('User cancelled login or did not fully authorize.');
        }
    }, { scope: 'public_profile,email' });
};

/**
 * Apple Login
 */
const loginWithApple = async () => {
    try {
        await loadAppleSDK();
        
        window.AppleID.auth.init({
            clientId: master.socialAuths.apple?.client_id,
            scope: 'name email',
            redirectURI: master.socialAuths.apple.redirect_url,
            state: '123456',
            usePopup: true,
        });

        const data = await window.AppleID.auth.signIn();
        const { authorization: { id_token: token, code } } = data;

        if (token && code) {
            const decoded = jwtDecode(token);
            sendCodeToBackend('1122', 'apple', decoded);
        }
    } catch (error) {
        console.error('Error during Apple sign in:', error);
        toast.error('Apple sign in failed', {
            position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
    }
};

/**
 * Load Facebook SDK
 */
const loadFacebookSDK = () => {
    return new Promise((resolve) => {
        if (window.FB) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://connect.facebook.net/en_US/sdk.js';
        script.async = true;
        script.defer = true;
        script.onload = () => resolve();
        document.body.appendChild(script);
    });
};

/**
 * Initialize Facebook SDK
 */
const initializeFB = () => {
    window.fbAsyncInit = () => {
        FB.init({
            appId: master.socialAuths?.facebook?.client_id,
            autoLogAppEvents: true,
            xfbml: true,
            version: 'v20.0',
        });
    };
};

/**
 * Load Apple ID SDK
 */
const loadAppleSDK = () => {
    return new Promise((resolve, reject) => {
        if (window.AppleID) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load Apple ID SDK'));
        document.body.appendChild(script);
    });
};

/**
 * Send authorization code to backend
 */
async function sendCodeToBackend(code, provider = 'google', data = {}) {
    try {
        const response = await axios.post('/auth/' + provider + '/token', {
            code,
            data,
        });

        if (response.data?.data?.user) {
            authStore.setToken(response.data.data.access.token);
            authStore.setUser(response.data.data.user);
            basketStore.fetchCart();
            authStore.fetchFavoriteProducts();
            
            toast.success('Login Successful', {
                position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
            
            const redirectTo = router.currentRoute.value.query.redirect || '/dashboard';
            router.push(redirectTo);
        }
    } catch (error) {
        toast.error(error.response?.data?.message || 'Social login failed', {
            position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
    }
}
</script>

<style scoped>
/* Import Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap');

/* Login Page Wrapper - Matches body styling from login.css */
.login-page-wrapper {
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    font-family: "Almarai", serif;
    padding: 40px;
}

/* Login Section - Direct from login.css */
.login-section {
    width: 100%;
    max-width: 1280px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 40px;
}

/* Thumbnail - Flex 1 implicit from layout */
.thumbnail {
    flex: 1;
    position: relative;
}

.thumbnail img {
    width: 100%;
}

/* Logo Overlay - Exact from blade template */
.logo_right {
    position: absolute;
    top: 15%;
    right: 50%;
    transform: translateX(47%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0;
}

.logo_right img {
    height: 90px;
}

.logo_right .site_name {
    font-size: 26px;
    font-weight: 900;
    color: #848589;
    text-transform: capitalize;
    text-align: center;
    letter-spacing: 0.5px;
    font-family: 'Poppins', sans-serif;
}

/* Card Styling - Direct from login.css */
.card {
    background: white;
    border-radius: 16px !important;
}

.loginCard {
    width: 550px;
    border-radius: 16px !important;
    border: 3px solid #f6f7f9;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.card-body {
    padding: 24px;
}

/* Text Styling */
.text-center {
    text-align: center;
}

.mt-4 {
    margin-top: 1.5rem;
}

.page-content {
    text-align: center;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.pagePera {
    font-size: 16px;
    color: #24262D;
    margin-top: 0.75rem;
    margin-bottom: 0.75rem;
}

.my-3 {
    margin-top: 0.75rem;
    margin-bottom: 0.75rem;
}

.fw-bold {
    font-weight: 700;
}

.text-primary {
    color: var(--primary-color, #689c41);
}

.text-capitalize {
    text-transform: capitalize;
}

/* Form Styling */
.mb-3 {
    margin-bottom: 1rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.65rem 1rem !important;
    border-radius: 8px !important;
    border: 1px solid var(--primary-color, #689c41) !important;
    font-size: 14px;
}

.form-control::placeholder {
    font-size: 14px !important;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color, #689c41) !important;
}

/* Password Input */
.passwordInput {
    position: relative !important;
}

.position-relative {
    position: relative;
}

.eye {
    position: absolute;
    right: 10px;
    top: calc(50% - 10px);
    cursor: pointer;
}

/* Error Styling */
.text-danger {
    color: #dc3545;
    display: block;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Button Styling - Direct from login.css */
.btn {
    border: none;
    cursor: pointer;
    border-radius: 8px;
}

.loginButton {
    width: 100%;
    padding: 12px 0;
    font-size: 16px;
    margin-top: 20px;
    margin-bottom: 10px;
    background: var(--primary-color, #689c41);
    color: #fff;
    transition: 0.3s;
}

.loginButton:hover {
    background: var(--primary-color, #689c41);
    color: #fff;
    opacity: 0.9;
}

.loginButton:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Horizontal Rule */
hr {
    margin-top: 1rem;
    margin-bottom: 1rem;
    border: 0;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

/* Responsive Design - Direct from login.css */
@media (max-width: 1200px) {
    .loginCard {
        width: 480px;
    }
}

@media (max-width: 1024px) {
    .loginCard {
        width: 400px;
    }
}

@media (max-width: 768px) {
    .thumbnail {
        display: none !important;
    }
    
    .loginCard {
        width: 80%;
        margin: auto;
    }
}

@media (max-width: 580px) {
    .login-page-wrapper {
        padding: 20px;
    }
    
    .loginCard {
        width: 100%;
        flex-grow: 1;
        flex-shrink: 1;
    }
}

@media (max-width: 420px) {
    .loginCard {
        width: 340px;
    }
}
/* =============== ANIMATION STYLES =============== */

/* 1️⃣ خلفية متحركة بشكل خفيف (تحريك gradient) */
.login-page-wrapper {
    background: linear-gradient(120deg, #a8e063, #56ab2f);
    background-size: 200% 200%;
    animation: backgroundMove 10s ease infinite;
}

@keyframes backgroundMove {
    0% {
        background-position: 0% 25%;
    }
    50% {
        background-position: 50% 25%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* 2️⃣ دخول الكارد بانزلاق من الأسفل مع شفافية */
.loginCard {
    animation: fadeUp 0.9s ease-out forwards;
    opacity: 0;
    transform: translateY(40px);
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* 3️⃣ تحريك اللوجو بحركة Bounce خفيفة عند الظهور */
.text-center img {
    animation: bounceIn 1.2s cubic-bezier(0.215, 0.61, 0.355, 1);
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
    }
}

/* 4️⃣ إضافة تأثير Hover احترافي للزر */
.loginButton {
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.loginButton::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.2);
    transition: all 0.4s ease;
    z-index: 0;
}

.loginButton:hover::before {
    left: 0;
}

.card-body > * {
    opacity: 0;
    transform: translateY(15px);
    animation: fadeIn 0.8s ease forwards;
}

.card-body > *:nth-child(1) { animation-delay: 0.2s; }
.card-body > *:nth-child(2) { animation-delay: 0.3s; }
.card-body > *:nth-child(3) { animation-delay: 0.4s; }
.card-body > *:nth-child(4) { animation-delay: 0.5s; }
.card-body > *:nth-child(5) { animation-delay: 0.6s; }

@keyframes fadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

</style>
