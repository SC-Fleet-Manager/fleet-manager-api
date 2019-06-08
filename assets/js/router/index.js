import Vue from 'vue'
import Router from 'vue-router'
import axios from 'axios';

// Containers
const DefaultContainer = () => import('../containers/DefaultContainer');

// Views
const CorpoFleets = () => import('../views/CorpoFleets');
const Profile = () => import('../views/Profile');
const MyFleet = () => import('../views/MyFleet');

// Views - Pages
const PrivacyPolicy = () => import('../views/PrivacyPolicy');
const Page404 = () => import('../views/Page404');

Vue.use(Router);

const router = new Router({
    mode: 'history',
    linkActiveClass: 'open active',
    scrollBehavior: () => ({y: 0}),
    routes: [
        {
            path: '/',
            redirect: '/profile',
            name: 'Home',
            component: DefaultContainer,
            meta: {
                requireAuth: true,
            },
            children: [
                {
                    path: 'organization-fleet/:sid',
                    name: 'Organization fleet',
                    component: CorpoFleets,
                    props: true
                },
                {
                    path: 'organizations-fleets',
                    name: 'Organizations\' fleets',
                    component: CorpoFleets,
                    beforeEnter(to, from, next) {
                        axios.get('/api/profile/').then(response => {
                            const citizen = response.data.citizen;
                            if (citizen === null ||  citizen.organizations.length === 0) {
                                next();
                            }
                            const defaultOrga = citizen.mainOrga !== null ? citizen.mainOrga.organization.organizationSid : (
                                citizen.organizations[0].organization !== null ? citizen.organizations[0].organization.organizationSid : citizen.organizations[0].organizationSid
                            );
                            next({ path: `/organization-fleet/${defaultOrga}` });
                        }).catch(err => {
                            next();
                        });
                    },
                    meta: {
                        requireAuth: true,
                    }
                },
                {
                    path: 'citizen/:userHandle',
                    name: 'User fleet',
                    component: MyFleet,
                    props: true
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
            path: '/privacy-policy',
            name: 'Privacy policy',
            component: PrivacyPolicy
        },
        {
            path: '*',
            component: Page404
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
