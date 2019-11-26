<template>
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
            <b-col style="padding-left:5rem;padding-right:5rem;">
                <b-form class="text-center mt-3 mb-3">
                    <b-button size="lg" block style="background-color: #7289da; color: #fff;" class="px-5" :href="discordLoginUrl"><i class="fab fa-discord"></i> Login with Discord</b-button>
                </b-form>
                <div class="text-center mt-5 mb-3">
                    <b-alert :show="registrationFormSuccessMessage" variant="success">{{ registrationFormSuccessMessage }}</b-alert>
                    <b-alert :show="lostPasswordFormSuccessMessage" variant="success">{{ lostPasswordFormSuccessMessage }}</b-alert>
                    <b-collapse id="collapse-login-form" accordion="login-form" visible>
                        <b-form @submit="onSubmitLoginForm">
                            <b-alert :show="loginFormErrorsGlobal" variant="danger">
                                {{ loginFormErrorsGlobal }}
                                <b-button v-if="loginNotConfirmed != null" :disabled="resendConfirmationEmailDisabled" type="button" variant="primary" class="mt-2" @click="resendConfirmationEmail"><i class="fas fa-redo"></i> Resend a confirmation email</b-button>
                            </b-alert>
                            <b-form-group label-for="input-email">
                                <b-form-input
                                        type="email"
                                        id="input-email"
                                        v-model="loginForm.email"
                                        required
                                        placeholder="Email"
                                ></b-form-input>
                            </b-form-group>
                            <b-form-group label-for="input-password">
                                <b-form-input
                                        type="password"
                                        id="input-password"
                                        v-model="loginForm.password"
                                        required
                                        placeholder="Password"
                                ></b-form-input>
                                <p class="mt-2 mb-0" style="cursor: pointer" v-b-toggle.collapse-lost-password-form>I lost my password.</p>
                            </b-form-group>
                            <b-button type="submit" size="lg" block variant="primary" class="px-5"><i class="fas fa-unlock-alt"></i> Login</b-button>
                            <p class="mt-2 mb-0" style="cursor: pointer" v-b-toggle.collapse-registration-form>I'm not registered yet.</p>
                        </b-form>
                    </b-collapse>
                    <b-collapse id="collapse-registration-form" accordion="login-form">
                        <b-form @submit="onSubmitRegistrationForm">
                            <b-alert :show="registrationFormErrorsGlobal" variant="danger">{{ registrationFormErrorsGlobal }}</b-alert>
                            <b-form-group :invalid-feedback="registrationFormErrorsViolations.email" :state="registrationFormErrorsViolations.email === null ? null : false">
                                <b-form-input
                                        type="email"
                                        id="input-registration-email"
                                        v-model="registrationForm.email"
                                        :state="registrationFormErrorsViolations.email === null ? null : false"
                                        required
                                        placeholder="Email"
                                ></b-form-input>
                            </b-form-group>
                            <b-form-group :invalid-feedback="registrationFormErrorsViolations.password" :state="registrationFormErrorsViolations.password === null ? null : false">
                                <b-input-group>
                                    <b-form-input
                                            :type="registrationFormPasswordVisible ? 'text' : 'password'"
                                            id="input-registration-password"
                                            v-model="registrationForm.password"
                                            :state="registrationFormErrorsViolations.password === null ? null : false"
                                            required
                                            placeholder="Password"
                                    ></b-form-input>
                                    <b-input-group-append>
                                        <b-button variant="info" v-b-tooltip.hover :title="registrationFormPasswordVisible ? 'Hide password' : 'Show password'" @click="registrationFormPasswordVisible = !registrationFormPasswordVisible"><i :class="{'fas fa-eye': registrationFormPasswordVisible, 'fas fa-eye-slash': !registrationFormPasswordVisible}"></i></b-button>
                                    </b-input-group-append>
                                </b-input-group>
                            </b-form-group>
                            <b-button type="submit" size="lg" block variant="primary" class="px-5"><i class="fas fa-id-card"></i> Register</b-button>
                            <p class="mt-2 mb-0" style="cursor: pointer" v-b-toggle.collapse-login-form>I'm already registered.</p>
                        </b-form>
                    </b-collapse>
                    <b-collapse id="collapse-lost-password-form" accordion="login-form">
                        <b-form @submit="onSubmitLostPasswordForm">
                            <b-alert :show="lostPasswordFormErrorsGlobal" variant="danger">{{ lostPasswordFormErrorsGlobal }}</b-alert>
                            <b-form-group :invalid-feedback="lostPasswordFormErrorsViolations.email" :state="lostPasswordFormErrorsViolations.email === null ? null : false">
                                <b-form-input
                                        type="email"
                                        id="input-lost-password-email"
                                        v-model="lostPasswordForm.email"
                                        :state="lostPasswordFormErrorsViolations.email === null ? null : false"
                                        required
                                        placeholder="Email"
                                ></b-form-input>
                            </b-form-group>
                            <b-button type="submit" size="lg" block variant="primary" class="px-5"><i class="fas fa-envelope"></i> Send me a new password</b-button>
                            <p class="mt-2 mb-0" style="cursor: pointer" v-b-toggle.collapse-login-form>I remember my password.</p>
                        </b-form>
                    </b-collapse>
                </div>
            </b-col>
        </b-row>
    </b-modal>
