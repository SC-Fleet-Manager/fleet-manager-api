<template>
    <b-card header="Security" class="js-security" v-if="isEmailRegistered">
        <b-row>
            <b-col cols="6">
                <ChangeEmail :user="user"></ChangeEmail>
            </b-col>
            <b-col cols="6">
                <ChangePassword :user="user"></ChangePassword>
            </b-col>
            <b-col cols="6">
                <h5 class="mb-3">Social networks</h5>
                <b-alert variant="danger" :show="errorMessage != null" v-html="errorMessage"></b-alert>
                <b-alert variant="warning" :show="conflict != null" v-if="conflict != null">
                    <p>Your Discord account is already linked to another user.<br/>
                        Please choose which Citizen you want to keep (<strong><i class="fas fa-exclamation-triangle"></i> the other will be deleted</strong>).</p>
                    <b-form @submit="onSubmit">
                        <b-form-group :invalid-feedback="formViolations.choiceResolveConflict" :state="formViolations.choiceResolveConflict === null ? null : false">
                            <b-form-radio v-model="form.choiceResolveConflict" name="choice-resolve-conflict" :value="conflict.me.citizen">{{ conflict.me.citizen.actualHandle.handle }} ({{ conflict.me.citizen.nickname }}) <i class="fas fa-info-circle" v-b-tooltip.hover title="Your current Citizen."></i></b-form-radio>
                            <b-form-radio v-model="form.choiceResolveConflict" name="choice-resolve-conflict" :value="conflict.alreadyLinkedUser.citizen.id">{{ conflict.alreadyLinkedUser.citizen.actualHandle.handle }} ({{ conflict.alreadyLinkedUser.citizen.nickname }}) <i class="fas fa-info-circle" v-b-tooltip.hover title="This Citizen is used by the other user which has your Discord account."></i></b-form-radio>
                        </b-form-group>
                        <b-button type="submit" :disabled="submitDisabled" variant="primary">Link my Discord account and use the selected Citizen.</b-button>
                    </b-form>
                </b-alert>
                <template v-if="isDiscordLinked">
                    <b-button size="lg" disabled style="background-color: #7289da; color: #fff;" class="px-5" href="/connect/discord"><i class="fab fa-discord"></i> Link my Discord</b-button> <i class="fas fa-check text-success font-3xl d-inline-block align-middle align-content-center"></i>
                </template>
                <b-button v-if="!isDiscordLinked" size="lg" style="background-color: #7289da; color: #fff;" class="px-5" href="/connect/discord"><i class="fab fa-discord"></i> Link my Discord</b-button>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import ChangePassword from "./ChangePassword";
    import ChangeEmail from "./ChangeEmail";

    export default {
        name: 'security',
        components: {ChangeEmail, ChangePassword},
        data() {
            return {
                linkDiscordWarning: null,
                errorMessage: null,
                conflict: null,
                submitDisabled: false,
                form: {choiceResolveConflict: null},
                formViolations: {choiceResolveConflict: null},
            };
        },
        props: ['user'],
        created() {
            const error = (new URL(document.location)).searchParams.get('error');
            if (error === 'already_linked_discord') {
                this.linkDiscordWarning = `Sorry, your Discord account is already linked to another user.`;

                this.errorMessage = null;
                this.conflict = null;
                axios({
                    method: 'get',
                    url: '/api/profile/conflict-connect/discord',
                }).then(response => {
                    this.conflict = response.data;
                }).catch(err => {
                    if (err.response.data.error) {
                        this.errorMessage = err.response.data.errorMessage;
                    } else {
                        this.errorMessage = 'An unexpected error has occurred. Please try again in a moment.';
                    }
                });
            }
        },
        computed: {
            isEmailRegistered() {
                return this.user.email != null;
            },
            isDiscordLinked() {
                return this.user.discordId != null;
            },
        },
        methods: {
            onSubmit(ev) {
                ev.preventDefault();

                const form = new FormData();
                form.append('conflictChoice', this.form.choiceResolveConflict);

                this.errorMessage = null;
                this.submitDisabled = true;
                this.formViolations = {choiceResolveConflict: null};
                axios({
                    method: 'post',
                    url: '/api/profile/resolve-conflict-connect/discord',
                    data: form,
                }).then(response => {
                    this.submitDisabled = false;
                    this.form = {choiceResolveConflict: null};
                    toastr.success('Your Discord account is successfully linked!');
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
            },
        },
    }
</script>
