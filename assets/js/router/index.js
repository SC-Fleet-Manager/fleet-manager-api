import Vue from 'vue'
import Router from 'vue-router'
import axios from 'axios';

// Containers
const DefaultContainer = () => import('../containers/DefaultContainer');

// Views
const CorpoFleets = () => import('../views/CorpoFleets');
const UpdateFleetFile = () => import('../views/UpdateFleetFile');
const Profile = () => import('../views/Profile');
const MyFleet = () => import('../views/MyFleet');

// Views - Pages
const Page404 = () => import('../views/pages/Page404');
const Page500 = () => import('../views/pages/Page500');
const Login = () => import('../views/pages/Login');

Vue.use(Router);

const router = new Router({
    mode: 'hash', // https://router.vuejs.org/api/#mode
    linkActiveClass: 'open active',
    scrollBehavior: () => ({y: 0}),
    routes: [
        {
            path: '/',
            redirect: '/my-fleet',
            name: 'Home',
            component: DefaultContainer,
            meta: {
                requireAuth: true,
            },
            children: [
                {
                    path: 'organizations-fleets',
                    name: 'Organizations\' fleets',
                    component: CorpoFleets,
                    meta: {
                        requireAuth: true,
                    }
                },
                {
                    path: 'my-fleet',
                    name: 'My Fleet',
                    component: MyFleet,
                    meta: {
                        requireAuth: true,
                    }
                },
                {
                    path: 'user/:userHandle',
                    name: 'User fleet',
                    component: MyFleet,
                    props: true,
                    meta: {
                        requireAuth: false,
                    }
                },
                {
                    path: 'profile',
                    name: 'Profile',
                    component: Profile,
                    meta: {
                        requireAuth: true,
                    }
                }
            ]
        },
        {
            path: '/pages',
            redirect: '/pages/404',
            name: 'Pages',
            component: {
                render(c) {
                    return c('router-view')
                }
            },
            children: [
                {
                    path: '404',
                    name: 'Page404',
                    component: Page404
                },
                {
                    path: '500',
                    name: 'Page500',
                    component: Page500
                },
                {
                    path: 'login',
                    name: 'Login',
                    component: Login
                }
            ]
        }
    ]
});
router.beforeEach((to, from, next) => {
    if (!to.meta.requireAuth) {
        // no need auth
        next();
        return;
    }

    // need auth
    axios.get('/api/me').then(response => {
        next();
    }).catch(err => {
        const status = err.response.status;
        const data = err.response.data;
        if ((status === 401 && data.error === 'no_auth')
            || (status === 403 && data.error === 'forbidden')) {
            window.location = data.loginUrl;
        }
    });
});

export default router;
