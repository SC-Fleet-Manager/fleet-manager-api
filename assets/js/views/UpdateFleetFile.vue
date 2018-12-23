<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card header="Mettre à jour ma flotte">
                    <b-form @submit="onSubmit">
                        <b-alert variant="success" :show="showSuccess">Votre flotte a été mise à jour avec succès !</b-alert>
                        <b-alert variant="danger" :show="showError" v-html="errorMessage"></b-alert>
                        <!--<b-form-group label="Handle Star Citizen" label-for="form_handle">
                            <b-form-input id="form_handle"
                                          type="text"
                                          v-model="form.handle"
                                          required
                                          placeholder="Entrez votre Handle Star Citizen"></b-form-input>
                        </b-form-group>-->
                        <b-form-group label="Votre flotte (.json)" label-for="form_fleetfile">
                            <b-form-file id="form_fleetfile"
                                         v-model="form.fleetFile"
                                         :state="Boolean(form.fleetFile)"
                                         required
                                         placeholder="Choisissez/Glissez votre fichier..."
                                         accept=".json"></b-form-file>
                        </b-form-group>
                        <b-button type="submit" :disabled="submitDisabled" variant="success">Mettre à jour</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: 'upload-fleet-file',
        components: {},
        data: function () {
            return {
                form: {
                    // handle: null,
                    fleetFile: null,
                },
                showSuccess: false,
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
                // form.append('handleSC', this.form.handle);
                form.append('fleetFile', this.form.fleetFile);

                this.showError = false;
                this.showSuccess = false;
                this.errorMessage = 'Une erreur est survenue. Veuillez réessayer dans quelques instants.';
                this.submitDisabled = true;
                axios({
                    method: 'post',
                    url: '/upload',
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

<style>
    .custom-file-input:lang(fr)~.custom-file-label::after {
        content: "Parcourir";
    }
</style>
