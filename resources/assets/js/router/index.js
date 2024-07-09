import { createRouter, createWebHashHistory } from "vue-router";
import routes from "./routes";

const router = createRouter({
    history: createWebHashHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return { x: 0, y: 0 };
    },
    linkActiveClass: 'active',
    linkExactActiveClass: 'exact-active'
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

export default router;
