require('./bootstrap');
import VueRouter from 'vue-router';

window.Vue = require('vue');

Vue.use(VueRouter);

const home = require('./components/home.vue');

const router = new VueRouter({
    mode: 'history',
    base: __dirname,
    routes: [{
        path: '/',
        component: home
    }]
})

const BaseVue = Vue.extend({
    router
});

var divs = document.querySelectorAll('.app');

[].forEach.call(divs, function(div) {
    // do whatever
    new BaseVue({
        el: div
    });
});
