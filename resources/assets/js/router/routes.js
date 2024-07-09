//Pages
import Dashboard from "@/components/pages/Dashboard.vue";
// import ImportTracking from './components/pages/ImportTracking'
import Order from "@/components/pages/Order.vue";

const routes = [
    {
        path: "/",
        name: "home",
        component: Dashboard,
    },
    // {
    //     path: '/import-tracking',
    //     name: 'import tracking',
    //     meta: {minRole: 50},
    //     component: ImportTracking
    // },
    {
        path: "/order/:id",
        name: "order",
        component: Order,
    },
    {
        path: "/pharmacies",
        name: "pharmacies",
        meta: { minRole: 50 },
        //component: Users
        component: () =>
            import(
                /* webpackChunkName: "Pharmacies" */ "@/components/pages/pharmacy/Pharmacies.vue"
            ),
    },
    {
        path: "/pharmacies/new",
        name: "new pharmacy",
        meta: { minRole: 50 },
        //component: Users
        component: () =>
            import(
                /* webpackChunkName: "NewPharmacy" */ "@/components/pages/pharmacy/NewPharmacy.vue"
            ),
    },
    {
        path: "/pharmacies/:id",
        name: "pharmacy",
        meta: { minRole: 50 },
        component: () =>
            import(
                /* webpackChunkName: "Pharmacy" */ "@/components/pages/pharmacy/Pharmacy.vue"
            ),
    },
    {
        path: "/clients",
        name: "clients",
        meta: { minRole: 50 },
        //component: Clients
        component: () =>
            import(
                /* webpackChunkName: "Clients" */ "@/components/pages/client/Clients.vue"
            ),
    },
    {
        path: "/clients/new",
        name: "new client",
        meta: { parent: "clients", minRole: 50 },
        component: () =>
            import(
                /* webpackChunkName: "New" */ "@/components/pages/client/New.vue"
            ),
    },
    {
        path: "/clients/:id",
        name: "client",
        meta: { parent: "clients", minRole: 50 },
        //component: Doctors
        component: () =>
            import(
                /* webpackChunkName: "Prescriber" */ "@/components/pages/client/Client.vue"
            ),
    },
    {
        path: "/users",
        name: "users",
        meta: { minRole: 30 },
        component: () =>
            import(
                /* webpackChunkName: "Users" */ "@/components/pages/user/Users.vue"
            ),
    },
    {
        path: "/users/new",
        name: "new user",
        meta: { parent: "users", minRole: 30 },
        component: () =>
            import(
                /* webpackChunkName: "NewUser" */ "@/components/pages/user/NewUser.vue"
            ),
    },
    {
        path: "/users/:id",
        name: "user",
        meta: { parent: "users", minRole: 30 },
        component: () =>
            import(
                /* webpackChunkName: "User" */ "@/components/pages/user/User.vue"
            ),
    },
    {
        path: "/reports",
        name: "reports",
        meta: { minRole: 50 },
        component: () =>
            import(
                /* webpackChunkName: "Reports" */ "@/components/pages/Reports.vue"
            ),
    },
    {
        path: "/orders",
        name: "orders",
        // meta: {minRole: 50},
        component: () =>
            import(
                /* webpackChunkName: "Orders" */ "@/components/pages/Orders.vue"
            ),
    },
    {
        path: "/info",
        name: "App Info",
        //meta: { minRole: 20 },
        // component: FMD
        component: () =>
            import(
                /* webpackChunkName: "Info" */ "@/components/pages/general/Info.vue"
            ),
    },
    {
        path: "/404",
        name: "404",
        //component: NotFound
        component: () =>
            import(
                /* webpackChunkName: "NotFound" */ "@/components/pages/generic/NotFound.vue"
            ),
    },
    // {
    //     path: '/notallowed',
    //     name: 'not allowed',
    //     //component: NotAllowed
    //     component: () => import(/* webpackChunkName: "NotAllowed" */ './components/pages/generic/NotAllowed.vue')
    // },
    {
        path: "/:catchAll(.*)",
        redirect: "/404"
    },
];

export default routes;
