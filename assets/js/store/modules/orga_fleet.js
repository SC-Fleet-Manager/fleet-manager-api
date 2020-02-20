import axios from 'axios';

const state = {
    selectedSid: null,
    selectedIndex: null, // index of the ship family in the page. used to compute where the details will open
    selectedShipFamily: null, // {chassisId: "00", name: "xx", ...}
    selectedShipVariants: [], // [{countTotalOwners: 0, countTotalShips: 0, shipInfo: {id: "00", name: "xxx", mediaThumbUrl: "https://...", ...}}, {...}]
    shipVariantUsersTrackChanges: 0, // +1 at each update TODO : only update the right ShipVariant instead of all !
    shipVariantUsers: {}, // {"<ship id>": {...}}
    shipVariantUsersMetadata: {}, // { page, lastPage, total }
    shipVariantUsersLoadedPages: {}, // {"<ship id>": [1, 2, 3...]}
    shipVariantHiddenUsers: {}, // {"<ship id>": <count hidden>}
    filterShipName: [],
    filterCitizenId: [],
    filterShipSize: [],
    filterShipStatus: null,
};

const getters = {
    usersInfos(state) {
        return state.usersInfos;
    },
    selectedSid(state) {
        return state.selectedSid;
    },
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
    updateShipVariantsHiddenUsers(state, { shipId, countHidden }) {
        state.shipVariantHiddenUsers[shipId] = countHidden;
        ++state.shipVariantUsersTrackChanges;
    },
    updateShipVariantsUsers(state, { users, page, lastPage, total, shipId }) {
        if (!state.shipVariantUsers[shipId]) {
            state.shipVariantUsers[shipId] = [];
        }
        if (!state.shipVariantUsersLoadedPages[shipId]) {
            state.shipVariantUsersLoadedPages[shipId] = [];
        }
        if (state.shipVariantUsersLoadedPages[shipId].indexOf(page) >= 0) {
            return;
        }
        for (let user of users) {
            state.shipVariantUsers[shipId].push(user);
        }
        state.shipVariantUsersMetadata[shipId] = { page, lastPage, total };
        state.shipVariantUsersLoadedPages[shipId].push(page);
        ++state.shipVariantUsersTrackChanges;
    },
    updateSid(state, value) {
        if (state.selectedSid === value) {
            return;
        }
        state.selectedSid = value;
    }
};

const actions = {
    async loadShipVariantUsers({ commit, state }, { ship, page, clean }) {
        clean = clean !== undefined ? clean : false;
        page = page > 0 ? page : 1;
        if (clean) {
            state.shipVariantUsersLoadedPages[ship.shipInfo.id] = [];
        }
        axios.get(`/api/fleet/orga-fleets/${state.selectedSid}/users/${ship.shipInfo.name}`, {
            params: {
                page,
                'filters[shipNames]': state.filterShipName,
                'filters[citizenIds]': state.filterCitizenId,
                'filters[shipSizes]': state.filterShipSize,
                'filters[shipStatus]': state.filterShipStatus,
            },
        }).then(response => {
            commit('updateShipVariantsUsers', {
                users: response.data.users,
                page: response.data.page,
                lastPage: response.data.lastPage,
                total: response.data.total,
                shipId: ship.shipInfo.id,
            });
            if (response.data.lastPage === response.data.page) {
                // we are on the last page : load how many hidden there are.
                axios.get(`/api/fleet/orga-fleets/${state.selectedSid}/hidden-users/${ship.shipInfo.name}`).then(response => {
                    commit('updateShipVariantsHiddenUsers', {
                        shipId: ship.shipInfo.id,
                        countHidden: response.data.hiddenUsers,
                    });
                });
            }
        });
    },
    async selectShipFamily({commit, state}, payload) {
        if (payload.index === null || payload.index === state.selectedIndex) { // we want to reselect same shipFamily : we close it
            commit('updateSelectedShipFamily', {
                selectedIndex: null,
                shipFamily: null,
                shipVariants: [],
            });
            return;
        }
        try {
            const response = await axios.get(`/api/fleet/orga-fleets/${state.selectedSid}/${payload.shipFamily.chassisId}`, {
                params: {
                    'filters[shipNames]': state.filterShipName,
                    'filters[citizenIds]': state.filterCitizenId,
                    'filters[shipSizes]': state.filterShipSize,
                    'filters[shipStatus]': state.filterShipStatus,
                },
            });
            commit('updateSelectedShipFamily', {
                selectedIndex: payload.index,
                shipFamily: payload.shipFamily,
                shipVariants: response.data,
            });
        } catch (err) {

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
