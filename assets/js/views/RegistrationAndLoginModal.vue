<template>
    <modal name="modal-registration-login" id="modal-registration-login" height="auto" :adaptive="true" overlayTransition="nice-modal-fade">
        <header class="header">
            <i class="fas fa-sign-in-alt"></i> Connect to Fleet Manager
            <i class="fas fa-times" @click="$modal.hide('modal-registration-login')"></i>
        </header>
        <section class="content">
            <a class="btn btn-discord" href="/connect/discord"><i class="fab fa-discord"></i> Log in with Discord</a>
            <div class="alert alert-success" v-if="registrationFormSuccessMessage">{{ registrationFormSuccessMessage }}</div>
            <div class="alert alert-success" v-if="lostPasswordFormSuccessMessage">{{ lostPasswordFormSuccessMessage }}</div>
            <div class="collapse" id="collapse-login-form" style="overflow: hidden;">
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
                        <div class="link" @click="showCollapse('collapse-lost-password-form')">lost your password?</div>
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Log in</button>
                    <p class="bottom-line">Don't have an account? <span class="link" @click="showCollapse('collapse-registration-form')">Sign up</span></p>
                </form>
            </div>
            <div class="collapse" id="collapse-registration-form" style="height: 0; overflow: hidden;">
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
                    <p class="bottom-line link" @click="showCollapse('collapse-login-form')">I'm already registered.</p>
                </form>
            </div>
            <div class="collapse" id="collapse-lost-password-form" style="height: 0; overflow: hidden;">
                <form @submit="onSubmitLostPasswordForm">
                    <div v-if="lostPasswordFormErrorsGlobal" class="alert alert-danger">
                        {{ lostPasswordFormErrorsGlobal }}
                    </div>
                    <div :class="{'has-errors': lostPasswordFormErrorsViolations.email !== null}">
                        <input type="email" class="form-control" id="input-lost-password-email" v-model="lostPasswordForm.email" required placeholder="Email">
                        <p class="form-errors" v-if="lostPasswordFormErrorsViolations.email">{{ lostPasswordFormErrorsViolations.email }}</p>
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-envelope"></i> Send me a new password</button>
                    <p class="bottom-line">You remember your password? <span class="link" @click="showCollapse('collapse-login-form')">Log in</span></p>
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
            closeCollapses(exceptElement) {
                document.querySelectorAll('.collapse').forEach((el) => {
                    if (el == exceptElement) {
                        return;
                    }
                    el.style.height = el.clientHeight+'px';
                    anime({
                        targets: el,
                        height: 0,
                        duration: 300,
                        easing: 'linear',
                        complete: () => {
                            el.style.display = 'none';
                        }
                    });
                });
            },
            showCollapse(elementId) {
                const el = document.getElementById(elementId);
                this.closeCollapses(el);
                el.style.display = 'block';
                anime({
                    targets: el,
                    height: el.scrollHeight,
                    duration: 300,
                    easing: 'linear',
                    complete: () => {
                        el.style.height = 'auto';
                    }
                });
            },
            show() {
                this.$modal.show('modal-registration-login');
            },
            hide() {
                this.$modal.hide('modal-registration-login');
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
    @import '~pretty-checkbox/src/pretty-checkbox.scss';
    @import '../../css/frontpage/registration_login_modal.scss';
</style>
