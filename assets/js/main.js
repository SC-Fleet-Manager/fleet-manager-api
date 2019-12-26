import Vue from 'vue';
import {
    LayoutPlugin,
    FormPlugin,
    FormGroupPlugin,
    FormInputPlugin,
    FormCheckboxPlugin,
    FormFilePlugin,
    FormRadioPlugin,
    InputGroupPlugin,
    ButtonPlugin,
    ButtonGroupPlugin,
    AlertPlugin,
    CardPlugin,
    ModalPlugin,
    DropdownPlugin,
    SpinnerPlugin,
    LinkPlugin,
    NavPlugin,
    NavbarPlugin,
    PaginationPlugin,
    BadgePlugin,
    CollapsePlugin,
    ProgressPlugin,
    TablePlugin,
    VBTooltipPlugin,
} from 'bootstrap-vue';
import VueToastr from "vue-toastr";
import App from './App';
import router from './router';
import store from './store/store';

Vue.use(LayoutPlugin);
Vue.use(FormPlugin);
Vue.use(FormGroupPlugin);
Vue.use(FormInputPlugin);
Vue.use(FormCheckboxPlugin);
Vue.use(FormFilePlugin);
Vue.use(FormRadioPlugin);
Vue.use(InputGroupPlugin);
Vue.use(ButtonPlugin);
Vue.use(ButtonGroupPlugin);
Vue.use(AlertPlugin);
Vue.use(CardPlugin);
Vue.use(ModalPlugin);
Vue.use(DropdownPlugin);
Vue.use(SpinnerPlugin);
Vue.use(LinkPlugin);
Vue.use(NavPlugin);
Vue.use(NavbarPlugin);
Vue.use(PaginationPlugin);
Vue.use(BadgePlugin);
Vue.use(CollapsePlugin);
Vue.use(ProgressPlugin);
Vue.use(TablePlugin);
Vue.use(VBTooltipPlugin);

Vue.use(VueToastr, {
    defaultTimeout: 3000,
    defaultProgressBar: false,
    defaultProgressBarValue: 0,
    defaultPosition: "toast-bottom-right",
    defaultCloseOnHover: false,
    defaultClassNames: ["animated", "zoomInUp"]
});

new Vue({
    el: '#app',
    router,
    store,
    template: '<App/>',
    components: {App},
});
