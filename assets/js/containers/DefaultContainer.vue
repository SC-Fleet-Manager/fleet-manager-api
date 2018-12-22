<template>
  <div class="app">
    <AppHeader fixed>
      <SidebarToggler class="d-lg-none" display="md" mobile />
      <b-link class="navbar-brand" to="#">
        <img class="navbar-brand-full" src="img/logo.png" alt="FLK Logo">
        <img class="navbar-brand-minimized" src="img/logo.png" width="30" height="30" alt="CoreUI Logo">
      </b-link>
      <SidebarToggler class="d-md-down-none" display="lg" />
      <b-navbar-nav class="d-md-down-none mr-auto">
        <b-nav-item class="px-3" to="/dashboard">Dashboard</b-nav-item>
        <!--<b-nav-item class="px-3" to="/users" exact>Users</b-nav-item>-->
        <!--<b-nav-item class="px-3">Settings</b-nav-item>-->
      </b-navbar-nav>
      <b-navbar-nav class="ml-auto">
        <b-nav-item class="px-3">
          <b-button to="/upload-fleet-file" variant="primary">Mettre à jour ma flotte</b-button>
        </b-nav-item>
        <!--<b-nav-item class="d-md-down-none">
          <i class="icon-bell"></i>
          <b-badge pill variant="danger">5</b-badge>
        </b-nav-item>
        <b-nav-item class="d-md-down-none">
          <i class="icon-list"></i>
        </b-nav-item>
        <b-nav-item class="d-md-down-none">
          <i class="icon-location-pin"></i>
        </b-nav-item>-->
        <!--<DefaultHeaderDropdownAccnt/>-->
      </b-navbar-nav>
      <!--<AsideToggler class="d-none d-lg-block" />-->
      <!--<AsideToggler class="d-lg-none" mobile />-->
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
        <Breadcrumb :list="list"/>
        <div class="container-fluid">
          <router-view></router-view>
        </div>
      </main>
      <!--<AppAside fixed>-->
        <!--aside-->
        <!--<DefaultAside/>-->
      <!--</AppAside>-->
    </div>
    <TheFooter>
      <!--footer-->
      <div>
        <a href="/">Star Citizen Fleet Manager</a>
        <span class="ml-1">&copy; 2018.</span>
      </div>
      <div class="ml-auto">
        <span class="mr-1">Réalisé par</span>
        <a href="https://coreui.io">Thomas "Ioni" Talbot</a>
      </div>
    </TheFooter>
  </div>
</template>

<script>
import nav from '../_nav'
import { Header as AppHeader, SidebarToggler, Sidebar as AppSidebar, SidebarFooter, SidebarForm, SidebarHeader, SidebarMinimizer, SidebarNav, AsideToggler, Footer as TheFooter, Breadcrumb } from '@coreui/vue'

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
  data () {
    return {
      nav: nav.items
    }
  },
  computed: {
    name () {
      return this.$route.name
    },
    list () {
      return this.$route.matched.filter((route) => route.name || route.meta.label )
    }
  }
}
</script>
