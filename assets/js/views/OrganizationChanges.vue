<template>
    <div class="">
        <div class="card">
            <div class="card-header">Last changes</div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <p v-for="change in changes" :key="change.id" class="mb-2">
                    {{ formatDate(change.createdAt) }}
                    <b-badge style="width: 6rem;" :variant="getVariantChangeType(change)">{{ getTitleChangeType(change) }}</b-badge>
                    <span v-if="change.type == 'upload_fleet'">
                        <b>{{ change.author ? change.author.actualHandle.handle : 'Unknown citizen' }}</b> :
                        <i v-if="change.payloadPrivate">hidden</i>
                        <template v-else>
                            <template v-for="(ship, index) in change.payload"><b-badge :variant="ship.count > 0 ? 'success' : 'danger'">{{ ship.count > 0 ? '+'+ship.count : ship.count }}</b-badge> {{ ship.ship }}<template v-if="index < change.payload.length - 1">, </template></template>
                        </template>
                    </span>
                    <span v-if="change.type == 'join_orga' || change.type == 'leave_orga'">
                        <b>{{ change.author ? change.author.actualHandle.handle : 'Unknown citizen' }}</b>
                    </span>
                    <span v-if="change.type == 'update_privacy_policy'">
                        <b>{{ change.author ? change.author.actualHandle.handle : 'Unknown citizen' }}</b> has changed orga's policy from <b>{{ formatOrgaPolicy(change.payload.oldValue) }}</b> to <b>{{ formatOrgaPolicy(change.payload.newValue) }}</b>
                    </span>
                    <span v-if="change.type == 'deleted_citizen'">
                        <b>{{ change.payload.handle }}</b> has been deleted and all of his/her ships.
                    </span>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
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
                        this.$toastr.e(err.response.data.errorMessage);
                    }
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
                    case 'deleted_citizen':
                        return 'danger';
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
                    case 'deleted_citizen':
                        return 'Deleted citizen';
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
                return 'Unknown';
            },
            formatDate(date) {
                return moment(date).format('LLL');
            }
        }
    };
</script>

<style lang="scss">
</style>
