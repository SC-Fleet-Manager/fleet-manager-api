<template>
    <div>
        <b-row>
            <b-col lg="12" xl="12">
                <b-nav tabs fill>
                    <b-nav-item :active="activeTab == 'all_members'" @click="activeTab = 'all_members'">All members ({{ totalMembers + hiddenMembers }})</b-nav-item>
                    <b-nav-item :active="activeTab == 'members_fleet_uploaded'" @click="activeTab = 'members_fleet_uploaded'">Members fleet uploaded ({{ countFleetUploadedMembers }})</b-nav-item>
                    <b-nav-item :active="activeTab == 'members_registered'" @click="activeTab = 'members_registered'">Members registered ({{ countRegisteredMembers }})</b-nav-item>
                    <b-nav-item :active="activeTab == 'members_not_registered'" @click="activeTab = 'members_not_registered'">Members not registered ({{ countNotRegisteredMembers }})</b-nav-item>
                </b-nav>
                <b-card style="max-height: 500px; overflow-y: auto;">
                    <b-alert variant="warning" :show="membersListWarningMessage != null">{{ membersListWarningMessage }}</b-alert>
                    <p class="mb-1 d-flex align-items-center align-content-center" v-for="member in filteredMembers" :key="member.infos.handle">
                        <b-button :disabled="refreshingProfile[member.infos.handle]" @click="refreshProfile(member.infos.handle)" class="mr-2" :class="{'invisible': member.status == 'not_registered'}" variant="secondary" size="sm" :title="'Force refresh '+member.infos.handle"><i class="fas fa-sync-alt" :class="{'fa-spin': refreshingProfile[member.infos.handle]}"></i></b-button>
                        <span class="registered-member-rank-icon mr-2"><i class="fas fa-star"></i><span class="registered-member-rank">{{ member.infos.rank }}</span></span>
                        <b-badge class="mr-2" style="width: 6rem;" :variant="getBadgeVariant(member.status)">{{ formatStatus(member.status) }}</b-badge>
                        {{ member.infos.handle }}
                        <template v-if="member.lastFleetUploadDate"><b-badge class="ml-2">last update the {{ formatDate(member.lastFleetUploadDate) }}</b-badge></template>
                    </p>
                    <p v-if="hiddenMembers > 0 && activeTab == 'all_members'" class="mb-1"><i>+ {{ hiddenMembers }} hidden members</i></p>
                </b-card>
            </b-col>
            <!--<b-col lg="12" xl="12">
                <h4>All linked members</h4>
                <b-alert variant="info" :show="true">
                    Useful when a member has left the orga but linked in Fleet Manager.<br/>
                    Feel free to refresh his profile by clicking next to his handle.
                </b-alert>
                <b-card style="max-height: 440px; overflow-y: auto;">
                    <p class="mb-1 d-flex align-items-center align-content-center" v-for="citizen in associatedCitizens" :key="citizen.actualHandle.handle">
                        <b-button :disabled="refreshingProfile[citizen.actualHandle.handle]" @click="refreshProfile(citizen.actualHandle.handle)" class="mr-2" variant="secondary" size="sm" :title="'Force refresh '+citizen.actualHandle.handle"><i class="fas fa-sync-alt" :class="{'fa-spin': refreshingProfile[citizen.actualHandle.handle]}"></i></b-button>
                        <b-button @click="showConfirmModal(citizen)" :disabled="refreshingProfile[citizen.actualHandle.handle]" class="mr-2" variant="danger" size="sm" :title="'Remove the member '+citizen.actualHandle.handle"><i class="fas fa-trash-alt"></i></b-button>
                        {{ citizen.actualHandle.handle }}
                    </p>
                </b-card>
            </b-col>-->
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import moment from 'moment-timezone';

    export default {
        name: 'orga-registered-members',
        components: {},
        props: ['selectedSid'],
        data() {
            return {
                activeTab: 'all_members',
                members: [],
                hiddenMembers: null,
                totalMembers: 0,
                countFleetUploadedMembers: 0,
                countRegisteredMembers: 0,
                countNotRegisteredMembers: 0,
                // page: 1,
                refreshingProfile: {},
                membersListWarningMessage: null,
                associatedCitizens: [],
            }
        },
        created() {
            this.membersListWarningMessage = null;
            axios.get(`/api/organization/${this.selectedSid}/members-registered`, {
                // params: {page: this.page},
            }).then(response => {
                this.refreshMemberList(response.data);
            }).catch(err => {
                if (err.response.data.error === 'orga_too_big') {
                    this.membersListWarningMessage = err.response.data.errorMessage;
                } else if (err.response.data.errorMessage) {
                    this.$toastr.e(err.response.data.errorMessage);
                } else {
                    this.$toastr.e('An error has occurred when retrieving members list. Please try again later.');
                }
            });
            // this.refreshAssociatedCitizens();
        },
        computed: {
            filteredMembers() {
                let res = [];
                switch (this.activeTab) {
                    case 'all_members':
                        return this.members;
                    case 'members_fleet_uploaded':
                        for (let member of this.members) {
                            if (member.status === 'fleet_uploaded') {
                                res.push(member);
                            }
                        }
                        break;
                    case 'members_registered':
                        for (let member of this.members) {
                            if (member.status === 'registered') {
                                res.push(member);
                            }
                        }
                        break;
                    case 'members_not_registered':
                        for (let member of this.members) {
                            if (member.status === 'not_registered') {
                                res.push(member);
                            }
                        }
                        break;
                }
                return res;
            },
        },
        methods: {
            showConfirmModal(citizenToRemove) {
                this.$bvModal.msgBoxConfirm(`Do you really want to remove ${citizenToRemove.actualHandle.handle}?`, {
                    title: 'Confirm',
                    size: 'md',
                    okVariant: 'danger',
                    okTitle: 'Delete',
                    cancelTitle: 'Cancel',
                    hideHeaderClose: false,
                    centered: true
                })
                .then(value => {
                    if (value) {
                        this.deleteMember(citizenToRemove);
                    }
                });
            },
            refreshAssociatedCitizens() {
                axios.get(`/api/organization/${this.selectedSid}/associated-citizens`, {
                    // params: {page: this.page},
                }).then(response => {
                    this.associatedCitizens = response.data;
                }).catch(err => {
                    if (err.response.data.errorMessage) {
                        this.$toastr.e(err.response.data.errorMessage);
                    } else {
                        this.$toastr.e('An error has occurred when retrieving associated citizens. Please try again later.');
                    }
                });
            },
            refreshMemberList(memberListData) {
                this.hiddenMembers = memberListData.countHiddenMembers;
                this.totalMembers = memberListData.totalItems;
                this.members = memberListData.members;
                this.countNotRegisteredMembers = 0;
                this.countRegisteredMembers = 0;
                this.countFleetUploadedMembers = 0;
                for (let member of this.members) {
                    switch (member.status) {
                        case 'not_registered':
                            ++this.countNotRegisteredMembers; break;
                        case 'registered':
                            ++this.countRegisteredMembers; break;
                        case 'fleet_uploaded':
                            ++this.countFleetUploadedMembers; break;
                    }
                }
                // for (let member of memberListData.members) {
                //     this.members.push(member);
                // }
                // ++this.page;
            },
            deleteMember(citizen) {
                this.$set(this.refreshingProfile, citizen.actualHandle.handle, true);
                axios.post(`/api/organization/${this.selectedSid}/delete-member/${citizen.id}`).then(response => {
                    // this.refreshAssociatedCitizens();
                    this.$emit('profileRefreshed', citizen.actualHandle.handle);
                    this.$toastr.s(`${citizen.actualHandle.handle} has been successfully removed.`);
                }).catch(err => {
                    if (err.response.data.errorMessage) {
                        this.$toastr.e(err.response.data.errorMessage);
                    }
                }).then(_ => {
                    this.$set(this.refreshingProfile, citizen.actualHandle.handle, false);
                });
            },
            refreshProfile(handle) {
                this.$set(this.refreshingProfile, handle, true);
                axios.post(`/api/organization/${this.selectedSid}/refresh-member/${handle}`).then(response => {
                    this.$emit('profileRefreshed', handle);
                    this.$toastr.s(`The RSI public profile of ${handle} has been successfully refreshed.`);
                }).catch(err => {
                    if (err.response.data.errorMessage) {
                        this.$toastr.e(err.response.data.errorMessage);
                    }
                }).then(_ => {
                    this.$set(this.refreshingProfile, handle, false);
                });
            },
            formatStatus(status) {
                switch (status) {
                    case 'not_registered':
                        return 'Not registered';
                    case 'registered':
                        return 'Registered';
                    case 'fleet_uploaded':
                        return 'Fleet uploaded';
                }
                return '';
            },
            getBadgeVariant(status) {
                switch (status) {
                    case 'not_registered':
                        return 'danger';
                    case 'registered':
                        return 'info';
                    case 'fleet_uploaded':
                        return 'success';
                }
                return 'secondary';
            },
            formatDate(date) {
                return moment(date).format('LL');
            }
        }
    };
</script>

<style lang="scss">
    @import '../../css/vendors/variables';

    .registered-member-rank-icon {
        position: relative;
        .fas {
            font-size: 1.8rem;
            color: $primary;
        }
        .registered-member-rank {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 1rem;
        }
    }
</style>
