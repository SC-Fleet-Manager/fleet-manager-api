<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card header="Your organisations' fleets">
                    <div class="mb-3">
                        <b-dropdown text="Corpo" v-for="corpo in corpos" :key="corpo.id">
                            <b-dropdown-item>{{ corpo }}</b-dropdown-item>
                        </b-dropdown>
                    </div>
                    <b-button download :disabled="false" class="mb-3" href="/create-organisation-fleet-file/flk" variant="success">Export entire fleet (.json)</b-button>
                    <div class="mb-1">
                        <label style="width: 50%">Citizens :
                        <select2 :options="citizens" v-model="citizenSelected" multiple style="width: 50%" @input="refreshTable"></select2></label>
                    </div>
                    <div class="mb-3">
                        <label style="width: 50%">Ships :
                        <select2 :options="ships" v-model="shipSelected" multiple style="width: 50%" @input="refreshTable"></select2></label>
                    </div>
                    <b-table small foot-clone hover striped bordered responsive="lg" :items="fleets" :fields="tableHeaders"></b-table>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import select2 from '../components/Select2';

    export default {
        name: 'dashboard',
        components: {select2},
        data: function () {
            return {
                corpos: [],

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
            this.refreshTable();
        },
        methods: {
            refreshTable() {
                axios.get('/fleets/flk', {
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
                }).catch(e => {
                    console.error(e);
                });
            },
        }
    }
</script>

<style>
</style>
