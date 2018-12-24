import Vue from 'vue'
import Router from 'vue-router'

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

export default new Router({
    mode: 'hash', // https://router.vuejs.org/api/#mode
    linkActiveClass: 'open active',
    scrollBehavior: () => ({y: 0}),
    routes: [
        {
            path: '/',
            redirect: '/my-fleet',
            name: 'Home',
            component: DefaultContainer,
            children: [
                {
                    path: 'corpo-fleets',
                    name: 'My corpo fleets',
                    component: CorpoFleets
                },
                {
                    path: 'my-fleet',
                    name: 'My Fleet',
                    component: MyFleet
                },
                {
                    path: 'profile',
                    name: 'Profile',
                    component: Profile
                },
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
})
