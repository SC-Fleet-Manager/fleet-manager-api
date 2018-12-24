<template>
    <b-card header="Update your SC Handle">
        <b-form @submit="onSubmit">
            <b-alert variant="success" :show="showSuccess">Your new SC Handle has been successfully updated!</b-alert>
            <b-alert variant="danger" :show="showError">{{ errorMessage }}</b-alert>
            <b-form-group label="Your new Star Citizen Handle" label-for="form_update_sc_handle">
                <b-form-input id="form_update_sc_handle"
                              type="text"
                              v-model="form.handle"
                              required
                              placeholder="Your new Handle Star Citizen"></b-form-input>
            </b-form-group>
            <b-button type="submit" :disabled="submitDisabled" variant="success">Update my SC handle</b-button>
        </b-form>
    </b-card>
</template>

<script>
    import axios from 'axios';

    export default {
        name: 'update-sc-handle',
        components: {},
        data: function () {
            return {
                form: {
                    handle: null,
                },
                submitDisabled: false,
                showError: false,
                showSuccess: false,
                errorMessage: null,
            }
        },
        created() {
        },
        methods: {
            onSubmit(ev) {
                ev.preventDefault();

                const form = new FormData();
                form.append('handleSC', this.form.handle);

                this.showError = false;
                this.showSuccess = false;
                this.errorMessage = 'An error has been occurred. Please try again in a moment.';
                this.submitDisabled = true;
                axios({
                    method: 'post',
                    url: '/update-handle',
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
