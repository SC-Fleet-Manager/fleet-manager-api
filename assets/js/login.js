import 'core-js/es6/promise'
import 'core-js/es6/string'
import 'core-js/es7/array'
// import cssVars from 'css-vars-ponyfill'
import Vue from 'vue'
import BootstrapVue from 'bootstrap-vue'
import Login from './Login'

// todo
// cssVars()

Vue.use(BootstrapVue);

/* eslint-disable no-new */
new Vue({
    el: '#app',
    render(h) {
        return h(Login, {
            props: {
                discordLoginUrl: this.$el.dataset.discordLoginUrl,
            }
        })
    }
});
