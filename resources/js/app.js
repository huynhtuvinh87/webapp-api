window.Vue = require('vue');

import axios from 'axios'
import Vuetify from 'vuetify'

Vue.prototype.$http = axios;

Vue.use(Vuetify,
    {
        theme: {
            primary: '#F7921E',   //orange
            alpha : '#006837', //green
            primaryFont: '#717171', //grey
            darkFont: '#444444',    // dark grey
            lightGrey: '#EEEEEE',    //light grey

            secondary: '#b0bec5',
            greys: '#b3b3b3',
            accent: '#8c9eff',
            error: '#b71c1c',
            update: '#006837'
        }
    })

const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key)))

Vue.component('password-gen-field', require('./components/PasswordGenField'))

const app = new Vue({
    el: '#app'
});
