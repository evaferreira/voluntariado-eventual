Vue.config.devtools = true;
import Vue from 'vue';
import VueRouter from 'vue-router';
import Login from './components/login.vue';
import Filtro from './components/filtro.vue';
import ContenedorDeTarjetas from './components/contenedorDeTarjetas';
import Inscripciones from './components/inscripciones';
import Registro from './components/registro';
import Datepicker from 'vuejs-datepicker';

require('./bootstrap');

window.Vue = require('vue');
window.events = new Vue();

Vue.component('filtro', Filtro);
Vue.component('login', Login);
Vue.component('contenedor-de-tarjetas', ContenedorDeTarjetas);
Vue.component('inscripciones', Inscripciones);
Vue.component('registro', Registro);
Vue.component('datepicker', Datepicker);


window.app = new Vue({
    el: "#app",
    data() {
      return {
          meta: [],
      }
    }
});


