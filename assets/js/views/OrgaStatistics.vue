<template>
    <b-row>
        <b-col lg="6">
            <b-card header="Ships">

                How many total ships : xxx Ships
                Total needed minimum / Maximum crew : xxx min crew - yyy max crew
                Total SCU capacity : xxx Total SCU
                Pie Charts of ship size repartition : Number of Size 1 / 2 / 3 / 4 / 5 / 6
                Number of Flyable vs in concept ships
            </b-card>
        </b-col>
        <b-col lg="6">
            <b-card header="Citizens">
                <dl class="row">
                    <dt class="col-sm-4">Registered citizens</dt>
                    <dd class="col-sm-8">{{ countCitizens }}</dd>
                    <dt class="col-sm-4">Average ships per citizen</dt>
                    <dd class="col-sm-8">{{ averageShipsPerCitizen }}</dd>
                    <dt class="col-sm-4">Citizen with most ships</dt>
                    <dd class="col-sm-8" v-if="citizenMostShips.citizen != null">{{ citizenMostShips.citizen.actualHandle.handle }} ({{ citizenMostShips.countShips }})</dd>
                </dl>
                Column bars of number of owned ships per citizens : x Number of Ships y number of citizens.
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';

    export default {
        name: 'OrgaStatistics',
        props: ['selectedSid'],
        data() {
            return {
                countCitizens: 0,
                averageShipsPerCitizen: 0,
                citizenMostShips: {citizen: null, countShips: 0},
            };
        },
        created() {
            this.findCitizenStatistics();
        },
        methods: {
            findCitizenStatistics() {
                axios.get(`/api/organization/${this.selectedSid}/stats/citizens`).then(response => {
                    this.countCitizens = response.data.countCitizens;
                    this.averageShipsPerCitizen = Math.round(response.data.averageShipsPerCitizen * 10) / 10;
                    this.citizenMostShips = response.data.citizenMostShips;
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
                    } else {
                        toastr.error('An error has occurred when retrieving citizen stats. Please retry more later.');
                    }
                    console.error(err);
                });
            },
        },
    };
</script>
