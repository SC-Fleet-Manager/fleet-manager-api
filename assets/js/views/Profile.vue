<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col col md="6">
                <b-card header="Link your RSI Account">
                    <b-alert variant="info" show>
                        To link your account you need to copy-paste the following token to your profile BIO then validate.
                    </b-alert>

                    <b-form @submit="onSubmit">
                        <b-alert variant="success" :show="showSuccess">Your RSI account has been successfully linked! You can remove the token from your bio.</b-alert>
                        <b-alert variant="danger" :show="showError">{{ errorMessage }}</b-alert>
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
                        <p><a target="_blank" href="https://robertsspaceindustries.com/account/profile">Go to your RSI Profile</a></p>
                        <b-form-group label="Handle Star Citizen" label-for="form_handle">
                            <b-form-input id="form_handle"
                                          type="text"
                                          v-model="form.handle"
                                          required
                                          placeholder="Your Handle Star Citizen"></b-form-input>
                        </b-form-group>
                        <b-button type="submit" :disabled="submitDisabled" variant="success">Link !</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: 'profile',
        components: {},
        data: function () {
            return {
                form: {
                    handle: null,
                },
                userToken: null,
                copied: false,
                submitDisabled: false,
                showError: false,
                showSuccess: false,
                errorMessage: null,
            }
        },
        created() {
            axios.get('/profile', {
                params: {}
            }).then(response => {
                this.userToken = response.data.token;
            }).catch(err => {
                this.showError = true;
                if (err.response.data.errorMessage) {
                    this.errorMessage = err.response.data.errorMessage;
                }
                console.error(err);
            });
        },
        methods: {
            onCopyToken() {
                this.copied = true;
            },
            onSubmit(ev) {
                ev.preventDefault();

                const form = new FormData();
                form.append('handleSC', this.form.handle);

                this.showError = false;
                this.showSuccess = false;
                this.errorMessage = 'Une erreur est survenue. Veuillez réessayer dans quelques instants.';
                this.submitDisabled = true;
                axios({
                    method: 'post',
                    url: '/link-account',
                    data: form,
                }).then(response => {
                    this.submitDisabled = false;
                    this.showSuccess = true;
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
            }
        }
    }
</script>
