import Vue from "vue";
import Vuex from 'vuex';
import orga_fleet from './modules/orga_fleet';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        orga_fleet,
    },
    state: {
        profile: null,
        citizens: {},
        organizations: {},
    },
    getters: {
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
