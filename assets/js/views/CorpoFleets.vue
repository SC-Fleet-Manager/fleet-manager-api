<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <nav class="mb-3 navbar navbar-light bg-light" v-if="!notEnoughRightsMessage">
                    <ul class="nav">
                        <b-dropdown
                            v-if="citizen != null && citizenOrgaInfo != null && organization != null"
                            id="select-orga"
                            class="js-select-orga nav-item mr-3"
                            :text="organization.name ? organization.name : 'No selected orga'"
                            variant="outline-primary"
                        >
                            <b-dropdown-item :active="organization.organizationSid == citizenOrga.organization.organizationSid" v-for="citizenOrga in citizen.organizations" :key="citizenOrga.organization.organizationSid" @click="changeSelectedOrga(citizenOrga.organization)">{{ citizenOrga.organization.name }}</b-dropdown-item>
                            <b-dropdown-item v-if="citizen.countRedactedOrganizations > 0" disabled>+{{ citizen.countRedactedOrganizations }} redacted organizations</b-dropdown-item>
                        </b-dropdown>
                        <b-button-group>
                            <b-button class="nav-item" :variant="menu == 'fleet' ? 'primary' : 'outline-primary'" @click="menu = 'fleet'">Fleet</b-button>
                            <b-button class="nav-item" :variant="menu == 'stats' ? 'primary' : 'outline-primary'" @click="menu = 'stats'">Statistics</b-button>
                            <b-button v-if="isAdmin" class="nav-item" :variant="menu == 'admin_panel' ? 'primary' : 'outline-primary'" @click="menu = 'admin_panel'">Admin panel</b-button>
                        </b-button-group>
                    </ul>
                </nav>
                <b-card v-if="menu == 'fleet'" class="js-organizations-fleets">
                    <b-row>
                        <b-col sm="10" md="8" lg="6" xl="6" class="mb-3" v-if="organization !== null">
                            <a :href="'https://robertsspaceindustries.com/orgs/'+organization.organizationSid" target="_blank"><img v-if="organization.avatarUrl" :src="organization.avatarUrl" alt="organization's logo" class="img-fluid" style="max-height: 8rem;" /></a>
                            <div class="d-inline-block align-top">
                                <h4><a :href="'https://robertsspaceindustries.com/orgs/'+organization.organizationSid" target="_blank">{{ organization.name }}</a></h4>
                                <div class="position-relative" v-if="citizen != null && citizenOrgaInfo != null">
                                    <i class="fas fa-star rank-icon" :class="{'rank-icon-active': (i <= citizenOrgaInfo.rank) }" v-for="i in 5"></i>
                                </div>
                                <p v-if="citizen != null && citizenOrgaInfo != null"><strong>{{ citizenOrgaInfo.rankName }}</strong></p>
                            </div>
                        </b-col>
                        <b-col col class="mb-3 text-right" v-if="!loadingOrgaFleet && !notEnoughRightsMessage && citizenOrgaInfo != null">
                            <b-dropdown variant="primary" class="mb-2">
                                <template slot="button-content"><i class="fas fa-cloud-download-alt"></i> Export fleet</template>
                                <b-dropdown-item download :disabled="selectedSid == null || shipFamilies.length == 0" :href="'/api/create-organization-fleet-file/'+selectedSid" ><i class="fas fa-file-code"></i> Export <strong>{{ selectedSid != null ? orgaFullname : 'N/A' }}</strong> fleet (.json)</b-dropdown-item>
                                <b-dropdown-item download :disabled="selectedSid == null || shipFamilies.length == 0" :href="'/api/export-orga-fleet/'+selectedSid"><i class="fas fa-file-csv"></i> Export <strong>{{ selectedSid != null ? orgaFullname : 'N/A' }}</strong> fleet (.csv)</b-dropdown-item>
                            </b-dropdown>
                            <!--<p><b>{{ orgaStats.countUploadedFleets }}</b> uploaded fleets for <b>{{ orgaStats.totalCitizen }}</b> members</p>-->
                        </b-col>
                    </b-row>
                    <b-row class="mb-3" v-if="!notEnoughRightsMessage && sid != null && ((citizen != null && citizenOrgaInfo != null) || (organization !== null && organization.publicChoice === 'public'))">
                        <b-col sm="6" md="6" lg="4" xl="2">
                            <v-select id="filters_input_ship_name" :reduce="item => item.id" v-model="filterShipGalaxyId" :options="filterOptionsShips" multiple @input="refreshOrganizationFleet(true)" placeholder="Filter by ship name"></v-select>
                        </b-col>
                        <b-col sm="6" md="6" lg="4" xl="2" v-if="isInSelectedOrganization">
                            <v-select id="filters_input_citizen_id" :reduce="item => item.id" v-model="filterCitizenId" :options="filterOptionsCitizens" multiple @input="refreshOrganizationFleet(true)" placeholder="Filter by citizen"></v-select>
                        </b-col>
                        <b-col sm="6" md="6" lg="4" xl="2">
                            <v-select id="filters_input_ship_size" :reduce="item => item.id" v-model="filterShipSize" :options="filterOptionsShipSize" multiple @input="refreshOrganizationFleet(true)" placeholder="Filter by ship size"></v-select>
                        </b-col>
                        <b-col sm="6" md="6" lg="4" xl="2">
                            <v-select id="filters_input_ship_status" :reduce="item => item.id" v-model="filterShipStatus" :options="filterOptionsShipStatus" @input="refreshOrganizationFleet(true)" placeholder="Filter by ship status"></v-select>
                        </b-col>
                    </b-row>
                    <b-row>
                        <!-- TODO : VERY UGLY THIS SHIT !! -->
                        <template v-if="notEnoughRightsMessage">
                            <b-col sm="12" md="12" lg="12" xl="12">
                                <b-alert show variant="danger" v-html="notEnoughRightsMessage"></b-alert>
                            </b-col>
                        </template>
                        <template v-else>
                            <template v-if="!loadingOrgaFleet && notFoundError">
                                <b-col sm="12" md="12" lg="12" xl="12">
                                    <b-alert show variant="danger">Sorry, this organization's fleet does not exist or is private. Try to <a href="/">login</a> to see it.</b-alert>
                                </b-col>
                            </template>
                            <template v-else>
                                <b-col v-if="shipFamilies.length === 0 && !loadingOrgaFleet">
                                    <b-alert show variant="warning">Sorry, no ships have been found.</b-alert>
                                </b-col>
                                <b-col col xl="12" lg="12" md="12" sm="12" xs="12" v-if="loadingOrgaFleet" class="text-center">
                                    <b-spinner variant="primary" style="width: 3rem; height: 3rem;"></b-spinner>
                                </b-col>
                            </template>
                        </template>
                        <template v-for="(shipFamily, index) in shipFamilies">
                            <ShipFamily :key="shipFamily.chassisId" :shipFamily="shipFamily" :index="index"></ShipFamily>
                            <ShipFamilyDetail
                                :index="index"
                                :totalShipFamilies="shipFamilies.length"
                                :breakpoint="actualBreakpoint"
                                v-if="
                                    (actualBreakpoint === 'xl' && (index % 6 === 5 || index === shipFamilies.length - 1))
                                    || (actualBreakpoint === 'lg' && (index % 4 === 3 || index === shipFamilies.length - 1))
                                    || (actualBreakpoint === 'md' && (index % 3 === 2 || index === shipFamilies.length - 1))
                                    || (actualBreakpoint === 'sm' && (index % 2 === 1 || index === shipFamilies.length - 1))
                                    || (actualBreakpoint === 'xs')
                                "
                                class="col-12"
                            ></ShipFamilyDetail>
                        </template>
                    </b-row>
                </b-card>
                <b-card v-if="menu == 'stats'">
                    <h4>Statistics of {{ organization.name }}</h4>
                    <OrgaStatistics :selectedSid="selectedSid"/>
                </b-card>
                <b-card v-if="menu == 'admin_panel'">
                    <CorpoFleetsAdmin :organization.sync="organization" :selectedSid="selectedSid" @changed="refreshOrganization(true)"></CorpoFleetsAdmin>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import vSelect from 'vue-select';
    import ShipFamilyDetail from './ShipFamilyDetail';
    import ShipFamily from './ShipFamily';
    import {createNamespacedHelpers} from 'vuex';
    import OrgaRegisteredMembers from "./OrgaRegisteredMembers";
    import OrganizationChanges from "./OrganizationChanges";
    import OrgaStatistics from "./OrgaStatistics";
    import CorpoFleetsAdmin from "./CorpoFleetsAdmin";

    const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('orga_fleet');
    const BREAKPOINTS = {xs: 0, sm: 576, md: 768, lg: 992, xl: 1200};

    /*
     * PUBLIC
     *      - logged
     *          - my orga : OK : see ships + filters + button export + orga selector + info orga
     *          - not my orga : OK : see ships + filters only + info orga global
     *      - not logged : OK : see ships + filters only + info orga global
     * PRIVATE
     *      - logged
     *          - my orga : OK : see ships + filters + button export + orga selector + info orga
     *          - not my orga : NOT OK : error message orga private
     *      - not logged : NOT OK : error message orga private
     */

    export default {
        name: 'organizations-fleets',
        props: ['sid'],
        components: {OrgaStatistics, OrgaRegisteredMembers, OrganizationChanges, vSelect, ShipFamily, ShipFamilyDetail, CorpoFleetsAdmin},
        data() {
            return {
                menu: 'fleet',
                organization: null,
                orgaStats: {},
                fleetPolicyErrors: false,
                fleetPolicyErrorMessages: null,
                notEnoughRightsMessage: null,
                notFoundError: false,
                savingPreferences: false,
                citizen: null,
                orgaFleetAdmins: [],
                shipFamilies: [], // families of ships (e.g. "Aurora" for MR, LX, etc.) that have the selected orga (no displayed if no orga members have this family).
                actualBreakpoint: 'xs',
                refreshedSid: null,
                loadingOrgaFleet: false,
                filterOptionsCitizens: [],
                filterOptionsShips: [],
                filterOptionsShipSize: [
                    {'id': '', 'label': 'N/A'},
                    {'id': 'vehicle', 'label': 'Vehicle'},
                    {'id': 'snub', 'label': 'Snub'},
                    {'id': 'small', 'label': 'Small'},
                    {'id': 'medium', 'label': 'Medium'},
                    {'id': 'large', 'label': 'Large'},
                    {'id': 'capital', 'label': 'Capital'},
                ],
                filterOptionsShipStatus: [
                    {'id': 'ready', 'label': 'Flight ready'},
                    {'id': 'not_ready', 'label': 'In concept'},
                ],
                refreshingMemberList: false,
            };
        },
        created() {
            if (this.sid !== null) {
                this.updateSid(this.sid);
                this.refreshProfile();
                this.refreshOrganization();
            }
        },
        mounted() {
            window.addEventListener('resize', this.refreshBreakpoint);
            this.refreshBreakpoint();
        },
        beforeRouteLeave (to, from, next) {
            this.filterShipGalaxyId = [];
            this.filterCitizenId = [];
            this.filterShipSize = [];
            this.filterShipStatus = null;
            next();
        },
        computed: {
            isInSelectedOrganization() {
                if (this.citizen === null) {
                    return false;
                }
                for (let orga of this.citizen.organizations) {
                    if (orga.organization.organizationSid === this.selectedSid) {
                        return true;
                    }
                }
                return false;
            },
            citizenOrgaInfo() {
                if (this.citizen === null) {
                    return null;
                }
                for (let orgaInfo of this.citizen.organizations) {
                    if (orgaInfo.organization.organizationSid === this.selectedSid) {
                        return orgaInfo;
                    }
                }
                return null;
            },
            filterShipGalaxyId: {
                get() {
                    return this.$store.state.orga_fleet.filterShipGalaxyId;
                },
                set(value) {
                    this.$store.state.orga_fleet.filterShipGalaxyId = value;
                }
            },
            filterCitizenId: {
                get() {
                    return this.$store.state.orga_fleet.filterCitizenId;
                },
                set(value) {
                    this.$store.state.orga_fleet.filterCitizenId = value;
                }
            },
            filterShipSize: {
                get() {
                    return this.$store.state.orga_fleet.filterShipSize;
                },
                set(value) {
                    this.$store.state.orga_fleet.filterShipSize = value;
                }
            },
            filterShipStatus: {
                get() {
                    return this.$store.state.orga_fleet.filterShipStatus;
                },
                set(value) {
                    this.$store.state.orga_fleet.filterShipStatus = value;
                }
            },
            ...mapGetters({
                selectedSid: 'selectedSid',
                selectedShipFamily: 'selectedShipFamily',
                selectedShipVariants: 'selectedShipVariants',
            }),
            orgaFullname() {
                if (this.organization !== null && this.organization.organizationSid === this.selectedSid && this.organization.name !== null) {
                    return this.organization.name;
                }

                return this.selectedSid;
            },
            isAdmin() {
                if (this.citizen === null) {
                    return false;
                }
                for (let admin of this.orgaFleetAdmins) {
                    if (admin.id === this.citizen.id) {
                        return true;
                    }
                }

                return false;
            },
        },
        watch: {
            organization(orga) {
                if (orga.publicChoice === 'public' || this.citizen !== null) {
                    this.refreshOrganizationFleet();
                }
            },
            sid(value) {
                if (value) {
                    this.updateSid(value);
                }
            },
            selectedSid() {
                this.notEnoughRightsMessage = null;
                this.refreshOrganizationFleet();
                this.refreshAdmins();
            },
            selectedShipVariants(shipVariants) {
                if (this.citizen === null) {
                    // public orga : we don't display the members
                    return;
                }
                for (let ship of shipVariants) {
                    this.loadShipVariantUsers({ ship, page: 1, clean: true });
                }
            },
            citizen(newCitizen) {
                if (newCitizen !== null || (this.organization !== null && this.organization.publicChoice === 'public')) {
                    this.refreshOrganizationFleet();
                }
            },
        },
        methods: {
            ...mapActions(['loadShipVariantUsers', 'selectShipFamily']),
            ...mapMutations(['updateSid']),
            selectSid(value) {
                if (value !== this.selectedSid) {
                    this.$router.replace({path: `/organization-fleet/${value}`});
                }
                this.updateSid(value);
            },
            changeSelectedOrga(orga) {
                this.menu = 'fleet';
                this.selectSid(orga.organizationSid);
            },
            refreshOrganization(force) {
                if (this.organization === null || force) {
                    axios.get(`/api/organization/${this.sid}`).then(response => {
                        this.organization = response.data;
                    }).catch(err => {
                        if (err.response.status === 401) {
                            // not connected
                            return;
                        }
                        if (err.response.status === 404) {
                            // not exist
                            return;
                        }
                    });
                } else if (this.citizen !== null) {
                    for (let citizenOrga of this.citizen.organizations) {
                        if (citizenOrga.organization.organizationSid === this.selectedSid) {
                            this.organization = citizenOrga.organization;
                            break;
                        }
                    }
                }
            },
            async refreshProfile() {
                this.refreshAdmins();

                this.citizen = this.$store.getters.citizen;
                if (this.citizen === null) {
                    try {
                        const response = await axios.get('/api/profile');
                        this.$store.commit('updateProfile', response.data.citizen);
                        this.citizen = response.data.citizen;
                    } catch (err) {
                        if (err.response.status === 401) {
                            // not connected
                            return;
                        }
                        if (err.response.data.errorMessage) {
                            this.$toastr.e(err.response.data.errorMessage);
                        }
                        return;
                    }
                }
                this.citizen.organizations.sort((orga1, orga2) => {
                    if (this.citizen.mainOrga) {
                        if (this.citizen.mainOrga.id === orga1.id) {
                            return -1;
                        } else if (this.citizen.mainOrga.id === orga2.id) {
                            return 1;
                        }
                    }
                    return orga1.organization.name > orga2.organization.name ? 1 : -1;
                });
                this.refreshOrganization();
            },
            refreshAdmins() {
                axios.get(`/api/fleet/orga-fleets/${this.selectedSid}/admins`).then(response => {
                    this.orgaFleetAdmins = response.data;
                }).catch(err => {
                    if (err.response.status === 401) {
                        // not connected
                        return;
                    }
                    if (err.response.status === 404) {
                        // not exist
                        return;
                    }
                    if (err.response.status === 400 && err.response.data.error === 'no_citizen_created') {
                        // no citizen created
                        return;
                    }
                    if (err.response.status === 403 && err.response.data.error.startsWith('not_enough_rights')) {
                        this.notEnoughRightsMessage = err.response.data.errorMessage;
                    }
                });
            },
            refreshOrganizationFleet(force) {
                if (!force && this.refreshedSid === this.selectedSid) {
                    // not multiple refresh
                    return;
                }
                if (this.refreshedSid !== this.selectedSid) {
                    this.refreshOrganization();
                    this.refreshFiltersOptions();
                }
                this.refreshedSid = this.selectedSid;
                this.shipFamilies = [];

                this.loadingOrgaFleet = true;
                this.notFoundError = false;
                axios.get(`/api/fleet/orga-fleets/${this.selectedSid}`, {
                    params: {
                        'filters[shipGalaxyIds]': this.filterShipGalaxyId,
                        'filters[citizenIds]': this.filterCitizenId,
                        'filters[shipSizes]': this.filterShipSize,
                        'filters[shipStatus]': this.filterShipStatus,
                    },
                }).then(response => {
                    this.selectShipFamily({index: null, shipFamily: null});
                    this.shipFamilies = response.data;
                }).catch(err => {
                    this.notFoundError = true;
                    if (err.response.status === 401) {
                        // not connected
                        return;
                    }
                    if (err.response.status === 404) {
                        // not exist
                        return;
                    }
                    if (err.response.status === 400 && err.response.data.error === 'no_citizen_created') {
                        // no citizen created
                        return;
                    } else if (err.response.status === 400 && err.response.data.error === 'unable_request_ships_infos_provider') {
                        this.notEnoughRightsMessage = err.response.data.errorMessage;
                    }
                    if (err.response.status === 403 && err.response.data.error.startsWith('not_enough_rights')) {
                        this.notEnoughRightsMessage = err.response.data.errorMessage;
                    }
                }).then(_ => {
                    this.loadingOrgaFleet = false;
                });
            },
            refreshFiltersOptions() {
                axios.get(`/api/organization/${this.selectedSid}/citizens`).then(response => {
                    this.filterOptionsCitizens = response.data;
                }).catch(err => {
                    if (err.response.status === 401) {
                        // not connected
                        return;
                    }
                    if (err.response.status === 404) {
                        // not exist
                        return;
                    }
                    if (err.response.status === 400 && err.response.data.error === 'no_citizen_created') {
                        // no citizen created
                        return;
                    }
                    if (err.response.status === 403 && err.response.data.error.startsWith('not_enough_rights')) {
                        this.notEnoughRightsMessage = err.response.data.errorMessage;
                    }
                });
                axios.get(`/api/organization/${this.selectedSid}/ships`).then(response => {
                    this.filterOptionsShips = response.data;
                }).catch(err => {
                    if (err.response.status === 401) {
                        // not connected
                        return;
                    }
                    if (err.response.status === 404) {
                        // not exist
                        return;
                    }
                    if (err.response.status === 400 && err.response.data.error === 'no_citizen_created') {
                        // no citizen created
                        return;
                    }
                    if (err.response.status === 403 && err.response.data.error.startsWith('not_enough_rights')) {
                        this.notEnoughRightsMessage = err.response.data.errorMessage;
                    }
                });
            },
            refreshBreakpoint() {
                const width = window.innerWidth;

                let prevBpName = null;
                for (let bpName in BREAKPOINTS) {
                    if (prevBpName && width >= BREAKPOINTS[prevBpName] && width < BREAKPOINTS[bpName]) {
                        this.actualBreakpoint = prevBpName;
                        break;
                    }
                    prevBpName = bpName;
                }
                if (prevBpName === 'xl') {
                    this.actualBreakpoint = 'xl';
                }
            },
        }
    }
</script>

<style lang="scss">
    @import '../../css/vendors/variables';
    @import '~vue-select/src/scss/vue-select';

    .rank-icon {
        display: inline-block;
        font-size: 2rem;
        margin-right: 2px;
        color: #d8d8d8;

        &.rank-icon-active {
            color: $primary;
        }
    }

    .vs__dropdown-option--selected {
        background: #d8d8d8;
    }
</style>
