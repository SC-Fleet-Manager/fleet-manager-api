// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import 'core-js/es6/promise'
import 'core-js/es6/string'
import 'core-js/es7/array'
// import cssVars from 'css-vars-ponyfill'
import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue';
import VueClipboard from 'vue-clipboard2';
import App from './App';
import router from './router';

Vue.use(BootstrapVue);
Vue.use(VueClipboard);

new Vue({
    el: '#app',
    router,
    template: '<App/>',
    components: {App},
});
