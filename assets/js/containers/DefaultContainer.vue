<template>
    <div class="app">
        <AppHeader fixed>
            <SidebarToggler class="d-lg-none" display="md" mobile/>
            <b-link class="navbar-brand" href="/">
                <img class="navbar-brand-full" src="../../img/fleet_manager_155x55.png" alt="SC Fleet Manager" height="45">
                <img class="navbar-brand-minimized" src="../../img/fleet_manager_128.png" alt="FM" height="40">
            </b-link>
            <SidebarToggler class="d-md-down-none" display="lg" :defaultOpen="true" ref="sidebarDesktop"/>
            <b-navbar-nav class="ml-auto">
                <b-nav-text v-if="user != null" class="px-3 d-none d-sm-inline-block">Welcome, {{ citizen !== null ? citizen.actualHandle.handle : (user.nickname !== null ? user.nickname : user.email) }}</b-nav-text>
                <b-nav-item v-if="user != null" class="px-3" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</b-nav-item>
                <b-nav-item v-else class="px-3" v-b-modal.modal-login><i class="fas fa-sign-in-alt"></i> Login</b-nav-item>
            </b-navbar-nav>
        </AppHeader>
        <div class="app-body">
            <AppSidebar fixed>
                <SidebarNav :navItems="nav"></SidebarNav>
                <SidebarMinimizer/>
            </AppSidebar>
            <main class="main">
                <div class="container-fluid mt-4">
                    <router-view></router-view>
                </div>
            </main>
        </div>
        <TheFooter class="font-lg">
            <div>
                <a href="/">Fleet Manager</a>
                <a href="https://blog.fleet-manager.space/tag/change-logs/" target="_blank">{{ lastVersion }}</a>
                <span class="ml-1">&copy; 2018 - {{ actualYear }}</span>
                - <a href="/privacy-policy">Privacy policy</a>
            </div>
            <b-nav class="ml-auto">
                <b-nav-item link-classes="p-2" href="https://ext.fleet-manager.space/fleet_manager_extension-latest.xpi"><i style="font-size: 1.4rem;" class="fab fa-firefox"></i></b-nav-item>
                <b-nav-item link-classes="p-2" target="_blank" href="https://chrome.google.com/webstore/detail/fleet-manager-extension/hbbadomkekhkhemjjmhkhgiokjhpobhk"><i style="font-size: 1.4rem;" class="fab fa-chrome"></i></b-nav-item>
                <b-nav-text class="p-2">–</b-nav-text>
                <b-nav-item href="https://discord.gg/f6mrA3Y" target="_blank" link-classes="p-2"><i class="fab fa-discord" style="font-size: 1.4rem;"></i></b-nav-item>
                <b-nav-item href="https://github.com/Ioni14/starcitizen-fleet-manager" target="_blank" link-classes="p-2"><i class="fab fa-github" style="font-size: 1.4rem;"></i></b-nav-item>
                <b-nav-text><span class="mr-1">Created by </span><a target="_blank" href="https://github.com/ioni14">Ioni</a></b-nav-text>
            </b-nav>
        </TheFooter>

        <RegistrationAndLoginModal discord-login-url="/connect/discord"></RegistrationAndLoginModal>
    </div>
</template>

<script>
    import axios from 'axios';
    import {
        Header as AppHeader,
        SidebarToggler,
        Sidebar as AppSidebar,
        SidebarMinimizer,
        SidebarNav,
        Footer as TheFooter
    } from '@coreui/vue';
    import { mapMutations } from 'vuex';
    import RegistrationAndLoginModal from "../views/RegistrationAndLoginModal";

    export default {
        name: 'DefaultContainer',
        components: {
            RegistrationAndLoginModal,
            AppHeader,
            AppSidebar,
            TheFooter,
            SidebarNav,
            SidebarToggler,
            SidebarMinimizer
        },
        data() {
            return {
                actualYear: (new Date()).getFullYear(),
                user: null,
                citizen: null,
                lastVersion: null,
            }
        },
        created() {
            axios.get('/api/profile').then(response => {
                this.user = response.data;
                this.updateUser(this.user);
                this.citizen = this.user.citizen;
                this.updateProfile(this.citizen);
                // this.$refs.sidebarDesktop.toggle(true);
            });

            this.$store.watch(
                (state, getters) => getters.citizen,
                (newValue, oldValue) => {
                    this.citizen = newValue;
                }
            );

            this.findLastVersion();
        },
        computed: {
            name() {
                return this.$route.name
            },
            nav() {
                const nav = [];
                if (this.citizen !== null) {
                    nav.push({
                        name: 'My Fleet',
                        url: `/citizen/${this.citizen ? this.citizen.actualHandle.handle : ''}`,
                        icon: 'fas fa-fighter-jet',
                        attributes: {
                            disabled: this.citizen === null,
                        },
                    });
                    if (this.citizen.organizations.length > 0) {
                        nav.push({
                            name: 'My Orgas',
                            url: '/organizations-fleets',
                            icon: 'fas fa-space-shuttle',
                            attributes: {
                                disabled: this.citizen === null || this.citizen.organizations.length === 0,
                            },
                        });
                    }
                }

                nav.push(
                    {
                        name: "Profile",
                        url: '/profile',
                        icon: 'fas fa-user',
                        attributes: {
                            disabled: this.user === null,
                        },
                    },
                    {
                        name: 'Supporters',
                        url: '/supporters',
                        icon: 'fas fa-star',
                        badge: {
                            text: 'NEW',
                            variant: 'danger',
                        },
                    }
                );

                if (this.citizen !== null) {
                    nav.push({
                        name: 'My backings',
                        url: '/my-backings',
                        icon: 'fas fa-hands-helping',
                    });
                }

                return nav;
            }
        },
        methods: {
            ...mapMutations(['updateProfile', 'updateUser']),
            findLastVersion() {
                axios.get('https://api.github.com/repos/Ioni14/starcitizen-fleet-manager/tags').then(response => {
                    this.lastVersion = response.data[0].name;
                });
            }
        }
    };
</script>
