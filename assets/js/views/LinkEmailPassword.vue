<template>
    <div>
        <b-form @submit="onSubmit">
            <b-alert variant="danger" :show="errorMessage != null" v-html="errorMessage"></b-alert>
            <b-form-group :invalid-feedback="formViolations.email" :state="formViolations.email === null ? null : false">
                <b-input-group prependHtml="<i class='fa fa-envelope'></i>">
                    <b-form-input
                            type="email"
                            v-model="form.email"
                            :state="formViolations.email === null ? null : false"
                            placeholder="My login email"
                    ></b-form-input>
                </b-input-group>
            </b-form-group>
            <b-form-group :invalid-feedback="formViolations.password" :state="formViolations.password === null ? null : false">
                <b-input-group prependHtml="<i class='fa fa-key'></i>">
                    <b-form-input
                            :type="visiblePassword ? 'text' : 'password'"
                            id="input-change-password-password"
                            v-model="form.password"
                            :state="formViolations.password === null ? null : false"
                            placeholder="My login password"
                    ></b-form-input>
                    <b-input-group-append>
                        <b-button variant="info" v-b-tooltip.hover :title="visiblePassword ? 'Hide password' : 'Show password'" @click="visiblePassword = !visiblePassword"><i :class="{'fas fa-eye': visiblePassword, 'fas fa-eye-slash': !visiblePassword}"></i></b-button>
                    </b-input-group-append>
                </b-input-group>
            </b-form-group>
            <b-button type="submit" size="lg" block variant="success" class="px-5"><i class="fas fa-check"></i> Link my email/password</b-button>
        </b-form>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: 'link-email-password',
        components: {},
        data() {
            return {
                form: {
                    email: null,
                    password: null,
                },
                formViolations: {
                    email: null,
                    password: null,
                },
                visiblePassword: false,
                submitDisabled: false,
                errorMessage: null,
            }
        },
        props: ['user'],
        methods: {
            onSubmit(ev) {
                ev.preventDefault();

                const form = new FormData();
                form.append('email', this.form.email);
                form.append('password', this.form.password);

                this.errorMessage = null;
                this.submitDisabled = true;
                this.formViolations = {email: null, password: null};
                axios({
                    method: 'post',
                    url: '/api/profile/link-email-password',
                    data: form,
                }).then(response => {
                    this.submitDisabled = false;
                    this.formViolations = {email: null, password: null};
                    this.$emit('success');
                }).catch(err => {
                    this.submitDisabled = false;
                    if (err.response.data.formErrors) {
                        for (let violation of err.response.data.formErrors.violations) {
                            this.$set(this.formViolations, violation.propertyPath, violation.title);
                        }
                    } else if (err.response.data.errorMessage) {
                        this.errorMessage = err.response.data.errorMessage;
                    } else {
                        this.errorMessage = 'An unexpected error has occurred. Please try again in a moment.';
                    }
                });
            }
        }
    }
</script>
