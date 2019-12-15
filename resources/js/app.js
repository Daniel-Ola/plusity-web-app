import Vue from 'vue'
import { router } from './vue-app/routes/index';
import vuetify from './vue-app/plugins/vuetify'

import App from './vuejs/App'


const app = new Vue({
    el: '#app',
    components: { App },
    router,
    vuetify,
});