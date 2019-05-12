<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card header="Your organisations' fleets">
                    <b-row>
                        <b-col col xl="3" lg="4" md="6" v-if="this.citizen != null">
                            <b-form-group label="Select an organisation" label-for="select-orga">
                                <b-form-select id="select-orga" v-model="selectedSid" class="mb-3">
                                    <option v-for="orga in this.citizen.organisations" :key="orga" :value="orga">{{ orga }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-button download :disabled="selectedSid == null" class="mb-3" :href="'/api/create-organisation-fleet-file/'+selectedSid" variant="success"><i class="icon-cloud-download"></i> Export entire fleet of <strong>{{ selectedSid != null ? selectedSid : 'N/A' }}</strong> (.json)</b-button>
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
                    <b-table small foot-clone hover striped bordered responsive="lg" :items="fleets" :fields="tableHeaders"></b-table>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import select2 from '../components/Select2';

    export default {
        name: 'organizations-fleets',
        components: {select2},
        data: function () {
            return {
                citizen: null,
                selectedSid: null,

                shipInfos: [],
                tableHeaders: [],
                fleets: [],
                fields: [],
                citizenSelected: null,
                citizens: [],
                shipSelected: null,
                ships: [],
            }
        },
        created() {
            this.refreshProfile();
        },
        watch: {
            selectedSid(value) {
                this.refreshTable();
            },
        },
        methods: {
            refreshProfile() {
                axios.get('/profile').then(response => {
                    this.citizen = response.data.citizen;
                    if (this.citizen !== null && this.citizen.organisations.length > 0) {
                        this.selectedSid = this.citizen.organisations[0];
                    }
                }).catch(err => {
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    }
                    console.error(err);
                });
            },
            refreshTable() {
                axios.get('/fleets/'+this.selectedSid, {
                    params: {
                        citizens: this.citizenSelected,
                        ships: this.shipSelected,
                    }
                }).then(response => {
                    this.tableHeaders = response.data.tableHeaders;
                    this.fleets = response.data.fleets;
                    this.shipInfos = response.data.shipInfos;
                    if (this.citizens.length === 0) {
                        for (let citizenId in response.data.citizens) {
                            if (!response.data.citizens.hasOwnProperty(citizenId)) continue;
                            this.citizens.push({
                                id: citizenId,
                                text: response.data.citizens[citizenId],
                            });
                        }
                    }
                    if (this.ships.length === 0) {
                        for (let shipId in response.data.ships) {
                            if (!response.data.ships.hasOwnProperty(shipId)) continue;
                            this.ships.push({
                                id: shipId,
                                text: response.data.ships[shipId],
                            });
                        }
                    }
                }).catch(err => {
                    if (err.response.data.errorMessage) {
                        toastr.error(err.response.data.errorMessage);
                    }
                    console.error(err);
                });
            },
        }
    }
</script>

<style>
</style>
