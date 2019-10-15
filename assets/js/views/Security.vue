<template>
    <b-card header="Security" class="js-security" v-if="isEmailRegistered">
        <b-row>
            <b-col>
                <ChangeEmail :user="user"></ChangeEmail>
            </b-col>
            <b-col>
                <ChangePassword :user="user"></ChangePassword>
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
        },
    }
</script>
