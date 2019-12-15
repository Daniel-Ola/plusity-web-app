import Vue from 'vue'
import VueRouter from 'vue-router'
import Welcome from '../Welcome';

Vue.use(VueRouter)

export const router = new VueRouter({
  mode: 'history',
  routes: [
      {
          path: '/',
          name: 'home',
          component: Welcome
      },
  ],
});