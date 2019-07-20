<template>
    <div class="">
        <div class="card">
            <div class="card-header">Last changes</div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <p v-for="change in changes" :key="change.id" class="mb-2">
                    {{ formatDate(change.createdAt) }}
                    <b-badge style="width: 6rem;" :variant="getVariantChangeType(change)">{{ getTitleChangeType(change) }}</b-badge>
                    <span v-if="change.type == 'upload_fleet'">
                        <i v-if="change.author === null">Hidden citizen</i><b v-else>{{ change.author.actualHandle.handle }}</b> :
                        <template v-for="(ship, index) in change.payload">{{ ship.count > 0 ? '+'+ship.count : ship.count }} {{ ship.ship }}<template v-if="index < change.payload.length - 1">, </template></template>
                    </span>
                    <span v-if="change.type == 'join_orga' || change.type == 'leave_orga'">
                        <i v-if="change.author === null">Hidden citizen</i><b v-else>{{ change.author.actualHandle.handle }}</b>
                    </span>
                    <span v-if="change.type == 'update_privacy_policy'">
                        <b>{{ change.author.actualHandle.handle }}</b> has changed orga's policy from <b>{{ formatOrgaPolicy(change.payload.oldValue) }}</b> to <b>{{ formatOrgaPolicy(change.payload.newValue) }}</b>
                    </span>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import moment from 'moment-timezone';

    export default {
        name: 'organization-changes',
        components: {},
        props: ['selectedSid'],
        data() {
            return {
                changes: [],
            }
        },
        created() {
            this.retrieveChanges();
        },
        methods: {
            retrieveChanges() {
                axios.get(`/api/organization/${this.selectedSid}/changes`).then(response => {
                    this.changes = response.data;
                }).catch(err => {
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    }
                    console.error(err);
                });
            },
            getVariantChangeType(change) {
                switch (change.type) {
                    case 'upload_fleet':
                        return 'info';
                    case 'join_orga':
                        return 'success';
                    case 'leave_orga':
                        return 'danger';
                    case 'update_privacy_policy':
                        return 'warning';
                }
                return 'secondary';
            },
            getTitleChangeType(change) {
                switch (change.type) {
                    case 'upload_fleet':
                        return 'Uploaded fleet';
                    case 'join_orga':
                        return 'Orga joined';
                    case 'leave_orga':
                        return 'Orga leaved';
                    case 'update_privacy_policy':
                        return 'Updated settings';
                }
                return 'Unknown';
            },
            formatOrgaPolicy(policy) {
                switch (policy) {
                    case 'public':
                        return 'Public';
                    case 'private':
                        return 'Members only';
                    case 'admin':
                        return 'Admin only';
                }
                return 'Unkown';
            },
            formatDate(date) {
                return moment(date).format('LLL');
            }
        }
    };
</script>

<style lang="scss">
</style>
