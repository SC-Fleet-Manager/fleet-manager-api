import Vue from 'vue'
import Router from 'vue-router'
import axios from 'axios';
import toastr from 'toastr';
import store from '../store/store';

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

async function getProfile(force)
{
    const citizen = store.getters.citizen;
    if (!force && citizen) {
        return citizen;
    }
    const response = await axios.get('/api/profile/');
    store.commit('updateProfile', response.data.citizen);

    return response.data.citizen;
}
async function getCitizen(handle)
{
    const citizen = store.getters.getCitizen(handle);
    if (citizen) {
        return citizen;
    }
    const response = await axios.get(`/api/citizen/${handle}`);
    store.commit('updateCitizen', response.data);

    return response.data;
}
async function getOrganization(sid)
{
    const orga = store.getters.getOrganization(sid);
    if (orga) {
        return orga;
    }
    const response = await axios.get(`/api/organization/${sid}`);
    store.commit('updateOrganization', response.data);

    return response.data;
}

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
                    props: true,
                    meta: {
                        titleTag: async (to) => {
                            try {
                                const orga = await getOrganization(to.params.sid);
                                return `${orga.name} Organization - Fleet Manager`;
                            } catch (err) {}
                            return `Unknown organization - Fleet Manager`;
                        },
                        metaTags: [
                            {
                                name: 'description',
                                content: async (to) => {
                                    try {
                                        const orga = await getOrganization(to.params.sid);
                                        return `The Star Citizen organization of ${orga.name}.`;
                                    } catch (err) {}
                                    return '';
                                },
                            },
                            {
                                property: 'og:description',
                                content: async (to) => {
                                    try {
                                        const orga = await getOrganization(to.params.sid);
                                        return `The Star Citizen organization of ${orga.name}.`;
                                    } catch (err) {}
                                    return '';
                                },
                            },
                            {
                                property: 'og:url',
                                content: async (to) => {
                                    return `${window.location.protocol}//${window.location.host}${to.path}`;
                                },
                            },
                            {
                                property: 'og:image',
                                content: async (to) => {
                                    try {
                                        const orga = await getOrganization(to.params.sid);
                                        return orga.avatarUrl;
                                    } catch (err) {}
                                    return '';
                                },
                            }
                        ],
                    },
                },
                {
                    path: 'organizations-fleets',
                    name: 'Organizations\' fleets',
                    component: CorpoFleets,
                    async beforeEnter(to, from, next) {
                        try {
                            const citizen = await getProfile();
                            if (citizen === null || citizen.organizations.length === 0) {
                                toastr.error('Sorry, you have no visible organizations to access this page.');
                                next({ path: '/profile' });
                            }
                            const defaultOrga = citizen.mainOrga !== null ? citizen.mainOrga.organization.organizationSid : (
                                citizen.organizations[0].organization !== null ? citizen.organizations[0].organization.organizationSid : citizen.organizations[0].organizationSid
                            );
                            next({ path: `/organization-fleet/${defaultOrga}` });
                        } catch (err) {
                            next();
                        }
                    },
                    meta: {
                        requireAuth: true,
                    }
                },
                {
                    path: 'my-fleet',
                    name: 'My Fleet',
                    async beforeEnter(to, from, next) {
                        try {
                            const citizen = await getProfile();
                            next({ path: `/citizen/${citizen.actualHandle.handle}` });
                        } catch (err) {
                            next();
                        }
                    },
                },
                {
                    path: 'citizen/:userHandle',
                    name: 'User fleet',
                    component: MyFleet,
                    props: true,
                    meta: {
                        titleTag: async (to) => {
                            try {
                                const citizen = await getCitizen(to.params.userHandle);
                                return `${citizen.nickname} Fleet - Fleet Manager`;
                            } catch (err) {}
                            return `Unknown citizen fleet - Fleet Manager`;
                        },
                        metaTags: [
                            {
                                name: 'description',
                                content: async (to) => {
                                    try {
                                        const citizen = await getCitizen(to.params.userHandle);
                                        return `The Star Citizen fleet of ${citizen.nickname}.`;
                                    } catch (err) {}
                                    return '';
                                },
                            },
                            {
                                property: 'og:description',
                                content: async (to) => {
                                    try {
                                        const citizen = await getCitizen(to.params.userHandle);
                                        return `The Star Citizen fleet of ${citizen.nickname}.`;
                                    } catch (err) {}
                                    return '';
                                },
                            },
                            {
                                property: 'og:url',
                                content: async (to) => {
                                    return `${window.location.protocol}//${window.location.host}${to.path}`;
                                },
                            },
                            {
                                property: 'og:image',
                                content: async (to) => {
                                    try {
                                        const citizen = await getCitizen(to.params.userHandle);
                                        return citizen.avatarUrl;
                                    } catch (err) {}
                                    return '';
                                },
                            }
                        ],
                    },
                },
                {
                    path: 'profile',
                    name: 'Profile',
                    component: Profile,
                    meta: {
                        requireAuth: true,
                        titleTag: 'Profile - Fleet Manager',
                        metaTags: [
                            {
                                name: 'description',
                                content: '',
                            },
                            {
                                property: 'og:description',
                                content: '',
                            },
                            {
                                property: 'og:url',
                                content: async (to) => {
                                    return `${window.location.protocol}//${window.location.host}${to.path}`;
                                },
                            },
                            {
                                property: 'og:image',
                                content: `${window.location.protocol}//${window.location.host}/icons/favicon-96x96.png`,
                            }
                        ],
                    },
                }
            ]
        },
        {
            path: '/privacy-policy',
            name: 'Privacy policy',
            component: PrivacyPolicy,
            meta: {
                titleTag: 'Privacy policy - Fleet Manager',
                metaTags: [
                    {
                        name: 'description',
                        content: 'The privacy policy of Fleet Manager.',
                    },
                    {
                        property: 'og:description',
                        content: 'The privacy policy of Fleet Manager.',
                    },
                    {
                        property: 'og:url',
                        content: async (to) => {
                            return `${window.location.protocol}//${window.location.host}${to.path}`;
                        },
                    },
                    {
                        property: 'og:image',
                        content: `${window.location.protocol}//${window.location.host}/icons/favicon-96x96.png`,
                    }
                ],
            },
        },
        {
            path: '/404',
            component: Page404,
            meta: {
                titleTag: '404 - Fleet Manager',
                metaTags: [
                    {
                        name: 'description',
                        content: '',
                    },
                    {
                        property: 'og:description',
                        content: '',
                    },
                    {
                        property: 'og:url',
                        content: '',
                    },
                    {
                        property: 'og:image',
                        content: `${window.location.protocol}//${window.location.host}/icons/favicon-96x96.png`,
                    }
                ],
            },
        },
        {
            path: '*',
            component: Page404,
            meta: {
                titleTag: '404 - Fleet Manager',
                metaTags: [
                    {
                        name: 'description',
                        content: '',
                    },
                    {
                        property: 'og:description',
                        content: '',
                    },
                    {
                        property: 'og:url',
                        content: '',
                    },
                    {
                        property: 'og:image',
                        content: `${window.location.protocol}//${window.location.host}/icons/favicon-96x96.png`,
                    }
                ],
            },
        }
    ]
});

async function refreshSeoTags(to)
{
    if (to.meta.titleTag) {
        if (typeof to.meta.titleTag === 'function') {
            document.title = await to.meta.titleTag(to);
        } else {
            document.title = to.meta.titleTag;
        }
    }
    if (!to.meta.metaTags) {
        return;
    }
    for (let metaTag of to.meta.metaTags) {
        let content = '';
        if (typeof metaTag.content === 'function') {
            content = await metaTag.content(to);
        } else {
            content = metaTag.content;
        }
        if (metaTag.name) {
            let meta = document.head.querySelector(`meta[name="${metaTag.name}"]`);
            if (!meta) {
                meta = document.createElement('meta');
                meta.setAttribute('name', metaTag.name);
                document.head.append(meta);
            }
            meta.content = content;
        } else if (metaTag.property) {
            let meta = document.head.querySelector(`meta[property="${metaTag.property}"]`);
            if (!meta) {
                meta = document.createElement('meta');
                meta.setAttribute('property', metaTag.property);
                document.head.append(meta);
            }
            meta.content = content;
        }
    }
}

router.beforeEach((to, from, next) => {
    refreshSeoTags(to);

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
