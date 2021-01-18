import Vue from 'vue';
import VueTest from './components/VueTest'
import './styles/app.scss';

require('bootstrap');

// start the Stimulus application
import './bootstrap';

// Vuejs
Vue.options.delimiters = ['${', '}$']

let vue = new Vue({
    el: '#kbin',
    data: {
        message: 'test'
    },
    components: {
        VueTest
    },
});
console.log(vue);
