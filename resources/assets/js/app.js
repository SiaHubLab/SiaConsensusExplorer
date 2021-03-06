require('./bootstrap');
import VueRouter from 'vue-router';

window.Vue = require('vue');

Vue.use(VueRouter);
Vue.component('mined-by', require('./components/mined-by.vue'));
Vue.component('api-health-main', require('./components/api-health-main.vue'));
Vue.component('api-health-endpoints', require('./components/api-health-endpoints.vue'));
Vue.component('home-pools-pie', require('./components/home-pools-pie.vue'));

Vue.component('mining-metrics-difficulty', require('./components/mining-metrics-difficulty.vue'));
Vue.component('mining-metrics-hashrate', require('./components/mining-metrics-hashrate.vue'));
Vue.component('ads', require('./components/ads.vue'));

const home = require('./components/home.vue');
const hash = require('./components/hash.vue');
const block = require('./components/block.vue');
const apiHealth = require('./components/api-health.vue');
const miningMetrics = require('./components/mining-metrics.vue');

const router = new VueRouter({
    mode: 'history',
    base: __dirname,
    routes: [{
            path: '/',
            component: home
        },
        {
            path: '/hash/:hash',
            component: hash
        },
        {
            path: '/block/:height',
            component: block
        },
        {
            path: '/api-health',
            component: apiHealth
        },
        {
            path: '/mining-metrics',
            component: miningMetrics
        }
    ]
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
