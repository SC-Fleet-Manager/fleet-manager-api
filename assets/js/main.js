import 'core-js/es6/promise'
import 'core-js/es6/string'
import 'core-js/es7/array'
import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue';
import VueClipboard from 'vue-clipboard2';
import App from './App';
import router from './router';
import store from './store/store';
import toastr from 'toastr';
import 'toastr/toastr.scss';

toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "positionClass": "toast-bottom-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

Vue.use(BootstrapVue);
Vue.use(VueClipboard);

new Vue({
    el: '#app',
    router,
    store,
    template: '<App/>',
    components: {App},
});
