import Vue from 'vue';
import Home from './Home';
import VModal from 'vue-js-modal'

Vue.use(VModal);

new Vue({
    el: '#app',
    render(h) {
        return h(Home);
    }
});
