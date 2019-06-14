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
                <!--<b-nav-text v-if="citizen != null" class="px-3 d-none d-sm-inline-block">Welcome, {{ citizen.actualHandle.handle }}</b-nav-text>
                <b-nav-text v-if="citizen == null && user != null" class="px-3 d-none d-sm-inline-block">Welcome, {{ user.nickname }}</b-nav-text>-->
                <b-nav-text v-if="user != null" class="px-3 d-none d-sm-inline-block">Welcome, {{ user.nickname }}</b-nav-text>
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
                <span class="ml-1">&copy; 2018 - {{ actualYear }}</span>
                - <a href="/privacy-policy">Privacy policy</a>
            </div>
            <b-nav class="ml-auto">
                <b-nav-item href="https://discord.gg/f6mrA3Y" target="_blank" link-classes="p-2"><i class="fab fa-discord" style="font-size: 1.4rem;"></i></b-nav-item>
                <b-nav-item href="https://github.com/Ioni14/starcitizen-fleet-manager" target="_blank" link-classes="p-2"><i class="fab fa-github" style="font-size: 1.4rem;"></i></b-nav-item>
                <b-nav-item href="https://www.patreon.com/ioni" target="_blank" link-classes="p-2"><i class="fab fa-patreon" style="font-size: 1.4rem;"></i></b-nav-item>
                <b-nav-text><span class="mr-1">Created by </span><a target="_blank" href="https://github.com/ioni14">Ioni</a></b-nav-text>
            </b-nav>
        </TheFooter>

        <b-modal
            id="modal-login"
            ref="modalLogin"
            title="Connect to Fleet Manager"
            size="md"
            centered hide-footer
            header-bg-variant="dark"
            header-text-variant="light"
            body-bg-variant="dark"
            body-text-variant="light"
            footer-bg-variant="dark"
            footer-text-variant="light"
        >
            <b-row class="justify-content-center">
                <b-col>
                    <b-form class="text-center mt-3 mb-3">
                        <b-button size="lg" style="background-color: #7289da; color: #fff;" class="px-5" href="/connect/discord"><i class="fab fa-discord"></i> Login with Discord</b-button>
                    </b-form>
                </b-col>
            </b-row>
        </b-modal>
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

    export default {
        name: 'DefaultContainer',
        components: {
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
            }
        },
        created() {
            axios.get('/api/profile/').then(response => {
                this.user = response.data;
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

                return [
                    ...nav,
                    {
                        name: "Profile",
                        url: '/profile',
                        icon: 'fas fa-user',
                        attributes: {
                            disabled: this.user === null,
                        },
                    },
                ];
            }
        },
        methods: {
            ...mapMutations(['updateProfile']),
        }
    };
</script>
