<template>
    <div>
        <h5 class="mb-3">Change my email</h5>
        <b-form @submit="onSubmit">
            <b-alert variant="danger" :show="showError" v-html="errorMessage"></b-alert>
            <b-alert variant="success" :show="successMessage != null" v-html="successMessage"></b-alert>
            <b-form-group :invalid-feedback="formViolations.newEmail" :state="formViolations.newEmail === null ? null : false">
                <b-input-group prependHtml="<i class='fa fa-envelope'></i>">
                    <b-form-input
                            type="email"
                            id="input-change-email-new-email"
                            v-model="form.newEmail"
                            :state="formViolations.newEmail === null ? null : false"
                            placeholder="Your new email"
                    ></b-form-input>
                </b-input-group>
            </b-form-group>
            <b-button type="submit" :disabled="submitDisabled" variant="success"><i class="fas fa-check"></i> Change my email</b-button>
        </b-form>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: 'change-email',
        components: {},
        data() {
            return {
                form: {
                    newEmail: null,
                },
                formViolations: {
                    newEmail: null,
                },
                submitDisabled: false,
                showError: false,
                errorMessage: null,
                successMessage: null,
            }
        },
        props: ['user'],
        methods: {
            onSubmit(ev) {
                ev.preventDefault();

                const form = new FormData();
                form.append('newEmail', this.form.newEmail);

                this.showError = false;
                this.errorMessage = null;
                this.successMessage = null;
                this.submitDisabled = true;
                this.formViolations = {newEmail: null};
                axios({
                    method: 'post',
                    url: '/api/profile/change-email-request',
                    data: form,
                }).then(response => {
                    this.submitDisabled = false;
                    this.form = {newEmail: null};
                    this.successMessage = 'An email has been sent to you to confirm your new email address.';
                }).catch(err => {
                    this.submitDisabled = false;
                    if (err.response.data.formErrors) {
                        for (let violation of err.response.data.formErrors.violations) {
                            this.$set(this.formViolations, violation.propertyPath, violation.title);
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
