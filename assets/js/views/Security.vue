<template>
    <b-card header="Security" class="js-security" v-if="isEmailRegistered">
        <b-row>
            <b-col>
                <h5 class="mb-3">Change my password</h5>
                <b-form @submit="onSubmit">
                    <b-alert variant="danger" :show="showError" v-html="errorMessage"></b-alert>
                    <b-form-group :invalid-feedback="changePasswordFormViolations.oldPassword" :state="changePasswordFormViolations.oldPassword === null ? null : false">
                        <b-form-input
                                type="password"
                                id="input-registration-old-password"
                                v-model="changePasswordForm.oldPassword"
                                :state="changePasswordFormViolations.oldPassword === null ? null : false"
                                placeholder="Your actual password"
                        ></b-form-input>
                    </b-form-group>
                    <b-form-group :invalid-feedback="changePasswordFormViolations.newPassword" :state="changePasswordFormViolations.newPassword === null ? null : false">
                        <b-input-group>
                            <b-form-input
                                    :type="visiblePassword ? 'text' : 'password'"
                                    id="input-registration-password"
                                    v-model="changePasswordForm.newPassword"
                                    :state="changePasswordFormViolations.newPassword === null ? null : false"
                                    placeholder="The new password"
                            ></b-form-input>
                            <b-input-group-append>
                                <b-button variant="info" v-b-tooltip.hover :title="visiblePassword ? 'Hide password' : 'Show password'" @click="visiblePassword = !visiblePassword"><i :class="{'fas fa-eye': visiblePassword, 'fas fa-eye-slash': !visiblePassword}"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form-group>
                    <b-button type="submit" :disabled="submitDisabled" variant="success"><i class="fas fa-key"></i> Change my password</b-button>
                </b-form>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';

    export default {
        name: 'security',
        components: {},
        data() {
            return {
                changePasswordForm: {
                    oldPassword: null,
                    newPassword: null,
                },
                changePasswordFormViolations: {
                    oldPassword: null,
                    newPassword: null,
                },
                visiblePassword: false,
                submitDisabled: false,
                showError: false,
                errorMessage: null,
            }
        },
        props: ['user'],
        computed: {
            isEmailRegistered() {
                return this.user.email != null;
            },
        },
        methods: {
            onSubmit(ev) {
                ev.preventDefault();

                const form = new FormData();
                form.append('oldPassword', this.changePasswordForm.oldPassword);
                form.append('newPassword', this.changePasswordForm.newPassword);

                this.showError = false;
                this.errorMessage = null;
                this.submitDisabled = true;
                this.changePasswordFormViolations = {oldPassword: null, newPassword: null};
                axios({
                    method: 'post',
                    url: '/api/profile/change-password',
                    data: form,
                }).then(response => {
                    this.submitDisabled = false;
                    this.changePasswordForm = {oldPassword: null, newPassword: null};
                    toastr.success('Your password has been successfully updated!');
                }).catch(err => {
                    this.submitDisabled = false;
                    if (err.response.data.formErrors) {
                        for (let violation of err.response.data.formErrors.violations) {
                            this.$set(this.changePasswordFormViolations, violation.propertyPath, violation.title);
                        }
                    } else {
                        this.showError = true;
                        this.errorMessage = 'An unexpected error has occurred. Please try again in a moment.';
                    }
                });
            }
        }
    }
</script>
