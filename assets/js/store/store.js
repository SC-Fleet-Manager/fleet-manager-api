import Vue from "vue";
import Vuex from 'vuex';
import orga_fleet from './modules/orga_fleet';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        orga_fleet,
    },
    state: {
        user: null,
        profile: null,
        citizens: {},
        organizations: {},
    },
    getters: {
        user(state) {
            return state.user;
        },
        citizen(state) {
            return state.profile;
        },
        getCitizen(state) {
            return (handle) => state.citizens[handle];
        },
        getOrganization(state) {
            return (sid) => state.organizations[sid];
        },
    },
    mutations: {
        updateUser(state, payload) {
            state.user = payload;
        },
        updateProfile(state, payload) {
            state.profile = payload;
        },
        updateCitizen(state, payload) {
            state.citizens[payload.actualHandle.handle] = payload;
        },
        updateOrganization(state, payload) {
            state.organizations[payload.organizationSid] = payload;
        },
    }
    // TODO : add a getter / action for checkAuth
});
