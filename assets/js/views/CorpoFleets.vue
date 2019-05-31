<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card header="Your organizations' fleet" class="js-organizations-fleets">
                    <b-row>
                        <b-col col xl="2" lg="3" md="4" v-if="citizen != null">
                            <b-form-group label="Select an organization" label-for="select-orga" class="js-select-orga">
                                <b-form-select id="select-orga" :value="selectedSid" @change="selectSid">
                                    <option v-for="orga in citizen.organisations" :key="orga" :value="orga">{{ organizations[orga] ? organizations[orga].fullname : orga }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col col xl="2" lg="3" md="4" class="mb-3">
                            <b-dropdown variant="primary">
                                <template slot="button-content"><i class="fas fa-cloud-download-alt"></i> Export fleet</template>
                                <b-dropdown-item download :disabled="selectedSid == null || shipFamilies.length == 0" :href="'/api/create-organization-fleet-file/'+selectedSid" ><i class="fas fa-file-code"></i> Export <strong>{{ selectedSid != null ? (organizations[selectedSid] ? organizations[selectedSid].fullname : selectedSid) : 'N/A' }}</strong> fleet (.json)</b-dropdown-item>
                                <b-dropdown-item download :disabled="selectedSid == null || shipFamilies.length == 0" :href="'/api/export-orga-fleet/'+selectedSid"><i class="fas fa-file-csv"></i> Export <strong>{{ selectedSid != null ? (organizations[selectedSid] ? organizations[selectedSid].fullname : selectedSid) : 'N/A' }}</strong> fleet (.csv)</b-dropdown-item>
                            </b-dropdown>
                        </b-col>
                    </b-row>
                    <b-row v-if="selectedSid != null">
                        <b-col col xl="2" lg="3" md="4" xs="6">
                            <b-form-group>
                                <b-form-input id="filters_input_ship_name"
                                              type="text"
                                              v-model="filterShipName"
                                              @keyup="refreshOrganizationFleet"
                                              placeholder="Filter by ship name"></b-form-input>
                            </b-form-group>
                        </b-col>
                        <b-col col xl="2" lg="3" md="4" xs="6">
                            <b-form-group>
                                <b-form-input id="filters_input_citizen_name"
                                              type="text"
                                              v-model="filterCitizenName"
                                              @keyup="refreshOrganizationFleet"
                                              placeholder="Filter by citizen name"></b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col v-if="shipFamilies.length === 0">
                            <b-alert show variant="warning">Sorry, no ships have been found.</b-alert>
                        </b-col>
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
<!--
                    <div class="mb-1">
                        <label style="width: 50%">Citizens :
                            <select2 :options="citizens" v-model="citizenSelected" multiple style="width: 50%" @input="refreshTable"></select2>
                        </label>
                    </div>
                    <div class="mb-3">
                        <label style="width: 50%">Ships :
                            <select2 :options="ships" v-model="shipSelected" multiple style="width: 50%" @input="refreshTable"></select2>
                        </label>
                    </div>
-->
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import select2 from '../components/Select2';
    import ShipFamilyDetail from './ShipFamilyDetail';
    import ShipFamily from './ShipFamily';
    import { createNamespacedHelpers } from 'vuex';

    const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('orga_fleet');
    const BREAKPOINTS = {
        xs: 0,
        sm: 576,
        md: 768,
        lg: 992,
        xl: 1200,
    };

    export default {
        name: 'organizations-fleets',
        props: ['sid'],
        components: {select2, ShipFamily, ShipFamilyDetail},
        data() {
            return {
                citizen: null,
                shipFamilies: [], // families of ships (e.g. "Aurora" for MR, LX, etc.) that have the selected orga (no displayed if no orga members have this family).
                actualBreakpoint: 'xs',
                organizations: {}, // orga infos of the citizens
            }
        },
        mounted() {
            this.refreshProfile();

            window.addEventListener('resize', this.refreshBreakpoint);
            this.refreshBreakpoint();
        },
        computed: {
            filterShipName: {
                get() {
                    return this.$store.state.orga_fleet.filterShipName;
                },
                set(value) {
                    this.$store.state.orga_fleet.filterShipName = value;
                }
            },
            filterCitizenName: {
                get() {
                    return this.$store.state.orga_fleet.filterCitizenName;
                },
                set(value) {
                    this.$store.state.orga_fleet.filterCitizenName = value;
                }
            },
            ...mapGetters({
                selectedSid: 'selectedSid',
                selectedShipFamily: 'selectedShipFamily',
                selectedShipVariants: 'selectedShipVariants',
            }),
        },
        watch: {
            sid(value) {
                if (value) {
                    this.updateSid(value);
                    return;
                }

                if (this.citizen !== null) {
                    const defaultOrga = this.citizen.organisations[0];
                    this.$router.replace({ path: `/organization-fleet/${defaultOrga}` });
                } else {
                    this.refreshProfile();
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
        },
        methods: {
            ...mapActions(['loadShipVariantUsers', 'selectShipFamily']),
            ...mapMutations(['updateSid']),
            selectSid(value) {
                this.$router.replace({ path: `/organization-fleet/${value}` });
                this.updateSid(value);
            },
            refreshProfile() {
                axios.get('/api/profile/').then(response => {
                    this.citizen = response.data.citizen;
                    if (this.citizen !== null && this.citizen.organisations.length > 0) {
                        const defaultOrga = this.citizen.organisations[0];
                        if (!this.sid) {
                            this.$router.replace({ path: `/organization-fleet/${defaultOrga}` });
                            if (this.selectedSid === defaultOrga) {
                                this.refreshOrganizationFleet();
                            }
                            this.updateSid(defaultOrga);
                        } else {
                            this.updateSid(this.sid);
                        }
                    }
                }).catch(err => {
                    this.checkAuth(err.response);
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
                    this.checkAuth(err.response);
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    }
                    console.error(err);
                });
            },
            refreshOrganizationFleet() {
                if (this.selectedSid === null) {
                    return;
                }
                axios.get('/api/fleet/orga-fleets/'+this.selectedSid, {
                    params: {
                        'filters[shipName]': this.filterShipName ? this.filterShipName : null,
                        'filters[citizenName]': this.filterCitizenName ? this.filterCitizenName : null,
                    },
                }).then(response => {
                    this.selectShipFamily({index: null, shipFamily: null});
                    this.shipFamilies = response.data;
                }).catch(err => {
                    this.checkAuth(err.response);
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
            checkAuth(response) {
                const status = response.status;
                const data = response.data;
                if ((status === 401 && data.error === 'no_auth')
                    || (status === 403 && data.error === 'forbidden')) {
                    window.location = data.loginUrl;
                }
            }
        }
    }
</script>

<style>
</style>
