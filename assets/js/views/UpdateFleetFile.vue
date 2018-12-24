<template>
    <b-form @submit="onSubmit">
        <b-alert variant="danger" :show="showError" v-html="errorMessage"></b-alert>
        <b-form-group label="Your fleet (.json)" label-for="form_fleetfile">
            <b-form-file id="form_fleetfile"
                         v-model="form.fleetFile"
                         :state="Boolean(form.fleetFile)"
                         required
                         placeholder="Choose/Drop your file..."
                         accept=".json"></b-form-file>
        </b-form-group>
        <b-button type="submit" :disabled="submitDisabled" variant="success">Update</b-button>
    </b-form>
</template>

<script>
    import axios from 'axios';
    import toastr from "toastr";

    export default {
        name: 'update-fleet-file',
        components: {},
        data: function () {
            return {
                form: {
                    fleetFile: null,
                },
                showError: false,
                errorMessage: '',
                submitDisabled: false,
            }
        },
        created() {
        },
        methods: {
            onSubmit(ev) {
                ev.preventDefault();

                const form = new FormData();
                form.append('fleetFile', this.form.fleetFile);

                this.showError = false;
                this.errorMessage = 'An error has been occurred. Please try again in a moment.';
                this.submitDisabled = true;
                axios({
                    method: 'post',
                    url: '/upload',
                    data: form,
                }).then(response => {
                    this.submitDisabled = false;
                    toastr.success('Your fleet has been successfully updated!');
                    this.$emit('success');
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

<style>
    .custom-file-input:lang(fr)~.custom-file-label::after {
        content: "Parcourir";
    }
</style>
