<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <nav class="mb-3 navbar navbar-light bg-light" v-if="citizen != null && citizenOrgaInfo != null">
                    <ul class="nav">
                        <b-dropdown
                            v-if="citizen.organizations.length >= 2 || citizen.countRedactedOrganizations > 0"
                            id="select-orga"
                            class="js-select-orga nav-item"
                            split
                            :split-variant="menu == 'fleet' ? 'primary' : 'outline-primary'"
                            :text="organization.name ? organization.name : 'No selected orga'"
                            variant="outline-primary"
                            @click="menu = 'fleet'"
                        >
                            <b-dropdown-item :active="organization.organizationSid == citizenOrga.organization.organizationSid" v-for="citizenOrga in citizen.organizations" :key="citizenOrga.organization.organizationSid" @click="changeSelectedOrga(citizenOrga)">{{ citizenOrga.organization.name }}</b-dropdown-item>
                            <b-dropdown-item v-if="citizen.countRedactedOrganizations > 0" disabled>+{{ citizen.countRedactedOrganizations }} redacted organizations</b-dropdown-item>
                        </b-dropdown>
                        <b-button v-else id="select-orga" class="nav-item" variant="primary">{{ organization.name ? organization.name : 'No selected orga' }}</b-button>
                        <b-button v-if="isAdmin" class="nav-item ml-3" :variant="menu == 'admin_panel' ? 'primary' : 'outline-primary'" @click="menu = 'admin_panel'">Admin panel</b-button>
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
                        <b-col col class="mb-3 text-right" v-if="!notEnoughRightsMessage && citizen != null && citizenOrgaInfo != null">
                            <b-dropdown variant="primary">
                                <template slot="button-content"><i class="fas fa-cloud-download-alt"></i> Export fleet</template>
                                <b-dropdown-item download :disabled="selectedSid == null || shipFamilies.length == 0" :href="'/api/create-organization-fleet-file/'+selectedSid" ><i class="fas fa-file-code"></i> Export <strong>{{ selectedSid != null ? orgaFullname : 'N/A' }}</strong> fleet (.json)</b-dropdown-item>
                                <b-dropdown-item download :disabled="selectedSid == null || shipFamilies.length == 0" :href="'/api/export-orga-fleet/'+selectedSid"><i class="fas fa-file-csv"></i> Export <strong>{{ selectedSid != null ? orgaFullname : 'N/A' }}</strong> fleet (.csv)</b-dropdown-item>
                            </b-dropdown>
                        </b-col>
                    </b-row>
                    <b-row class="mb-3" v-if="!notEnoughRightsMessage && sid != null && ((citizen != null && citizenOrgaInfo != null) || (organization !== null && organization.publicChoice === 'public'))">
                        <b-col sm="6" md="6" lg="4" xl="2">
                            <v-select id="filters_input_ship_name" :reduce="item => item.id" v-model="filterShipName" :options="filterOptionsShips" multiple @input="refreshOrganizationFleet(true)" placeholder="Filter by ship name"></v-select>
                        </b-col>
                        <b-col sm="6" md="6" lg="4" xl="2">
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
                            <b-col>
                                <b-alert show variant="danger" v-html="notEnoughRightsMessage"></b-alert>
                            </b-col>
                        </template>
                        <template v-else>
                            <template v-if="!loadingOrgaFleet && ((citizen == null && (organization === null || organization.publicChoice !== 'public'))
                                            || (citizen != null && citizenOrgaInfo == null && (organization == null || organization.publicChoice !== 'public')))">
                                <b-col>
                                    <b-alert show variant="danger">Sorry, this organization's fleet does not exist or is private. Try to <a href="/">login</a> to see it.</b-alert>
                                </b-col>
                            </template>
                            <template v-else>
                                <b-col v-if="shipFamilies.length === 0 && !loadingOrgaFleet">
                                    <b-alert show variant="warning">Sorry, no ships have been found.</b-alert>
                                </b-col>
                                <b-col col xl="12" lg="12" md="12" sm="12" xs="12" v-if="loadingOrgaFleet" class="text-center">
                                    <i class="fas fa-circle-notch fa-spin fa-5x" style="color:#ccc"></i>
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
                <b-card v-if="menu == 'admin_panel'">
                    <b-alert variant="danger" :show="fleetPolicyErrors" v-html="fleetPolicyErrorMessages"></b-alert>
                    <b-form-group :label="'Fleet policy of '+organization.name">
                        <b-form-radio v-model="orgaPublicChoice" @change="saveOrgaPublicChoice" :disabled="savingPreferences" :name="'orga-public-choice-' + organization.organizationSid" value="private">Members only</b-form-radio>
                        <b-form-radio v-model="orgaPublicChoice" @change="saveOrgaPublicChoice" :disabled="savingPreferences" :name="'orga-public-choice-' + organization.organizationSid" value="admin">Admin only</b-form-radio>
                        <b-form-radio v-model="orgaPublicChoice" @change="saveOrgaPublicChoice" :disabled="savingPreferences" :name="'orga-public-choice-' + organization.organizationSid" value="public">Public</b-form-radio>
                    </b-form-group>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import vSelect from 'vue-select';
    import ShipFamilyDetail from './ShipFamilyDetail';
    import ShipFamily from './ShipFamily';
    import { createNamespacedHelpers } from 'vuex';

    const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('orga_fleet');
    const BREAKPOINTS = {xs: 0, sm: 576, md: 768, lg: 992, xl: 1200};
    const MENU_FLEET = 'fleet';
    const MENU_ADMIN_PANEL = 'admin_panel';

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
        components: {vSelect, ShipFamily, ShipFamilyDetail},
        data() {
            return {
                menu: MENU_FLEET,
                organization: null,
                orgaPublicChoice: null,
                fleetPolicyErrors: false,
                fleetPolicyErrorMessages: null,
                notEnoughRightsMessage: null,
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
            this.filterShipName = [];
            this.filterCitizenId = [];
            this.filterShipSize = [];
            this.filterShipStatus = null;
            next();
        },
        computed: {
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
            filterShipName: {
                get() {
                    return this.$store.state.orga_fleet.filterShipName;
                },
                set(value) {
                    this.$store.state.orga_fleet.filterShipName = value;
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
                for (let ship of shipVariants) {
                    this.loadShipVariantUsers({ ship, page: 1 });
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
                this.$router.replace({ path: `/organization-fleet/${value}` });
                this.updateSid(value);
            },
            changeSelectedOrga(orga) {
                this.menu = MENU_FLEET;
                this.selectSid(orga.organization.organizationSid);
            },
            savePreferences() {
                this.savingPreferences = true;
                this.fleetPolicyErrors = false;
                axios.post(`/api/organization/${this.organization.organizationSid}/save-preferences`, {
                   publicChoice: this.orgaPublicChoice,
                }).then(response => {
                    toastr.success('Changes saved');
                }).catch(err => {
                    // this.checkAuth(err.response);
                    if (err.response.data.errorMessage) {
                        this.fleetPolicyErrorMessages = err.response.data.errorMessage;
                    } else {
                        toastr.error('An error has occurred. Please retry more later.');
                    }
                    this.fleetPolicyErrors = true;
                    console.error(err);
                }).then(_ => {
                    this.savingPreferences = false;
                });
            },
            saveOrgaPublicChoice(value) {
                this.orgaPublicChoice = value;
                this.savePreferences();
            },
            refreshOrganization() {
                if (this.citizen === null) {
                    axios.get(`/api/organization/${this.sid}`).then(response => {
                        this.organization = response.data;
                        this.orgaPublicChoice = this.organization.publicChoice;
                    }).catch(err => {
                        if (err.response.status === 401) {
                            // not connected
                            return;
                        }
                        if (err.response.status === 404) {
                            // not exist
                            return;
                        }
                        console.error(err);
                    });
                    return;
                }
                for (let citizenOrga of this.citizen.organizations) {
                    if (citizenOrga.organization.organizationSid === this.selectedSid) {
                        this.organization = citizenOrga.organization;
                        break;
                    }
                }
            },
            refreshProfile() {
                axios.get('/api/profile/').then(response => {
                    this.citizen = response.data.citizen;
                    this.refreshOrganization();
                }).catch(err => {
                    if (err.response.status === 401) {
                        // not connected
                        return;
                    }
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    }
                    console.error(err);
                });

                this.refreshAdmins();
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
                    console.error(err);
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
                axios.get(`/api/fleet/orga-fleets/${this.selectedSid}`, {
                    params: {
                        'filters[shipNames]': this.filterShipName,
                        'filters[citizenIds]': this.filterCitizenId,
                        'filters[shipSizes]': this.filterShipSize,
                        'filters[shipStatus]': this.filterShipStatus,
                    },
                }).then(response => {
                    this.selectShipFamily({index: null, shipFamily: null});
                    this.shipFamilies = response.data;
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
                    console.error(err);
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
                    console.error(err);
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
                    console.error(err);
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
