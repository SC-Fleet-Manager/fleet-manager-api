<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card :title="userHandle ? 'Citizen ' + userHandle : ''">
<!--                    <h1 v-if="userHandle != null">Citizen {{ userHandle }}</h1>-->
                    <div class="mb-3" v-if="userHandle == null">
                        <b-button v-b-modal.modal-upload-fleet variant="primary" :disabled="citizen == null"><i class="icon-cloud-upload"></i> Update my fleet</b-button>
                        <b-button download :disabled="citizen == null" :href="citizen != null ? '/api/create-citizen-fleet-file/'+citizen.number.number : ''" variant="success"><i class="icon-cloud-download"></i> Export my fleet (.json)</b-button>
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
                                    <strong>LTI</strong>: <b-badge variant="success" v-if="ship.insured">Yes</b-badge><b-badge variant="danger" v-else>No</b-badge><br/>
                                    <strong>Cost</strong>: &dollar;{{ ship.cost }}<br/>
                                    <strong>Pledge date</strong>: {{ ship.pledgeDate|date('LL') }}<br/>
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
        props: {
            userHandle: null,
        },
        created() {
            this.refreshMyFleet();

            if (!this.userHandle) { // it is my fleet page
                axios.get('/profile', {
                    params: {}
                }).then(response => {
                    this.citizen = response.data.citizen;
                }).catch(err => {
                    this.checkAuth(err.response);
                    toastr.error('Cannot retrieve your profile.');
                    console.error(err);
                });
            }
        },
        filters: {
            date: (value, format) => {
                return moment(value).format(format);
            },
        },
        methods: {
            refreshMyFleet() {
                axios.get(this.userHandle ? `/user-fleet/${this.userHandle}` : '/my-fleet', {
                    params: {}
                }).then(response => {
                    this.ships = [];
                    if (response.data.fleet !== null) {
                        this.ships = response.data.fleet.ships;
                    }
                    this.shipInfos = response.data.shipInfos;
                }).catch(err => {
                    this.checkAuth(err.response);
                    this.showError = true;
                    if (err.response.data.error === 'no_citizen_created') {
                        this.errorMessage = err.response.data.errorMessage;
                    } else if (err.response.data.error === 'no_rights') {
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
            checkAuth(response) {
                const status = response.status;
                const data = response.data;
                if ((status === 401 && data.error === 'no_auth')
                    || (status === 403 && data.error === 'forbidden')) {
                    window.location = data.loginUrl;
                }
            },
            getFixShipName(hangarShipName) {
                // case '<Display name>: return '<ShipMatrix name>';'
                switch (hangarShipName) {
                    case '315p Explorer': return '315p';
                    case '325a Fighter': return '325a';
                    case '350r Racer': return '350r';
                    case '600i Exploration Module': return '600i Explorer';
                    case '600i Touring Module': return '600i Touring';
                    case '890 JUMP': return '890 Jump';
                    case 'Aopoa San\'tok.yāi': return 'San\'tok.yāi';
                    case 'Argo SRV': return 'SRV';
                    case 'Crusader Mercury Star Runner': return 'Mercury Star Runner';
                    case 'Cyclone RC': return 'Cyclone-RC';
                    case 'Cyclone RN': return 'Cyclone-RN';
                    case 'Cyclone TR': return 'Cyclone-TR';
                    case 'Cyclone AA': return 'Cyclone-AA';
                    case 'Dragonfly Star Kitten Edition': return 'Dragonfly Yellowjacket';
                    case 'Hercules Starlifter C2': return 'C2 Hercules';
                    case 'Hercules Starlifter M2': return 'M2 Hercules';
                    case 'Hercules Starlifter A2': return 'A2 Hercules';
                    case 'Hornet F7C': return 'F7C Hornet';
                    case 'F7A Hornet': return 'F7A Hornet';
                    case 'Hornet F7C-M Heartseeker': return 'F7C-M Super Hornet Heartseeker';
                    case 'Hornet F7C-S Ghost': return 'F7C-S Super Hornet Ghost';
                    case 'Hornet F7C-R Tracker': return 'F7C-R Super Hornet Tracker';
                    case 'Hornet F7C-M Hornet': return 'F7C-M Super Hornet Hornet';
                    case 'Idris-P Frigate': return 'Idris-P';
                    case 'Khartu-al': return 'Khartu-Al';
                    case 'Mustang Omega : AMD Edition': return 'Mustang Omega';
                    case 'Nova Tank': return 'Nova';
                    case 'P-52 Merlin': return 'P52 Merlin';
                    case 'P-72 Archimedes': return 'P72 Archimedes';
                    case 'Reliant Kore - Mini Hauler': return 'Reliant Kore';
                    case 'Reliant Mako - News Van': return 'Reliant Mako';
                    case 'Reliant Sen - Researcher': return 'Reliant Sen';
                    case 'Reliant Tana - Skirmisher': return 'Reliant Tana';
                    case 'Valkyrie ': return 'Valkyrie';
                    case 'Valkyrie Liberator Edition ': return 'Valkyrie Liberator Edition';
                    case 'X1': return 'X1 Base';
                    case 'X1 - FORCE': return 'X1 Force';
                    case 'X1 - VELOCITY': return 'X1 Velocity';
                }

                return hangarShipName;
            }
        }
    }
</script>

<style>
</style>
