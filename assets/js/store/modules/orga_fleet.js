import axios from 'axios';

const state = {
    selectedSid: null,
    selectedIndex: null, // index of the ship family in the page. used to compute where the details will open
    selectedShipFamily: null, // {chassisId: "00", name: "xx", ...}
    selectedShipVariants: [], // [{countTotalOwners: 0, countTotalShips: 0, shipInfo: {id: "00", name: "xxx", mediaThumbUrl: "https://...", ...}}, {...}]
    shipVariantUsersTrackChanges: 0, // +1 at each update
    shipVariantUsers: {}, // {"<ship id>": {...}}
};

const getters = {
    selectedIndex(state) {
        return state.selectedIndex;
    },
    selectedShipFamily(state) {
        return state.selectedShipFamily;
    },
    selectedShipVariants(state) {
        return state.selectedShipVariants;
    },
    shipVariantUser(state) {
        return (shipId) => state.shipVariantUsers[shipId];
    },
};

const mutations = {
    updateSelectedShipFamily(state, payload) {
        state.shipVariantUsers = {};
        state.selectedShipFamily = payload.shipFamily;
        state.selectedShipVariants = payload.shipVariants;
        state.selectedIndex = payload.selectedIndex;
    },
    updateShipVariantsUsers(state, { users, shipId }) {
        if (!state.shipVariantUsers[shipId]) {
            state.shipVariantUsers[shipId] = [];
        }
        for (let user of users) {
            state.shipVariantUsers[shipId].push(user);
        }
        ++state.shipVariantUsersTrackChanges;
    }
};

const actions = {
    async loadShipVariantUsers({commit, state}, { ship, page }) {
        page = page > 0 ? page : 1;
        axios.get(`/orga-fleets/${state.selectedSid}/users/${ship.shipInfo.name}`, {
            params: { page },
        }).then(response => {
            commit('updateShipVariantsUsers', {
                users: response.data,
                shipId: ship.shipInfo.id,
            });
        }).catch(err => {
            // this.checkAuth(err.response);
            console.error(err);
        });
    },
    async selectShipFamily({commit, state}, payload) {
        if (payload.index === state.selectedIndex) { // we want to reselect same shipFamily : we close it
            commit('updateSelectedShipFamily', {
                selectedIndex: null,
                shipFamily: null,
                shipVariants: [],
            });
            return;
        }
        try {
            const response = await axios.get(`/orga-fleets/${state.selectedSid}/${payload.shipFamily.chassisId}`);
            commit('updateSelectedShipFamily', {
                selectedIndex: payload.index,
                shipFamily: payload.shipFamily,
                shipVariants: response.data,
            });
        } catch (err) {
            // this.checkAuth(err.response);
            // if (err.response.data.errorMessage) {
            //     toastr.error(err.response.data.errorMessage);
            // }
            console.error(err);
        }
    }
};

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
};
