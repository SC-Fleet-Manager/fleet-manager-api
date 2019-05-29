<template>
    <div class="app">
        <AppHeader fixed>
            <SidebarToggler class="d-lg-none" display="md" mobile/>
            <b-link class="navbar-brand" to="/">
                <img class="navbar-brand-full" src="../../img/fleet_manager_155x55.png" alt="SC Fleet Manager" height="45">
                <img class="navbar-brand-minimized" src="../../img/fleet_manager_128.png" alt="FM" height="40">
            </b-link>
            <SidebarToggler class="d-md-down-none" display="lg"/>
            <b-navbar-nav class="ml-auto">
                <b-nav-text v-if="citizen != null" class="px-3">Welcome, {{ this.citizen.actualHandle.handle }}</b-nav-text>
                <b-nav-item v-if="user != null" class="px-3" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout
                </b-nav-item>
                <b-nav-item v-else class="px-3" href="/login"><i class="fas fa-sign-in-alt"></i> Login</b-nav-item>
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
                <a href="/">Star Citizen Fleet Manager</a>
                <span class="ml-1">&copy; 2018 - {{ actualYear }}</span>
            </div>
            <div class="ml-auto">
                <a href="https://discord.gg/f6mrA3Y" target="_blank"><i class="fab fa-discord"></i> Discord</a> -
                <a target="_blank" href="https://github.com/Ioni14/starcitizen-fleet-manager/issues"> <i
                        class="fab fa-github"></i> Bugs, feedbacks,
                    ideas</a>
                -
                <a target="_blank" href="https://www.patreon.com/ioni"><i class="fab fa-patreon"></i> Patreon</a>
                -
                <span class="mr-1">Created by</span> <a target="_blank" href="https://github.com/ioni14">Thomas "Ioni"
                Talbot</a>
            </div>
        </TheFooter>
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
                    nav.push({
                        name: 'Organizations\' fleets',
                        url: '/organizations-fleets',
                        icon: 'fas fa-fighter-jet',
                        attributes: {
                            disabled: this.citizen === null,
                        },
                    });
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
