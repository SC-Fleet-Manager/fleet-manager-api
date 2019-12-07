<template>
    <div>
        <h4>Admin panel of {{ organization.name }}</h4>
        <b-alert variant="danger" :show="fleetPolicyErrors" v-html="fleetPolicyErrorMessages"></b-alert>
        <b-row>
            <b-col lg="6">
                <b-form-group label="Fleet policy">
                    <b-form-radio v-model="orgaPublicChoice" @change="saveOrgaPublicChoice" :disabled="savingPreferences" :name="'orga-public-choice-' + organization.organizationSid" value="private">Members only <i class="fas fa-info-circle" v-b-tooltip.hover title="Only the orga's members can see the orga's fleet."></i></b-form-radio>
                    <b-form-radio v-model="orgaPublicChoice" @change="saveOrgaPublicChoice" :disabled="savingPreferences" :name="'orga-public-choice-' + organization.organizationSid" value="admin">Admin only <i class="fas fa-info-circle" v-b-tooltip.hover title="Only the highest ranks (admins) of the orga can see the orga's fleet."></i></b-form-radio>
                    <b-form-radio v-model="orgaPublicChoice" @change="saveOrgaPublicChoice" :disabled="savingPreferences" :name="'orga-public-choice-' + organization.organizationSid" value="public">Public <i class="fas fa-info-circle" v-b-tooltip.hover title="Everyone can see the orga's fleet."></i></b-form-radio>
                </b-form-group>
                <OrganizationChanges :selectedSid="selectedSid" ref="orgaChanges"/>
            </b-col>
            <b-col lg="6" >
                <b-row>
                    <b-col class="mb-2" md="6" lg="12" xl="6">
                        <b-button :disabled="refreshingMemberList" @click="refreshMemberList" variant="secondary"><i class="fas fa-sync-alt" :class="{'fa-spin': refreshingMemberList}"></i> Refresh the members list</b-button>
                    </b-col>
                    <b-col md="6" lg="12" xl="6">
                        <div class="text-right"><b-button variant="primary" class="mb-3" download :disabled="selectedSid == null" :href="'/api/organization/export-orga-members/'+selectedSid"><i class="fas fa-file-csv"></i> Export <strong>{{ selectedSid != null ? orgaFullname : 'N/A' }}</strong> members (.csv)</b-button></div>
                    </b-col>
                </b-row>
                <OrgaRegisteredMembers :selectedSid="selectedSid" ref="orgaRegisteredMembers" @profileRefreshed="refreshLastChanges()"/>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import OrgaRegisteredMembers from "./OrgaRegisteredMembers";
    import OrganizationChanges from "./OrganizationChanges";

    export default {
        name: 'organizations-fleets-admin',
        props: ['organization', 'selectedSid'],
        components: {OrgaRegisteredMembers, OrganizationChanges},
        data() {
            return {
                orgaPublicChoice: null,
                fleetPolicyErrors: false,
                fleetPolicyErrorMessages: null,
                savingPreferences: false,
                refreshingMemberList: false,
            };
        },
        created() {
            this.orgaPublicChoice = this.organization.publicChoice;
        },
        computed: {
            orgaFullname() {
                if (this.organization !== null && this.organization.organizationSid === this.selectedSid && this.organization.name !== null) {
                    return this.organization.name;
                }

                return this.selectedSid;
            },
        },
        watch: {
            organization() {
                this.orgaPublicChoice = this.organization.publicChoice;
            },
        },
        methods: {
            refreshLastChanges() {
                this.$refs.orgaChanges.retrieveChanges();
            },
            savePreferences() {
                this.savingPreferences = true;
                this.fleetPolicyErrors = false;
                axios.post(`/api/organization/${this.organization.organizationSid}/save-preferences`, {
                   publicChoice: this.orgaPublicChoice,
                }).then(response => {
                    this.$emit('changed');
                    this.refreshLastChanges();
                    toastr.success('Changes saved');
                }).catch(err => {
                    if (err.response.data.errorMessage) {
                        this.fleetPolicyErrorMessages = err.response.data.errorMessage;
                    } else {
                        toastr.error('An error has occurred. Please try again later.');
                    }
                    this.fleetPolicyErrors = true;
                }).then(_ => {
                    this.savingPreferences = false;
                });
            },
            saveOrgaPublicChoice(value) {
                this.orgaPublicChoice = value;
                this.savePreferences();
            },
            refreshMemberList() {
                this.refreshingMemberList = true;
                axios.post(`/api/organization/${this.organization.organizationSid}/refresh-orga`).then(response => {
                    toastr.success('The members list has been refresh.');
                    this.$refs.orgaRegisteredMembers.refreshMemberList(response.data);
                }).catch(err => {
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    } else {
                        toastr.error('An error has occurred. Please try again later.');
                    }
                }).then(_ => {
                    this.refreshingMemberList = false;
                });
            },
        }
    }
</script>
