<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col col md="6">
                <b-card header="Preferences" class="js-preferences">
                    <b-form>
                        <b-form-group>
                            <b-input-group prepend="My fleet link">
                                <b-form-input readonly
                                              id="my_fleet_link"
                                              type="text"
                                              v-model="myFleetLink"></b-form-input>
                                <b-input-group-append>
                                    <b-btn :variant="fleetLinkCopied ? 'success' : 'outline-success'"
                                           v-clipboard:copy="myFleetLink"
                                           v-clipboard:success="onCopyFleetLink">{{ fleetLinkCopied ? 'Copied' : 'Copy' }}</b-btn>
                                </b-input-group-append>
                            </b-input-group>
                        </b-form-group>
                        <strong>Supporters preferences</strong>
                        <b-form-checkbox v-model="supporterVisible" @change="saveSupporterVisible" :disabled="savingPreferences" name="supporter-visibility" switch>
                            Display my name on Supporters page
                        </b-form-checkbox>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col col md="6" v-if="user != null">
                <Security :user="user" @accountLinked="onAccountLinked"></Security>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import Security from "./Security";
    import { mapMutations } from 'vuex';
    import VueClipboard from 'vue-clipboard2';

    import Vue from "vue";
    Vue.use(VueClipboard);

    export default {
        name: 'profile',
        components: {Security},
        data() {
            return {
                form: {
                    handle: null,
                },
                user: null,
                citizen: null,
                myFleetLink: null,
                publicChoice: null,
                orgaVisibilityChoices: {},
                manageableOrgas: [],
                savingPreferences: false,
                preferencesLoaded: false,
                userToken: null,
                copied: false,
                fleetLinkCopied: false,
                submitDisabled: false,
                showError: false,
                errorMessage: null,
                showLinkAccount: false,
                showUpdateHandle: false,
                refreshingProfile: false,
                showButtonStep1: true,
                showButtonStep2: false,
                showErrorStep1: false,
                errorStep1Message: '',
                searchedCitizen: null,
                searchingHandle: false,
                lastShortBio: null,
                showCollapseStep1: false,
                showCollapseStep2: false,
                supporterVisible: null,
            }
        },
        created() {
            this.refreshProfile();
        },
        methods: {
            ...mapMutations(['updateProfile']),
            saveSupporterVisible(value) {
                this.supporterVisible = value;
                this.savePreferences();
            },
            savePreferences() {
                this.savingPreferences = true;
                axios.post('/api/profile/save-preferences', {
                    publicChoice: this.publicChoice,
                    orgaVisibilityChoices: this.orgaVisibilityChoices,
                    supporterVisible: this.supporterVisible,
                }).then(response => {
                    this.$toastr.s('Changes saved');
                }).catch(err => {
                    this.checkAuth(err.response);
                    this.$toastr.e('An error has occurred. Please try again later.');
                }).then(_ => {
                    this.savingPreferences = false;
                });
            },
            onCopyToken() {
                this.copied = true;
            },
            onCopyFleetLink() {
                this.fleetLinkCopied = true;
            },
            onAccountLinked() {
                this.refreshProfile();
            },
            refreshProfile() {
                axios.get('/api/profile').then(response => {
                    this.user = response.data;
                    this.userToken = this.user.token;
                    this.myFleetLink = this.getMyFleetLink();
                }).catch(err => {
                    this.checkAuth(err.response);
                    this.showError = true;
                    if (err.response.data.errorMessage) {
                        this.errorMessage = err.response.data.errorMessage;
                    }
                });
            },
            getMyFleetLink() {
                return '';

                return `${window.location.protocol}//${window.location.host}/citizen/${this.user.username}`;
            },
            checkAuth(response) {
                const status = response.status;
                const data = response.data;
                if ((status === 401 && data.error === 'no_auth')
                    || (status === 403 && data.error === 'forbidden')) {
                    window.location = data.loginUrl;
                }
            }
        }
    }
</script>
