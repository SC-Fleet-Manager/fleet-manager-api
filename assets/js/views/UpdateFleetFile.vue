<template>
    <b-form @submit="onSubmit">
        <b-alert variant="info" show>In order to <strong>upload your fleet</strong>, you need to use <em>Fleet Manager</em> browser extension:
            <a target="_blank" href="https://fleet-extension.fallkrom.space/fleet_manager_extension-latest.xpi">Firefox</a> - <a target="_blank" href="https://chrome.google.com/webstore/detail/fleet-manager-extension/hbbadomkekhkhemjjmhkhgiokjhpobhk">Chrome</a>.<br/>
            Then go to <a target="_blank" href="https://robertsspaceindustries.com/account/pledges">your Hangar in your RSI account</a> and click on <strong>Export to Fleet Manager</strong> button.</b-alert>
        <b-button v-b-toggle.collapse-upload-manual variant="primary">Nope! I want to upload it myself.</b-button>
        <b-collapse id="collapse-upload-manual" class="mt-2">
            <b-alert variant="info" show>In order to <strong>generate your fleet file</strong>, you need to use <em>Hangar EXPLORer</em> browser extension:
                <a target="_blank" href="https://addons.mozilla.org/en-US/firefox/addon/star-citizen-hangar-xplorer/">Firefox</a> - <a target="_blank" href="https://chrome.google.com/webstore/detail/star-citizen-hangar-xplor/bhkgemjdepodofcnmekdobmmbifemhkc">Chrome</a>.<br/>
                Then go to <a target="_blank" href="https://robertsspaceindustries.com/account/pledges">your Hangar in your RSI account</a> and click on <strong>Download JSON</strong> button.</b-alert>
            <b-alert variant="danger" :show="showError" v-html="errorMessage"></b-alert>
            <b-form-group>
                <b-form-file id="form_fleetfile"
                             v-model="form.fleetFile"
                             :state="Boolean(form.fleetFile)"
                             required
                             placeholder="Choose/Drop your fleet file (.json)"
                             accept=".json"></b-form-file>
            </b-form-group>
            <b-button type="submit" :disabled="submitDisabled" variant="success">Update my fleet</b-button>
        </b-collapse>
    </b-form>
</template>

<script>
    import axios from 'axios';
    import toastr from "toastr";

    export default {
        name: 'update-fleet-file',
        components: {},
        data() {
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
                    method: 'POST',
                    url: '/api/upload',
                    data: form,
                }).then(response => {
                    this.submitDisabled = false;
                    toastr.success('Your fleet has been successfully updated!');
                    this.$emit('success');
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

<style>
    .custom-file-input:lang(fr)~.custom-file-label::after {
        content: "Parcourir";
    }
</style>
