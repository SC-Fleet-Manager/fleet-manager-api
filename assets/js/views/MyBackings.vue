<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card title="TAMER">
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import moment from 'moment-timezone';
    import {mapMutations} from "vuex";

    export default {
        name: 'my-backings',
        props: [],
        components: {},
        data() {
            return {
                citizen: null,
            }
        },
        created() {
            this.refreshProfile();
        },
        methods: {
            ...mapMutations(['updateProfile']),
            refreshProfile() {
                axios.get('/api/profile').then(response => {
                    this.user = response.data;
                    this.citizen = this.user.citizen;
                    this.updateProfile(this.citizen);

                    if (this.citizen) {

                    }
                }).catch(err => {
                    this.checkAuth(err.response);
                    this.showError = true;
                    if (err.response.data.errorMessage) {
                        this.errorMessage = err.response.data.errorMessage;
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
            },
        }
    }
</script>

<style>
</style>
