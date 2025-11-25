import axios from "axios";
import { defineStore } from "pinia";
import { useBasketStore } from "./BasketStore";
import { useChat } from "./ChatStore";

export const useAuth = defineStore("authStore", {
    state: () => ({
        user: null,
        addresses: [],
        token: null,
        favoriteProducts: 0,
        loginModal: false,
        registerModal: false,
        showAddressModal: false,
        showChangeAddressModal: false,
        orderCancel: false,
        favoriteRemove: false,
        selectedBranch: (() => {
            // Initialize selectedBranch from sessionStorage
            if (typeof window !== 'undefined') {
                const storedBranch = sessionStorage.getItem('selectedBranch');
                if (storedBranch) {
                    try {
                        return JSON.parse(storedBranch);
                    } catch (e) {
                        console.warn('Failed to parse selectedBranch from sessionStorage:', e);
                        sessionStorage.removeItem('selectedBranch');
                    }
                }
            }
            return null;
        })(),
        showBranchModal: false,
    }),

    getters: {
        getAddressById: (state) => (id) => {
            return state.addresses.find((address) => address.id == id);
        },
        hasSelectedBranch: (state) => {
            return state.selectedBranch !== null;
        },
    },

    actions: {
        setToken(token) {
            this.token = `Bearer ${token}`;
        },
        setUser(user) {
            this.user = user;
            // Fetch selected branch when user is set
            if (user && this.token) {
                this.fetchSelectedBranch();
            }
        },

        showLoginModal() {
            this.loginModal = true;
        },

        hideLoginModal() {
            this.loginModal = false;
        },

        fetchAddresses() {
            axios
                .get("/addresses", {
                    headers: {
                        Authorization: this.token,
                    },
                })
                .then((response) => {
                    this.addresses = response.data.data.addresses;
                    const basketStore = useBasketStore();
                    this.addresses.forEach((address) => {
                        if (address.is_default) {
                            basketStore.address = address;
                            return true;
                        } else {
                            basketStore.address = this.addresses[0];
                        }
                    });
                })
                .catch((error) => {
                    if (error.response.status === 401) {
                        this.token = null;
                        this.user = null;
                        this.addresses = [];
                        this.favoriteProducts = 0;
                    }
                });
        },
        fetchFavoriteProducts() {
            if (this.token) {
                axios
                    .get("/favorite-products", {
                        headers: {
                            Authorization: this.token,
                        },
                    })
                    .then((response) => {
                        this.favoriteProducts =
                            response.data.data.products?.length ?? 0;
                    })
                    .catch((error) => {
                        if (error.response.status === 401) {
                            this.token = null;
                            this.user = null;
                            this.addresses = [];
                        }
                    });
            } else {
                this.favoriteProducts = 0;
            }
        },

        logout() {
            axios
                .get("/logout", {
                    headers: {
                        Authorization: this.token,
                    },
                })
                .then((response) => {
                    const chatStore = useChat();
                    this.user = null;
                    this.addresses = [];
                    this.token = null;
                    this.favoriteProducts = 0;
                    this.selectedBranch = null;
                    // Clear selected branch from sessionStorage
                    if (typeof window !== 'undefined') {
                        sessionStorage.removeItem('selectedBranch');
                    }
                    chatStore.chats = [];
                    chatStore.activeShop = null;
                })
                .catch((error) => {
                    this.user = null;
                    this.addresses = [];
                    this.token = null;
                    this.favoriteProducts = 0;
                    this.selectedBranch = null;
                    // Clear selected branch from sessionStorage
                    if (typeof window !== 'undefined') {
                        sessionStorage.removeItem('selectedBranch');
                    }
                });
        },

        showBranchModal() {
            this.showBranchModal = true;
        },

        hideBranchModal() {
            this.showBranchModal = false;
        },

        setSelectedBranch(branch) {
            this.selectedBranch = branch;
            // Store selected branch in sessionStorage
            if (typeof window !== 'undefined') {
                if (branch) {
                    sessionStorage.setItem('selectedBranch', JSON.stringify(branch));
                } else {
                    sessionStorage.removeItem('selectedBranch');
                }
            }
        },

        async fetchSelectedBranch() {
            // First check sessionStorage for existing selection
            if (typeof window !== 'undefined') {
                const storedBranch = sessionStorage.getItem('selectedBranch');
                if (storedBranch) {
                    try {
                        this.selectedBranch = JSON.parse(storedBranch);
                        console.log('Selected branch loaded from sessionStorage:', this.selectedBranch);
                        return; // Don't make API call if we have sessionStorage data
                    } catch (e) {
                        console.warn('Failed to parse selectedBranch from sessionStorage:', e);
                        sessionStorage.removeItem('selectedBranch');
                    }
                }
            }

            // Fallback to API call if no sessionStorage data and user is authenticated
            if (!this.token) return;

            try {
                const response = await axios.get('/api/selected-branch', {
                    headers: {
                        Authorization: this.token,
                    },
                });
                this.selectedBranch = response.data.data.branch;
                // Store in sessionStorage for future use
                if (this.selectedBranch && typeof window !== 'undefined') {
                    sessionStorage.setItem('selectedBranch', JSON.stringify(this.selectedBranch));
                }
                console.log('Selected branch fetched from API:', this.selectedBranch);
            } catch (error) {
                console.error('Error fetching selected branch:', error);
                this.selectedBranch = null;
                // Clear sessionStorage on error
                if (typeof window !== 'undefined') {
                    sessionStorage.removeItem('selectedBranch');
                }
            }
        },
    },

    persist: {
        paths: [
            'user',
            'addresses',
            'token',
            'favoriteProducts',
            'loginModal',
            'registerModal',
            'showAddressModal',
            'showChangeAddressModal',
            'orderCancel',
            'favoriteRemove',
            'showBranchModal'
        ],
        // Note: selectedBranch is excluded from persistence and handled manually in sessionStorage
    },
});
