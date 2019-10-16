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
                <template v-if="isDiscordLinked">
                    <b-button size="lg" disabled style="background-color: #7289da; color: #fff;" class="px-5" href="/connect/discord"><i class="fab fa-discord"></i> Link your Discord</b-button> <i class="fas fa-check text-success font-3xl d-inline-block align-middle align-content-center"></i>
                </template>
                <b-button v-if="!isDiscordLinked" size="lg" style="background-color: #7289da; color: #fff;" class="px-5" href="/connect/discord"><i class="fab fa-discord"></i> Link your Discord</b-button>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    import ChangePassword from "./ChangePassword";
    import ChangeEmail from "./ChangeEmail";

    export default {
        name: 'security',
        components: {ChangeEmail, ChangePassword},
        data() {
            return {
            }
        },
        props: ['user'],
        computed: {
            isEmailRegistered() {
                return this.user.email != null;
            },
            isDiscordLinked() {
                return this.user.discordId != null;
            }
        },
    }
</script>
