<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card :header="citizen != null && organizations[sid] ? 'Your organizations\' fleets' : orgaFullname +' fleet'" class="js-organizations-fleets">
                    <b-row>
                        <b-col v-if="citizen != null && hasOrganization(sid)" xl="3" lg="3" md="4" sm="12" class="mb-3">
                            <b-form>
                                <b-form-group label="Select an organization" label-for="select-orga" class="js-select-orga">
                                    <b-form-select id="select-orga" :value="selectedSid" @change="selectSid">
                                        <option v-for="orga in citizen.organisations" :key="orga" :value="orga">{{ organizations[orga] ? organizations[orga].fullname : orga }}</option>
                                    </b-form-select>
                                </b-form-group>
                            </b-form>
                        </b-col>
                        <b-col xs="12" sm="12" md="6" class="mb-3" v-if="organization !== null">
                            <a :href="'https://robertsspaceindustries.com/orgs/'+organization.organizationSid" target="_blank"><img v-if="organization.avatarUrl" :src="organization.avatarUrl" alt="organization's logo" class="img-fluid" style="max-height: 8rem;" /></a>
                            <div class="d-inline-block align-top">
                                <h4><a :href="'https://robertsspaceindustries.com/orgs/'+organization.organizationSid" target="_blank">{{ organization.name }}</a></h4>
                                <div class="position-relative" v-if="citizen != null && citizenOrgaInfo != null">
                                    <i class="fas fa-star rank-icon" :class="{'rank-icon-active': (i <= citizenOrgaInfo.rank) }" v-for="i in 5"></i>
                                </div>
                                <p v-if="citizen != null && citizenOrgaInfo != null"><strong>{{ citizenOrgaInfo.rankName }}</strong></p>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row v-if="citizen != null && organizations[sid]">
                        <b-col col xl="2" lg="3" md="4" class="mb-3">
                            <b-dropdown variant="primary">
                                <template slot="button-content"><i class="fas fa-cloud-download-alt"></i> Export fleet</template>
                                <b-dropdown-item download :disabled="selectedSid == null || shipFamilies.length == 0" :href="'/api/create-organization-fleet-file/'+selectedSid" ><i class="fas fa-file-code"></i> Export <strong>{{ selectedSid != null ? orgaFullname : 'N/A' }}</strong> fleet (.json)</b-dropdown-item>
                                <b-dropdown-item download :disabled="selectedSid == null || shipFamilies.length == 0" :href="'/api/export-orga-fleet/'+selectedSid"><i class="fas fa-file-csv"></i> Export <strong>{{ selectedSid != null ? orgaFullname : 'N/A' }}</strong> fleet (.csv)</b-dropdown-item>
                            </b-dropdown>
                        </b-col>
                    </b-row>
                    <b-row class="mb-3" v-if="sid != null && ((citizen != null && organizations[sid]) || (organization !== null && organization.publicChoice === 'public'))">
                        <b-col col xl="2" lg="3" md="4" xs="6">
                            <v-select id="filters_input_ship_name" :reduce="item => item.id" v-model="filterShipName" :options="filterOptionsShips" multiple @input="refreshOrganizationFleet(true)" placeholder="Filter by ship name"></v-select>
                        </b-col>
                        <b-col col xl="2" lg="3" md="4" xs="6">
                            <v-select id="filters_input_citizen_id" :reduce="item => item.id" v-model="filterCitizenId" :options="filterOptionsCitizens" multiple @input="refreshOrganizationFleet(true)" placeholder="Filter by citizen"></v-select>
                        </b-col>
                        <b-col col xl="2" lg="3" md="4" xs="6">
                            <v-select id="filters_input_ship_size" :reduce="item => item.id" v-model="filterShipSize" :options="filterOptionsShipSize" multiple @input="refreshOrganizationFleet(true)" placeholder="Filter by ship size"></v-select>
                        </b-col>
                        <b-col col xl="2" lg="3" md="4" xs="6">
                            <v-select id="filters_input_ship_status" :reduce="item => item.id" v-model="filterShipStatus" :options="filterOptionsShipStatus" @input="refreshOrganizationFleet(true)" placeholder="Filter by ship status"></v-select>
                        </b-col>
                    </b-row>
                    <b-row>
                        <template v-if="(citizen == null && organization !== null && organization.publicChoice !== 'public')
                                        || (citizen != null && !organizations[sid] && organization !== null && organization.publicChoice !== 'public')">
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
                organization: null,
                citizen: null,
                shipFamilies: [], // families of ships (e.g. "Aurora" for MR, LX, etc.) that have the selected orga (no displayed if no orga members have this family).
                actualBreakpoint: 'xs',
                organizations: {}, // orga infos of the citizens
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
                for (let orgaInfo of this.citizen.organizations) {
                    if (orgaInfo.organizationSid === this.selectedSid) {
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
                } else if (this.organizations[this.selectedSid]) {
                    return this.organizations[this.selectedSid].fullname;
                }

                return this.selectedSid;
            }
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
                this.refreshOrganizationFleet();
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
            refreshOrganization() {
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
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    }
                    console.error(err);
                });
            },
            refreshProfile() {
                axios.get('/api/profile/').then(response => {
                    this.citizen = response.data.citizen;
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

                axios.get('/api/my-orgas').then(response => {
                    for (let orga of response.data) {
                        this.$set(this.organizations, orga.spectrumId.sid, orga);
                    }
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
            },
            refreshOrganizationFleet(force) {
                if (!force && this.refreshedSid === this.sid) {
                    // not multiple refresh
                    return;
                }
                if (this.refreshedSid !== this.sid) {
                    this.refreshOrganization();
                    this.refreshFiltersOptions();
                }
                this.refreshedSid = this.sid;
                this.shipFamilies = [];

                this.loadingOrgaFleet = true;
                axios.get(`/api/fleet/orga-fleets/${this.sid}`, {
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
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    }
                    console.error(err);
                }).then(_ => {
                    this.loadingOrgaFleet = false;
                });
            },
            refreshFiltersOptions() {
                axios.get(`/api/organization/${this.sid}/citizens`).then(response => {
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
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    }
                    console.error(err);
                });
                axios.get(`/api/organization/${this.sid}/ships`).then(response => {
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
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
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
            hasOrganization(sid) {
                return this.citizen.organisations.indexOf(sid) !== -1;
            }
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
