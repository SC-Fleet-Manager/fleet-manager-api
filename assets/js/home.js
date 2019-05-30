import Vue from 'vue'
import BootstrapVue from 'bootstrap-vue'
import Home from './Home'

Vue.use(BootstrapVue);

new Vue({
    el: '#app',
    render(h) {
        return h(Home, {
            props: {
                discordLoginUrl: this.$el.dataset.discordLoginUrl,
            }
        })
    }
});
