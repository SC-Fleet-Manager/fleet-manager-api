<template>
  <div class="app flex-row align-items-center">
    <div class="container">
      <b-row class="justify-content-center">
        <b-col md="8">
          <b-card-group>
            <b-card no-body class="p-4">
              <b-card-body>
                <b-form>
                  <h1>Welcome to<br/>SC Fleet Manager</h1>
                  <p class="text-muted">This application helps you to manage and share your Star Citizen organizations' fleet.</p>
                    <b-button size="lg" block style="background-color: #7289da; color: #fff;" class="px-4" :href="discordLoginUrl">Connect with <i class="fab fa-discord"></i> Discord</b-button>
                </b-form>
              </b-card-body>
            </b-card>
            <b-card no-body class="text-white bg-gray-700 py-2 d-md-down-none" style="width:44%">
              <b-card-body class="text-center">
                <img src="../img/fleet_manager_512.png" style="max-width:100%;max-height:100%;" />
              </b-card-body>
            </b-card>
          </b-card-group>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Login',
  props: ['discordLoginUrl'],
}
</script>

<style lang="scss">
  /* Import Font Awesome Icons Set */
  $fa-font-path: '~@fortawesome/fontawesome-free/webfonts/';
  @import '~@fortawesome/fontawesome-free/scss/fontawesome';
  @import '~@fortawesome/fontawesome-free/scss/solid';
  @import '~@fortawesome/fontawesome-free/scss/brands';
  /* Import Bootstrap Vue Styles */
  @import '~bootstrap-vue/dist/bootstrap-vue.css';
  // Import Main styles for this application
  @import '../css/style';
</style>
