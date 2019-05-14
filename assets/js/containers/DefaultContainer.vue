<template>
    <div class="app">
        <AppHeader fixed>
            <SidebarToggler class="d-lg-none" display="md" mobile/>
            <b-link class="navbar-brand" to="/">
                <img class="navbar-brand-full" src="../../img/fleet_manager_155x55.png" alt="SC Fleet Manager" style="padding: 10px;">
                <img class="navbar-brand-minimized" src="../../img/fleet_manager_128.png" alt="FM" style="padding: 5px;">
            </b-link>
            <SidebarToggler class="d-md-down-none" display="lg"/>
            <b-navbar-nav class="d-md-down-none mr-auto">
            </b-navbar-nav>
            <b-navbar-nav class="ml-auto">
                <b-nav-item v-if="user != null" class="px-3" href="/logout"><i class="icon-logout"></i> Logout</b-nav-item>
                <b-nav-item v-else class="px-3" href="/login"><i class="icon-login"></i> Login</b-nav-item>
            </b-navbar-nav>
        </AppHeader>
        <div class="app-body">
            <AppSidebar fixed>
                <SidebarHeader/>
                <SidebarForm/>
                <SidebarNav :navItems="nav"></SidebarNav>
                <SidebarFooter/>
                <SidebarMinimizer/>
            </AppSidebar>
            <main class="main">
                <div class="container-fluid mt-4">
                    <router-view></router-view>
                </div>
            </main>
        </div>
        <TheFooter>
            <div>
                <a href="/">Star Citizen Fleet Manager</a>
                <span class="ml-1">&copy; 2018 - {{ actualYear }}</span>
            </div>
            <div class="ml-auto">
                <a href="https://discord.gg/yCWj2b" target="_blank">Fleet Manager Discord</a> -
                <a target="_blank" href="https://github.com/Ioni14/starcitizen-fleet-manager/issues">Bugs, feedbacks,
                    ideas</a>
                -
                <span class="mr-1">Created by</span>
                <a target="_blank" href="https://github.com/ioni14">Thomas "Ioni" Talbot</a>. Referral Code <strong>STAR-GQYR-SN3X</strong>
            </div>
        </TheFooter>
    </div>
</template>

<script>
    import axios from 'axios';
    import nav from '../_nav';
    import {
        Header as AppHeader,
        SidebarToggler,
        Sidebar as AppSidebar,
        SidebarFooter,
        SidebarForm,
        SidebarHeader,
        SidebarMinimizer,
        SidebarNav,
        AsideToggler,
        Footer as TheFooter,
        Breadcrumb
    } from '@coreui/vue';

    export default {
        name: 'DefaultContainer',
        components: {
            AsideToggler,
            AppHeader,
            AppSidebar,
            TheFooter,
            Breadcrumb,
            SidebarForm,
            SidebarFooter,
            SidebarToggler,
            SidebarHeader,
            SidebarNav,
            SidebarMinimizer
        },
        data() {
            return {
                nav: nav.items,
                actualYear: (new Date()).getFullYear(),
                user: null
            }
        },
        created() {
            axios.get('/api/me').then(response => {
                this.user = response.data;
            });
        },
        computed: {
            name() {
                return this.$route.name
            },
            list() {
                return this.$route.matched.filter((route) => route.name || route.meta.label)
            }
        }
    };
</script>
