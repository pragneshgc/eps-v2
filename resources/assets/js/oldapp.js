import "./bootstrap";

import Vue from "vue/dist/vue.js";
import VueRouter from "vue-router";
import vSelect from "vue-select";
import VueSweetalert2 from "vue-sweetalert2";
import routes from "./router/routes";
import AppLayout from "./components/layouts/Layout.vue";
import Toasted from "vue-toasted";

import TableComponent from "./components/TableComponent.vue";
/**
 * Components
 * uncomment if needed
 */
Vue.use(VueRouter);
Vue.use(VueSweetalert2);
Vue.use(Toasted);
Vue.component("v-select", vSelect);
//Vue.component('Chart', require('./components/ChartComponent.vue'));
//Vue.component('PieChart', require('./components/PieChartComponent.vue'));
Vue.component("DataTable", TableComponent);
//Vue.component('InventoryDataTable', require('./components/InventoryTableComponent.vue'));
//Vue.component('MapChart', require('./components/MapComponent.vue'));

/**
 * Router setup
 * Uses routes.js file for routes list
 */
const router = new VueRouter({
    //mode: 'history',
    routes,
    scrollBehavior(to, from, savedPosition) {
        return { x: 0, y: 0 };
    },
    linkActiveClass: "active",
    linkExactActiveClass: "exact-active",
});

/**
 * Router middleware
 */
router.beforeEach((to, from, next) => {
    if (to.meta.minRole > userInfo.role) {
        router.push({ path: "/notallowed" });
    } else {
        next();
    }
});

/**
 * Startup the Vue app
 */
const app = new Vue({
    el: "#app",
    components: { AppLayout },
    data: {
        online: true,
    },
    created: function () {
        window.addEventListener("online", () => {
            this.online = true;
        });
        window.addEventListener("offline", () => {
            this.online = false;
        });
    },
    router,
});

if (import.meta.env.PROD) {
    Vue.config.devtools = false
}
