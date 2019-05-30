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
    },
    getters: {
        citizen(state) {
            return state.profile;
        }
    },
    mutations: {
        updateProfile(state, payload) {
            state.profile = payload;
        }
    }
    // TODO : add a getter / action for checkAuth
});
