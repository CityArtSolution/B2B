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
        selectedBranch: null,
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
                    chatStore.chats = [];
                    chatStore.activeShop = null;
                })
                .catch((error) => {
                    this.user = null;
                    this.addresses = [];
                    this.token = null;
                    this.favoriteProducts = 0;
                    this.selectedBranch = null;
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
        },

        async fetchSelectedBranch() {
            if (!this.token) return;

            try {
                const response = await axios.get('/api/selected-branch', {
                    headers: {
                        Authorization: this.token,
                    },
                });
                this.selectedBranch = response.data.data.branch;
            } catch (error) {
                console.error('Error fetching selected branch:', error);
                this.selectedBranch = null;
            }
        },
    },

    persist: true,
});
