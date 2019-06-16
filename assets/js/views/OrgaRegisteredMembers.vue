<template>
    <div class="">
        <b-nav tabs fill>
            <b-nav-item :active="activeTab == 'all_members'" @click="activeTab = 'all_members'">All members ({{ totalMembers + hiddenMembers }})</b-nav-item>
            <b-nav-item :active="activeTab == 'members_fleet_uploaded'" @click="activeTab = 'members_fleet_uploaded'">Members fleet uploaded ({{ countFleetUploadedMembers }})</b-nav-item>
            <b-nav-item :active="activeTab == 'members_registered'" @click="activeTab = 'members_registered'">Members registered ({{ countRegisteredMembers }})</b-nav-item>
            <b-nav-item :active="activeTab == 'members_not_registered'" @click="activeTab = 'members_not_registered'">Members not registered ({{ countNotRegisteredMembers }})</b-nav-item>
        </b-nav>
        <b-card style="max-height: 500px; overflow-y: auto;">
            <p class="mb-1 d-flex align-items-center align-content-center" v-for="member in filteredMembers" :key="member.infos.handle">
                <span class="registered-member-rank-icon mr-2"><i class="fas fa-star"></i><span class="registered-member-rank">{{ member.infos.rank }}</span></span>
                <b-badge class="mr-2" style="width: 6rem;" :variant="getBadgeVariant(member.status)">{{ formatStatus(member.status) }}</b-badge>
                {{ member.infos.nickname }}
                <template v-if="member.lastFleetUploadDate"><b-badge class="ml-2">last update the {{ formatDate(member.lastFleetUploadDate) }}</b-badge></template>
            </p>
            <p v-if="hiddenMembers > 0 && activeTab == 'all_members'" class="mb-1"><i>+ {{ hiddenMembers }} hidden members</i></p>
        </b-card>
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
                totalMembers: null,
                countFleetUploadedMembers: null,
                countRegisteredMembers: null,
                countNotRegisteredMembers: null,
                // page: 1,
            }
        },
        created() {
            axios.get(`/api/organization/${this.selectedSid}/members-registered`, {
                // params: {page: this.page},
            }).then(response => {
                this.hiddenMembers = response.data.countHiddenMembers;
                this.totalMembers = response.data.totalItems;
                this.members = response.data.members;
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
                // for (let member of response.data.members) {
                //     this.members.push(member);
                // }
                // ++this.page;
            }).catch(err => {
                console.error(err);
            });
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
