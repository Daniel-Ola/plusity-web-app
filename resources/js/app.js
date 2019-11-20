import Vue from 'vue'
import { router } from './components/routes/index';
import vuetify from './components/plugins/vuetify'

import App from './components/App'


const app = new Vue({
    el: '#app',
    components: { App },
    router,
    vuetify,
});