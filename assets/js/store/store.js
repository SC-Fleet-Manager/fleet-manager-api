import Vue from "vue";
import Vuex from 'vuex';
import orga_fleet from './modules/orga_fleet';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {},
    modules: {
        orga_fleet,
    },
    // TODO : add a getter / action for checkAuth
});
