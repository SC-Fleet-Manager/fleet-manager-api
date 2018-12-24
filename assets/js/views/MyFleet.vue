<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card>
                    <div class="mb-3">
                        <b-button v-b-modal.modal-upload-fleet variant="primary" :disabled="citizen == null"><i class="icon-cloud-upload"></i> Update my fleet</b-button>
                        <b-button download :disabled="citizen == null" :href="citizen != null ? '/create-citizen-fleet-file/'+citizen.number.number : ''" variant="success"><i class="icon-cloud-download"></i> Export my fleet (.json)</b-button>
                    </div>
                    <b-alert :show="showError" variant="danger" v-html="errorMessage"></b-alert>
                    <b-row v-if="ships !== null">
                        <b-col v-if="ships.length === 0">
                            <b-alert show variant="warning">Your fleet is empty, you should upload it.</b-alert>
                        </b-col>
                        <b-col col xl="3" lg="4" md="6" v-for="ship in ships" :key="ship.id">
                            <b-card class="mb-3"
                                    :img-src="getShipInfo(getFixShipName(ship.name)).mediaThumbUrl"
                                    img-top
                                    :title="ship.name">
                                <p class="card-text">
                                    <strong>Manufacturer</strong>: {{ ship.manufacturer }}<br/>
                                    <strong>Insured</strong>: {{ ship.insured ? 'Yes' : 'No' }}<br/>
                                    <strong>Cost</strong>: &dollar;{{ ship.cost.cost }}<br/>
                                    <strong>Pledge date</strong>: {{ ship.pledgeDate|date('L') }}<br/>
                                </p>
                            </b-card>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
        <b-modal id="modal-upload-fleet" ref="modalUploadFleet" size="lg" centered title="Update my fleet" hide-footer>
            <UpdateFleetFile @success="onUploadSuccess"></UpdateFleetFile>
        </b-modal>
    </div>
</template>

<script>
    import axios from 'axios';
    import toastr from 'toastr';
    import moment from 'moment-timezone';
    import UpdateFleetFile from './UpdateFleetFile';

    export default {
        name: 'my-fleet',
        components: {UpdateFleetFile},
        data: function () {
            return {
                ships: null,
                shipInfos: [],
                citizen: null,
                showError: false,
                errorMessage: '',
            }
        },
        created() {
            this.refreshMyFleet();

            axios.get('/profile', {
                params: {}
            }).then(response => {
                this.citizen = response.data.citizen;
            }).catch(err => {
                toastr.error('Cannot retrieve your profile.');
                console.error(err);
            });
        },
        filters: {
            date: (value, format) => {
                return moment(value).format(format);
            },
        },
        methods: {
            refreshMyFleet() {
                axios.get('/my-fleet', {
                    params: {}
                }).then(response => {
                    this.ships = [];
                    if (response.data.fleet !== null) {
                        this.ships = response.data.fleet.ships;
                    }
                    this.shipInfos = response.data.shipInfos;
                }).catch(err => {
                    this.showError = true;
                    if (err.response.data.error === 'no_citizen_created') {
                        this.errorMessage = err.response.data.errorMessage;
                    } else {
                        toastr.error('Cannot retrieve your fleet.');
                    }
                    console.error(err);
                });
            },
            onUploadSuccess(ev) {
                this.refreshMyFleet();
                this.$refs.modalUploadFleet.hide();
            },
            getShipInfo(shipName) {
                for (let i in this.shipInfos) {
                    let shipInfo = this.shipInfos[i];
                    if (shipInfo.name === shipName) {
                        return shipInfo;
                    }
                }

                return {
                    mediaThumbUrl: '',
                };
            },
            getFixShipName(hangarShipName) {
                switch (hangarShipName) {
                    case 'Crusader Mercury Star Runner': return 'Mercury Star Runner';
                    case 'Hercules Starlifter C2': return 'C2 Hercules';
                    case 'Hercules Starlifter A2': return 'A2 Hercules';
                    case 'Hercules Starlifter M2': return 'M2 Hercules';
                }

                return hangarShipName;
            }
        }
    }
</script>

<style>
</style>
