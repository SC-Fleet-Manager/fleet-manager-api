<template>
    <b-card header="Security" class="js-security" >
        <b-row>
            <b-col col xl="6" lg="12" v-if="isEmailRegistered" class="mb-3">
                <ChangeEmail :user="user"></ChangeEmail>
            </b-col>
            <b-col col xl="6" lg="12" v-if="isEmailRegistered" class="mb-3">
                <ChangePassword :user="user"></ChangePassword>
            </b-col>
            <b-col col xl="6" lg="12" class="mb-3">
                <h5 class="mb-3">My Sign-Ins</h5>

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
                <div class="mb-3">
                    <b-button v-if="isDiscordLinked" size="lg" block disabled style="background-color: #7289da; color: #fff;" class="px-5" href="/connect/discord"><i class="fab fa-discord"></i> Discord linked <i class="fas fa-check"></i></b-button>
                    <b-button v-else size="lg" block style="background-color: #7289da; color: #fff;" class="px-5" href="/connect/discord"><i class="fab fa-discord"></i> Link my Discord</b-button>
                </div>

                <b-alert variant="success" :show="linkEmailPasswordSuccessMessage != null" v-html="linkEmailPasswordSuccessMessage"></b-alert>
                <template v-if="!isEmailRegistered">
                    <b-collapse id="collapse-link-email-password" accordion="login-form" v-model="linkEmailPasswordCollapsed" visible>
                        <LinkEmailPassword class="mb-3" @success="onLinkEmailPasswordSuccess"></LinkEmailPassword>
                    </b-collapse>
                    <b-button v-if="!linkEmailPasswordCollapsed" size="lg" block variant="primary" v-b-toggle.collapse-link-email-password><i class="fas fa-key"></i> Link with email/password</b-button>
                </template>
                <b-button v-else size="lg" block variant="primary" disabled><i class="fas fa-key"></i> Email/password linked <i class="fas fa-check"></i></b-button>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import ChangePassword from "./ChangePassword";
    import ChangeEmail from "./ChangeEmail";
    import LinkEmailPassword from "./LinkEmailPassword";

    export default {
        name: 'security',
        components: {LinkEmailPassword, ChangeEmail, ChangePassword},
        data() {
            return {
                linkDiscordWarning: null,
                errorMessage: null,
                conflict: null,
                submitDisabled: false,
                form: {choiceResolveConflict: null},
                formViolations: {choiceResolveConflict: null},
                linkEmailPasswordCollapsed: false,
                linkEmailPasswordSuccessMessage: null,
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
                return this.user.email != null && this.user.emailConfirmed;
            },
            isDiscordLinked() {
                return this.user.discordId != null;
            },
        },
        methods: {
            onLinkEmailPasswordSuccess() {
                this.linkEmailPasswordCollapsed = false;
                this.linkEmailPasswordSuccessMessage = 'Your email/password has been successfully linked. A confirmation email has been sent to you, check your inbox/spams.';
                this.$emit('accountLinked');
            },
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
                    this.conflict = null; // hide the warning form
                    this.$emit('accountLinked');
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