</template>

<script>
    import axios from 'axios';
    import qs from 'qs';

    export default {
        name: 'registration-and-login-modal',
        components: {},
        data() {
            return {
                loginForm: {email: null, password: null},
                registrationForm: {email: null, password: null},
                lostPasswordForm: {email: null},
                loginFormShow: true,
                loginFormErrorsGlobal: null,
                loginNotConfirmed: null,
                registrationFormShow: false,
                resendConfirmationEmailDisabled: false,
                registrationFormSuccessMessage: null,
                registrationFormPasswordVisible: false,
                registrationFormErrorsGlobal: null,
                registrationFormErrorsViolations: {email: null, password: null},
                lostPasswordFormSuccessMessage: null,
                lostPasswordFormErrorsGlobal: null,
                lostPasswordFormErrorsViolations: {email: null},
            };
        },
        props: ['discordLoginUrl'],
        created() {
        },
        computed: {
        },
        methods: {
            onSubmitLoginForm(ev) {
                ev.preventDefault();

                this.lostPasswordFormSuccessMessage = null;
                this.lostPasswordFormErrorsGlobal = null;
                this.lostPasswordFormErrorsViolations = {email: null};
                this.registrationFormSuccessMessage = null;
                this.registrationFormErrorsGlobal = null;
                this.registrationFormErrorsViolations = {email: null, password: null};
                this.loginFormErrorsGlobal = null;
                this.loginNotConfirmed = null;
                axios({
                    method: 'POST',
                    url: '/api/login/check-form-login',
                    data: qs.stringify({
                        '_username': this.loginForm.email,
                        '_password': this.loginForm.password,
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                }).then(response => {
                    window.location = response.data.redirectTo;
                }).catch(err => {
                    if (err.response.data.errorMessage) {
                        this.loginFormErrorsGlobal = err.response.data.errorMessage;
                        if (err.response.data.error === 'not_confirmed_registration') {
                            this.loginNotConfirmed = this.loginForm.email;
                        }
                    } else {
                        this.loginFormErrorsGlobal = 'An unexpected error has occurred. Please try again in a moment.';
                    }
                });
            },
            resendConfirmationEmail() {
                if (!this.loginNotConfirmed) {
                    return;
                }
                this.resendConfirmationEmailDisabled = true;
                axios({
                    method: 'POST',
                    url: '/api/resend-registration-confirmation',
                    data: qs.stringify({
                        'username': this.loginNotConfirmed,
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                }).then(response => {
                    this.resendConfirmationEmailDisabled = false;
                }).catch(err => {
                    this.resendConfirmationEmailDisabled = false;
                });
            },
            onSubmitRegistrationForm(ev) {
                ev.preventDefault();

                this.lostPasswordFormSuccessMessage = null;
                this.lostPasswordFormErrorsGlobal = null;
                this.lostPasswordFormErrorsViolations = {email: null};
                this.registrationFormSuccessMessage = null;
                this.registrationFormErrorsGlobal = null;
                this.registrationFormErrorsViolations = {email: null, password: null};
                this.loginFormErrorsGlobal = null;
                axios({
                    method: 'POST',
                    url: '/api/register',
                    data: {
                        email: this.registrationForm.email,
                        password: this.registrationForm.password,
                    },
                }).then(response => {
                    this.$root.$emit('bv::toggle::collapse', 'collapse-login-form');
                    this.registrationFormSuccessMessage = 'Well done! An email has been sent to you to confirm your registration.';
                }).catch(err => {
                    if (err.response.data.formErrors) {
                        for (let violation of err.response.data.formErrors.violations) {
                            this.$set(this.registrationFormErrorsViolations, violation.propertyPath, violation.title);
                        }
                    } else {
                        this.registrationFormErrorsGlobal = 'An unexpected error has occurred. Please try again in a moment.';
                    }
                });
            },
            onSubmitLostPasswordForm(ev) {
                ev.preventDefault();

                this.lostPasswordFormSuccessMessage = null;
                this.lostPasswordFormErrorsGlobal = null;
                this.lostPasswordFormErrorsViolations = {email: null};
                this.registrationFormSuccessMessage = null;
                this.registrationFormErrorsGlobal = null;
                this.registrationFormErrorsViolations = {email: null, password: null};
                this.loginFormErrorsGlobal = null;
                axios({
                    method: 'POST',
                    url: '/api/lost-password',
                    data: {
                        email: this.lostPasswordForm.email,
                    },
                }).then(response => {
                    this.$root.$emit('bv::toggle::collapse', 'collapse-login-form');
                    this.lostPasswordFormSuccessMessage = 'If we recognize this email, we will send to you the instructions to create a new password.';
                }).catch(err => {
                    if (err.response.data.formErrors) {
                        for (let violation of err.response.data.formErrors.violations) {
                            this.$set(this.lostPasswordFormErrorsViolations, violation.propertyPath, violation.title);
                        }
                    } else {
                        this.lostPasswordFormErrorsGlobal = 'An unexpected error has occurred. Please try again in a moment.';
                    }
                });
            },
        },
    }
</script>

<style lang="scss">
    #modal-login {
        .modal-title {
            font-size: 1.2rem;
        }
    }
</style>
