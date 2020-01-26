import Vue from 'vue';
// import {
//     LayoutPlugin,
//     FormPlugin,
//     FormGroupPlugin,
//     FormInputPlugin,
//     FormCheckboxPlugin,
//     InputGroupPlugin,
//     ButtonPlugin,
//     AlertPlugin,
//     ModalPlugin,
//     CollapsePlugin,
//     VBTooltipPlugin,
// } from 'bootstrap-vue';
import Home from './Home';

// Vue.use(LayoutPlugin);
// Vue.use(ModalPlugin);
// Vue.use(FormPlugin);
// Vue.use(FormGroupPlugin);
// Vue.use(FormInputPlugin);
// Vue.use(InputGroupPlugin);
// Vue.use(ButtonPlugin);
// Vue.use(AlertPlugin);
// Vue.use(CollapsePlugin);
// Vue.use(FormCheckboxPlugin);
// Vue.use(VBTooltipPlugin);

new Vue({
    el: '#app',
    render(h) {
        return h(Home);
    }
});
