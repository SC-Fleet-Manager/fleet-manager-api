<template>
    <modal name="hello-world" id="modal-registration-login" height="auto">
        <header class="header">
            <i class="fas fa-sign-in-alt"></i> Connect to Fleet Manager
            <i class="fas fa-times" @click="$modal.hide('hello-world')"></i>
        </header>
        <section class="content">
            <a class="btn btn-discord" href="/connect/discord"><i class="fab fa-discord"></i> Log in with Discord</a>
            <div class="alert alert-success" v-if="registrationFormSuccessMessage">{{ registrationFormSuccessMessage }}</div>
            <div class="alert alert-success" v-if="lostPasswordFormSuccessMessage">{{ lostPasswordFormSuccessMessage }}</div>
            <div class="collapse" id="collapse-login-form" style="height: 0; overflow: hidden;">
                <form @submit="onSubmitLoginForm">
                    <div v-if="loginFormErrorsGlobal" class="alert alert-danger">
                        {{ loginFormErrorsGlobal }}
                        <button v-if="loginNotConfirmed != null" :disabled="resendConfirmationEmailDisabled" type="button" @click="resendConfirmationEmail"><i class="fas fa-redo"></i> Resend a confirmation email</button>
                    </div>
                    <div>
                        <input type="email" class="form-control" id="input-email" v-model="loginForm.email" required placeholder="Email">
                    </div>
                    <div>
                        <input type="password" class="form-control" id="input-password" v-model="loginForm.password" required placeholder="Password">
                    </div>
                    <div class="rememberme">
                        <div class="pretty p-default p-curve">
                            <input type="checkbox" v-model="loginForm.rememberMe">
                            <div class="state">
                                <label>Remember me</label>
                            </div>
                        </div>
                        <div class="link">lost your password?</div>
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Log in</button>
                    <p class="no-account">Don't have an account? <span class="link" @click="showSignup">Sign up</span></p>
                </form>
            </div>
            <div class="collapse" id="collapse-registration-form" style="overflow: hidden;">
                <form @submit="onSubmitRegistrationForm">
                    <div v-if="registrationFormErrorsGlobal" class="alert alert-danger">
                        {{ registrationFormErrorsGlobal }}
                    </div>
                    <div :class="{'has-errors': registrationFormErrorsViolations.email !== null}">
                        <input type="email" class="form-control" id="input-registration-email" v-model="registrationForm.email" required placeholder="Email">
                        <p class="form-errors" v-if="registrationFormErrorsViolations.email">{{ registrationFormErrorsViolations.email }}</p>
                    </div>
                    <div class="form-group" :class="{'has-errors': registrationFormErrorsViolations.email !== null}">
                        <input :type="registrationFormPasswordVisible ? 'text' : 'password'" class="form-control" id="input-registration-password" v-model="registrationForm.password" required placeholder="Password">
                        <p class="form-errors" v-if="registrationFormErrorsViolations.password">{{ registrationFormErrorsViolations.password }}</p>
                        <div class="input-append">
                            <button type="button" class="btn" :title="registrationFormPasswordVisible ? 'Hide password' : 'Show password'" @click="registrationFormPasswordVisible = !registrationFormPasswordVisible"><i :class="{'fas fa-eye': registrationFormPasswordVisible, 'fas fa-eye-slash': !registrationFormPasswordVisible}"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-id-card"></i> Register</button>
                    <p class="no-account link" @click="showLogin">I'm already registered.</p>
                </form>
            </div>
        </section>
    </modal>
</template>

<script>
    import axios from 'axios';
    import qs from 'qs';
    import anime from 'animejs/lib/anime.es.js';

    export default {
        name: 'registration-and-login-modal',
        components: {},
        data() {
            return {
                loginForm: {email: null, password: null, rememberMe: false},
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
        methods: {
            showLogin() {
                document.getElementById('collapse-registration-form').style.height = document.getElementById('collapse-registration-form').clientHeight+'px';
                anime({
                    targets: '#collapse-registration-form',
                    height: 0,
                    duration: 300,
                    easing: 'linear',
                    complete: () => {
                        document.getElementById('collapse-registration-form').style.display = 'none';
                    }
                });
                document.getElementById('collapse-login-form').style.display = 'block';
                anime({
                    targets: '#collapse-login-form',
                    height: document.getElementById('collapse-login-form').scrollHeight,
                    duration: 300,
                    easing: 'linear',
                    complete: () => {
                        document.getElementById('collapse-login-form').style.height = 'auto';
                    }
                });
            },
            showSignup() {
                document.getElementById('collapse-login-form').style.height = document.getElementById('collapse-login-form').clientHeight+'px';
                anime({
                    targets: '#collapse-login-form',
                    height: 0,
                    duration: 300,
                    easing: 'linear',
                    complete: () => {
                        document.getElementById('collapse-login-form').style.display = 'none';
                    }
                });
                document.getElementById('collapse-registration-form').style.display = 'block';
                anime({
                    targets: '#collapse-registration-form',
                    height: document.getElementById('collapse-registration-form').scrollHeight,
                    duration: 300,
                    easing: 'linear',
                    complete: () => {
                        document.getElementById('collapse-registration-form').style.height = 'auto';
                    }
                });
            },
            show() {
                this.$modal.show('hello-world');
            },
            hide() {
                this.$modal.hide('hello-world');
            },
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
                        '_remember_me': this.loginForm.rememberMe,
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
                    this.showLogin();
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
    $fa-font-path: '~@fortawesome/fontawesome-free/webfonts/';
    @import '~@fortawesome/fontawesome-free/scss/fontawesome';
    @import '~@fortawesome/fontawesome-free/scss/solid';
    @import '~@fortawesome/fontawesome-free/scss/brands';
    @import '~pretty-checkbox/src/pretty-checkbox.scss';
    @import '../../css/frontpage/registration_login_modal.scss';
</style>
