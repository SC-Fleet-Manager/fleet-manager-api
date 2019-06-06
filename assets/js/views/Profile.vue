<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col col md="6" v-if="showLinkAccount">
                <b-card header="Link your RSI Account">
                    <b-form>
                        <b-alert variant="success" show>
                            In order to protect your fleet, we need that you <b>link your RSI account</b> to Fleet Manager.<br/>
                            This allows us to prevent impersonation of RSI accounts.<br/>
                            For now, the only best and simplest way is to put a <b>special marker</b> on your <b>RSI biography</b>.
                        </b-alert>
                        <b-button v-b-toggle.collapse-step-1 variant="primary" size="lg" v-if="showButtonStep1">Okay, I'm ready to link my account!</b-button>
                        <b-collapse id="collapse-step-1" class="mt-3" @show="showButtonStep1 = false">
                            <h4>1. Who are you?</h4>
                            <b-form-group>
                                <p class="">Firstly, let us know your Star Citizen Handle.</p>
                                <b-alert variant="danger" :show="showErrorStep1" v-html="errorStep1Message"></b-alert>
                                <b-input-group>
                                    <b-form-input id="form_handle"
                                                  type="text"
                                                  v-model="form.handle"
                                                  placeholder="Type your SC handle then click on search"></b-form-input>
                                    <b-input-group-append>
                                        <b-btn variant="success" @click="searchHandle" :disabled="searchingHandle">
                                            <template v-if="!searchingHandle"><i class="fas fa-search"></i> Search</template>
                                            <template v-else><i class="fas fa-spinner fa-pulse"></i> Search</template>
                                        </b-btn>
                                    </b-input-group-append>
                                </b-input-group>
                                <div v-if="searchedCitizen != null" class="row mt-3">
                                    <div v-if="searchedCitizen.avatarUrl" class="col col-xs-12 col-md-3 col-lg-2">
                                        <img :src="searchedCitizen.avatarUrl" alt="avatar" class="img-fluid" />
                                    </div>
                                    <div class="col">
                                        <strong>Nickname</strong>: {{ searchedCitizen.nickname }}<br/>
                                        <strong>Handle</strong>: <a :href="'https://robertsspaceindustries.com/citizens/'+searchedCitizen.handle.handle" target="_blank">{{ searchedCitizen.handle.handle }}</a><br/>
                                        <strong>Number</strong>: {{ searchedCitizen.numberSC.number }}<br/>
                                        <strong>Main orga</strong>: <span v-html="searchedCitizen.mainOrga ? formatOrganizationList([searchedCitizen.mainOrga]) : ''"></span><br/>
                                        <strong>All orgas</strong>: <span v-html="formatOrganizationList(searchedCitizen.organisations)"></span><br/>
                                    </div>
                                </div>
                            </b-form-group>
                            <b-row v-if="searchedCitizen == null">
                                <b-col>
                                    <b-alert variant="info" show>
                                        <i class="fas fa-info-circle"></i>
                                        Your <b>SC Handle</b> is your <b>RSI username</b> (not your nickname) and can be visible on your <b>RSI Profile panel</b> or <a href="https://robertsspaceindustries.com/account/settings" target="_blank" style="text-decoration: underline"><b>RSI Settings</b></a>.
                                    </b-alert>
                                </b-col>
                                <b-col lg="5" class="text-right">
                                    <img class="img-fluid" src="../../img/sc-handle.png" alt="How to retrieve your Handle" />
                                </b-col>
                            </b-row>
                        </b-collapse>

                        <b-button v-b-toggle.collapse-step-2 variant="primary" size="lg" v-if="showButtonStep2">Great, this is my account, let's continue!</b-button>
                        <b-collapse id="collapse-step-2" class="mt-3" @show="showButtonStep2 = false">
                            <h4>2. Special marker</h4>
                            <p>
                                Finally, you have to put this following token into your <a href="https://robertsspaceindustries.com/account/profile" target="_blank" style="text-decoration: underline"><b>RSI short bio</b></a>.<br/>
                                Don't worry, you can remove it just after your successful link. ;)
                            </p>
                            <b-alert variant="danger" :show="showError" v-html="errorMessage"></b-alert>
                            <div v-if="lastShortBio != null">
                                <strong>Your actual bio:</strong>
                                <p style="max-height: 150px; overflow-y: auto;">{{ lastShortBio }}</p>
                            </div>
                            <b-form-group>
                                <b-input-group prepend="Token">
                                    <b-form-input readonly
                                                  id="form_user_token"
                                                  type="text"
                                                  v-model="userToken"></b-form-input>
                                    <b-input-group-append>
                                        <b-btn :variant="copied ? 'success' : 'outline-success'"
                                               v-clipboard:copy="userToken"
                                               v-clipboard:success="onCopyToken">{{ copied ? 'Copied' : 'Copy' }}</b-btn>
                                    </b-input-group-append>
                                </b-input-group>
                            </b-form-group>
                            <b-button type="button" :disabled="submitDisabled" size="lg" variant="success" @click="linkAccount">Done! I've pasted the token in my bio.</b-button>
                        </b-collapse>
                    </b-form>
                </b-card>
            </b-col>
            <b-col col md="6" v-if="showUpdateHandle">
                <UpdateScHandle :citizen="citizen"></UpdateScHandle>
            </b-col>
        </b-row>
        <b-row>
            <b-col col md="6" v-if="showUpdateHandle">
                <b-card header="Preferences" class="js-preferences">
                    <b-form>
                        <b-button type="button" variant="secondary" :disabled="refreshingProfile" @click="refreshMyRsiProfile" class="mb-3" title="Force to retrieve your public profile from RSI"><i class="fas fa-sync-alt" :class="{'fa-spin': refreshingProfile}"></i>
                            Refresh my RSI Profile</b-button>
                        <b-form-group label="Personal fleet policy">
                            <b-form-radio v-model="publicChoice" @change="savePublicChoice" :disabled="savingPreferences" name="public-choice" value="private">Private</b-form-radio>
                            <b-form-radio v-model="publicChoice" @change="savePublicChoice" :disabled="savingPreferences" name="public-choice" value="orga">Organizations only</b-form-radio>
                            <b-form-radio v-model="publicChoice" @change="savePublicChoice" :disabled="savingPreferences" name="public-choice" value="public">Public</b-form-radio>
                        </b-form-group>
                        <!-- TODO uncomment this shit -->
                        <!--<b-form-group :label="'Organization ' + orga.organizationSid + ' fleet policy'" v-for="orga in manageableOrgas" :key="orga.organizationSid">
                            <b-form-radio v-model="orgaPublicChoices[orga.organizationSid]" @change="saveOrgaPublicChoice($event, orga)" :disabled="savingPreferences" :name="'orga-public-choice-' + orga.organizationSid" value="private">Private</b-form-radio>
                            <b-form-radio v-model="orgaPublicChoices[orga.organizationSid]" @change="saveOrgaPublicChoice($event, orga)" :disabled="savingPreferences" :name="'orga-public-choice-' + orga.organizationSid" value="public">Public</b-form-radio>
                        </b-form-group>-->
                        <b-form-group label="My fleet link" label-for="my_fleet_link">
                            <b-input-group>
                                <b-input-group-prepend>
                                    <b-btn :variant="fleetLinkCopied ? 'success' : 'outline-success'"
                                           v-clipboard:copy="myFleetLink"
                                           v-clipboard:success="onCopyFleetLink">{{ fleetLinkCopied ? 'Copied' : 'Copy' }}</b-btn>
                                </b-input-group-prepend>
                                <b-form-input readonly
                                              id="my_fleet_link"
                                              type="text"
                                              v-model="myFleetLink"></b-form-input>
                            </b-input-group>
                        </b-form-group>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import UpdateScHandle from "./UpdateSCHandle";
    import { mapMutations } from 'vuex';

    export default {
        name: 'profile',
        components: {UpdateScHandle},
        data() {
            return {
                form: {
                    handle: null,
                },
                myFleetLink: null,
                publicChoice: null,
                orgaPublicChoices: {},
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
            }
        },
        created() {
            this.refreshProfile();
        },
        methods: {
            ...mapMutations(['updateProfile']),
            savePublicChoice(value) {
                this.publicChoice = value;
                this.savePreferences();
            },
            saveOrgaPublicChoice(value, orga) {
                this.$set(this.orgaPublicChoices, orga.organizationSid, value);
                this.savePreferences();
            },
            savePreferences() {
                this.savingPreferences = true;
                axios.post('/api/profile/save-preferences', {
                    publicChoice: this.publicChoice,
                    orgaPublicChoices: this.orgaPublicChoices,
                }).then(response => {
                    toastr.success('Changes saved');
                }).catch(err => {
                    this.checkAuth(err.response);
                    console.error(err);
                    toastr.error('An error has occurred. Please retry more later.');
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
            refreshProfile() {
                axios.get('/api/profile/').then(response => {
                    this.citizen = response.data.citizen;
                    this.showLinkAccount = !this.citizen;
                    this.showUpdateHandle = !!this.citizen;
                    this.userToken = response.data.token;
                    this.myFleetLink = this.getMyFleetLink();
                    this.publicChoice = response.data.publicChoice;
                    this.updateProfile(this.citizen);
                    this.refreshManageableOrgas();
                }).catch(err => {
                    this.checkAuth(err.response);
                    this.showError = true;
                    if (err.response.data.errorMessage) {
                        this.errorMessage = err.response.data.errorMessage;
                    }
                    console.error(err);
                });
            },
            refreshManageableOrgas() {
                if (this.citizen === null) {
                    return;
                }
                axios.get('/api/manageable-organizations').then(response => {
                    this.manageableOrgas = response.data;
                    for (let orga of this.manageableOrgas) {
                        this.$set(this.orgaPublicChoices, orga.organizationSid, orga.publicChoice);
                    }
                }).catch(err => {
                    this.checkAuth(err.response);
                    toastr.error('Sorry, can\'t retrieve manageable organizations.');
                    console.error(err);
                });
            },
            getMyFleetLink() {
                if (this.citizen === null) {
                    return '';
                }

                return `${window.location.protocol}//${window.location.host}/citizen/${this.citizen.actualHandle.handle}`;
            },
            refreshMyRsiProfile(ev) {
                this.refreshingProfile = true;
                axios.post('/api/profile/refresh-rsi-profile').then(response => {
                    toastr.success('Your RSI public profile has been successfully refreshed.');
                }).catch(err => {
                    this.checkAuth(err.response);
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    }
                    console.error(err);
                }).then(_ => {
                    this.refreshingProfile = false;
                });
            },
            searchHandle() {
                if (!this.form.handle) {
                    return;
                }

                this.searchedCitizen = null;
                this.showErrorStep1 = false;
                this.searchingHandle = true;
                axios.get('/api/search-handle', {
                    params: {handle: this.form.handle}
                }).then(response => {
                    this.searchedCitizen = response.data;
                    this.showButtonStep2 = true;
                }).catch(err => {
                    if (err.response.data.error === 'not_found_handle') {
                        this.errorStep1Message = `Sorry, it seems that <a href="https://robertsspaceindustries.com/citizens/${this.form.handle}" target="_blank">SC Handle ${this.form.handle}</a> does not exist. Try to check the typo and search again.`;
                    } else {
                        this.errorStep1Message = `Sorry, an unexpected error has occurred. Please retry.`;
                    }
                    this.showErrorStep1 = true;
                    console.error(err);
                }).then(_ => {
                    this.searchingHandle = false;
                });
            },
            formatOrganizationList(orgas) {
                let res = [];
                for (let orga of orgas) {
                    res.push(`<a href="https://robertsspaceindustries.com/orgs/${orga.sid.sid}" target="_blank">${orga.sid.sid}</a>`);
                }
                return res.join(', ');
            },
            linkAccount() {
                const form = new FormData();
                form.append('handleSC', this.searchedCitizen.handle.handle);

                this.lastShortBio = null;
                this.showError = false;
                this.errorMessage = 'An error has been occurred. Please try again in a moment.';
                this.submitDisabled = true;
                axios.post('/api/profile/link-account', form).then(response => {
                    this.refreshProfile();
                    toastr.success('Your RSI account has been successfully linked! You can remove the token from your bio.');
                    this.submitDisabled = false;
                    this.showLinkAccount = false;
                    this.showUpdateHandle = true;
                }).catch(async err => {
                    this.submitDisabled = false;
                    if (err.response.data.error === 'invalid_form') {
                        const response = await axios.get('/api/search-handle', {
                            params: {handle: this.form.handle}
                        });
                        if (response.data) {
                            this.searchedCitizen = response.data;
                            this.lastShortBio = this.searchedCitizen.bio;
                        }
                    }
                    if (err.response.data.errorMessage) {
                        this.errorMessage = err.response.data.errorMessage;
                    } else if (err.response.data.error === 'invalid_form') {
                        this.errorMessage = err.response.data.formErrors.join("\n");
                    }
                    this.showError = true;
                    console.error(err);
                });
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
