<template>
    <b-card header="Update Star Citizen handle" class="js-update-sc-handle">
        <p>
            <strong>SC Handle : </strong>{{ citizen != null ? citizen.actualHandle.handle : '' }}<br/>
            <strong>SC Number : </strong>{{ citizen != null ? citizen.number.number : '' }}
        </p>
        <b-form @submit="onSubmit">
            <b-alert variant="danger" :show="showError" v-html="errorMessage"></b-alert>
            <b-form-group>
                <b-form-input id="form_update_sc_handle"
                              type="text"
                              v-model="form.handle"
                              required
                              placeholder="Your new Handle Star Citizen"></b-form-input>
            </b-form-group>
            <b-button type="submit" :disabled="submitDisabled" variant="primary"><i class="fas fa-cloud-upload-alt"></i> Update my SC handle</b-button>
        </b-form>
    </b-card>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';

    export default {
        name: 'update-sc-handle',
        components: {},
        data() {
            return {
                form: {
                    handle: null,
                },
                submitDisabled: false,
                showError: false,
                errorMessage: null,
            }
        },
        props: ['citizen'],
        methods: {
            onSubmit(ev) {
                ev.preventDefault();

                const form = new FormData();
                form.append('handleSC', this.form.handle);

                this.showError = false;
                this.errorMessage = 'An error has been occurred. Please try again later.';
                this.submitDisabled = true;
                axios({
                    method: 'post',
                    url: '/api/profile/update-handle',
                    data: form,
                }).then(response => {
                    this.submitDisabled = false;
                    this.form.handle = null;
                    toastr.success('Your new SC Handle has been successfully updated!');
                }).catch(err => {
                    this.checkAuth(err.response);
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
