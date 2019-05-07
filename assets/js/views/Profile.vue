<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col col md="6" v-if="showLinkAccount">
                <b-card header="Link your RSI Account">
                    <b-alert variant="info" show>
                        To link your Star Citizen account, copy-paste the following token into your "Brief Bio" from your <a target="_blank" href="https://robertsspaceindustries.com/account/profile">RSI profile</a>. Then enter your Handle and validate.
                    </b-alert>

                    <b-form @submit="onSubmit">
                        <b-alert variant="danger" :show="showError" v-html="errorMessage"></b-alert>
                        <b-form-group label="Account Token" label-for="form_user_token">
                            <b-input-group>
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
                        <b-form-group label="Handle Star Citizen" label-for="form_handle">
                            <b-form-input id="form_handle"
                                          type="text"
                                          v-model="form.handle"
                                          required
                                          placeholder="Your Handle Star Citizen"></b-form-input>
                        </b-form-group>
                        <b-button type="submit" :disabled="submitDisabled" variant="success">Link my RSI account</b-button>
                    </b-form>
                </b-card>
            </b-col>
            <b-col col md="6" v-if="showUpdateHandle">
                <UpdateScHandle :citizen="citizen"></UpdateScHandle>
            </b-col>
        </b-row>
        <b-row>
            <b-col col md="6" v-if="showUpdateHandle">
                <b-card header="Preferences">
                    <b-form>
                        <b-form-group label="Personal fleet policy">
                            <b-form-radio v-model="publicChoice" @change="savePreferences" :disabled="savingPreferences" name="public-choice" value="private">Private</b-form-radio>
                            <b-form-radio v-model="publicChoice" @change="savePreferences" :disabled="savingPreferences" name="public-choice" value="orga">Organizations only</b-form-radio>
                            <b-form-radio v-model="publicChoice" @change="savePreferences" :disabled="savingPreferences" name="public-choice" value="public">Public</b-form-radio>
                        </b-form-group>
                        <!--<b-form-group label="Orga fleet policy">
                            <b-form-radio v-model="publicChoice" name="some-radios" value="A">Option A</b-form-radio>
                            <b-form-radio v-model="publicChoice" name="some-radios" value="B">Option B</b-form-radio>
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
            }
        },
        created() {
            this.refreshProfile();
        },
        methods: {
            savePreferences() {
                this.savingPreferences = true;
                axios.post('/save-preferences', {
                    publicChoice: this.publicChoice
                }).then(response => {
                    this.savingPreferences = false;
                    toastr.success('Changes saved');
                }).catch(err => {
                    this.savingPreferences = false;
                    // TODO : show errors
                    console.error(err);
                    toastr.error('An error has occurred. Please retry more later.');
                });
            },
            onCopyToken() {
                this.copied = true;
            },
            onCopyFleetLink() {
                this.fleetLinkCopied = true;
            },
            refreshProfile() {
                axios.get('/profile', {
                    params: {}
                }).then(response => {
                    this.citizen = response.data.citizen;
                    this.showLinkAccount = !this.citizen;
                    this.showUpdateHandle = !!this.citizen;
                    this.userToken = response.data.token;
                    this.myFleetLink = this.getMyFleetLink();
                    this.publicChoice = response.data.publicChoice;
                }).catch(err => {
                    this.showError = true;
                    if (err.response.data.errorMessage) {
                        this.errorMessage = err.response.data.errorMessage;
                    }
                    console.error(err);
                });
            },
            onSubmit(ev) {
                ev.preventDefault();

                const form = new FormData();
                form.append('handleSC', this.form.handle);

                this.showError = false;
                this.errorMessage = 'An error has been occurred. Please try again in a moment.';
                this.submitDisabled = true;
                axios({
                    method: 'post',
                    url: '/link-account',
                    data: form,
                }).then(response => {
                    this.refreshProfile();
                    toastr.success('Your RSI account has been successfully linked! You can remove the token from your bio.');
                    this.submitDisabled = false;
                    this.showLinkAccount = false;
                    this.showUpdateHandle = true;
                }).catch(err => {
                    this.submitDisabled = false;
                    this.showError = true;
                    if (err.response.data.errorMessage) {
                        this.errorMessage = err.response.data.errorMessage;
                    } else if (err.response.data.error === 'invalid_form') {
                        this.errorMessage = err.response.data.formErrors.join("\n");
                    }
                    console.error(err);
                });
            },
            getMyFleetLink() {
                return `${window.location.protocol}//${window.location.host}/#/user/${this.citizen.actualHandle.handle}`;
            }
        }
    }
</script>
