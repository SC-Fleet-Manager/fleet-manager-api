<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card :title="!isMyProfile ? 'Citizen ' + userHandle : ''">
                    <div class="mb-3" v-if="isMyProfile">
                        <b-button v-b-modal.modal-upload-fleet variant="primary" :disabled="citizen == null"><i class="fas fa-cloud-upload-alt"></i> Update my fleet</b-button>
                        <b-button download :disabled="citizen == null" :href="citizen != null ? '/api/create-citizen-fleet-file' : ''" variant="success"><i class="fas fa-cloud-download-alt"></i> Export my fleet (.json)</b-button>
                    </div>
                    <b-alert :show="showError" variant="danger" v-html="errorMessage"></b-alert>
                    <b-row v-if="ships !== null">
                        <b-col v-if="ships.length === 0">
                            <b-alert show variant="warning">Your fleet is empty, you should upload it.</b-alert>
                        </b-col>
                        <b-col col xl="3" lg="4" md="6" v-for="ship in ships" :key="ship.id">
                            <b-card class="mb-3 js-card-ship"
                                    :img-src="getShipInfo(getFixShipName(ship.name)).mediaThumbUrl"
                                    img-top
                                    :title="ship.name">
                                <p class="card-text">
                                    <strong>Manufacturer</strong>: {{ ship.manufacturer }}<br/>
                                    <strong>LTI</strong>: <b-badge variant="success" v-if="ship.insured">Yes</b-badge><b-badge variant="danger" v-else>No</b-badge><br/>
                                    <span v-if="ship.cost !== undefined && ship.cost > 0"><strong>Cost</strong>: <i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ ship.cost }}<br/></span>
                                    <span v-if="ship.cost !== undefined && ship.cost == 0"><b-badge variant="info">Referral/Event</b-badge><br/></span>
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
        props: ['userHandle'],
        components: {UpdateFleetFile},
        data() {
            return {
                citizen: null,
                isMyProfile: false,
                ships: null,
                shipInfos: [],
                showError: false,
                errorMessage: '',
            }
        },
        async created() {
            axios.get('/api/profile', {
                params: {}
            }).then(response => {
                this.citizen = response.data.citizen;
                this.$store.commit('updateProfile', response.data.citizen);
                this.isMyProfile = this.citizen.actualHandle.handle === this.userHandle;
            }).catch(err => {
                if (err.response.status === 401) {
                    // not connected
                    return;
                }
                toastr.error('Cannot retrieve your profile.');
            }).then(_ => {
                this.refreshMyFleet();
            });
        },
        filters: {
            date: (value, format) => {
                return moment(value).format(format);
            },
        },
        watch: {
            userHandle() {
                this.ships = null;
                this.shipInfos = [];
                this.showError = false;
                this.errorMessage = '';
                this.refreshMyFleet();
            }
        },
        methods: {
            refreshMyFleet() {
                axios.get(this.isMyProfile ? '/api/fleet/my-fleet' : `/api/fleet/user-fleet/${this.userHandle}`).then(response => {
                    this.ships = [];
                    if (response.data.fleet !== null) {
                        this.ships = response.data.fleet.ships;
                        this.ships.sort((ship1, ship2) => {
                            return ship1.name > ship2.name ? 1 : -1;
                        });
                    }
                    this.shipInfos = response.data.shipInfos;
                }).catch(err => {
                    this.checkAuth(err.response);
                    this.showError = true;
                    if (err.response.data.error === 'no_citizen_created') {
                        this.errorMessage = err.response.data.errorMessage;
                    } else if (err.response.data.error === 'no_rights') {
                        this.errorMessage = err.response.data.errorMessage;
                    } else if (err.response.status === 404) {
                        this.errorMessage = 'This citizen does not exist.';
                    } else {
                        toastr.error('Cannot retrieve your fleet.');
                    }
                    console.error(err);
                });
            },
            onUploadSuccess() {
                this.refreshMyFleet();
                this.$refs.modalUploadFleet.hide();
            },
            getShipInfo(shipName) {
                for (let i in this.shipInfos) {
                    let shipInfo = this.shipInfos[i];
                    if (shipInfo.name.toLowerCase().trim() === shipName.toLowerCase().trim()) {
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
                    case 'Ballista': return 'Anvil Ballista';
                    case 'Ballista Snowblind': return 'Anvil Ballista Snowblind';
                    case 'Ballista Dunestalker': return 'Anvil Ballista Dunestalker';
                    case 'Pisces': return 'C8 Pisces';
                    case 'Pisces - Expedition': return 'C8X Pisces Expedition';
                    case 'Consolidated Outland Pioneer': return 'Pioneer';
                    case 'Crusader Mercury Star Runner': return 'Mercury Star Runner';
                    case 'Cyclone RC': return 'Cyclone-RC';
                    case 'Cyclone RN': return 'Cyclone-RN';
                    case 'Cyclone-TR': return 'Cyclone-TR'; // yes, same
                    case 'Cyclone AA': return 'Cyclone-AA';
                    case 'Defender': return 'Banu Defender';
                    case 'Hercules Starlifter C2': return 'C2 Hercules';
                    case 'Hercules Starlifter M2': return 'M2 Hercules';
                    case 'Hercules Starlifter A2': return 'A2 Hercules';
                    case 'Hornet F7C': return 'F7C Hornet';
                    case 'Hornet F7C-M Heartseeker': return 'F7C-M Super Hornet Heartseeker';
                    case 'Idris-P Frigate': return 'Idris-P';
                    case 'Idris-M Frigate': return 'Idris-M';
                    case 'Khartu-al': return 'Khartu-Al';
                    case 'Nova Tank': return 'Nova';
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
                    case 'Cutlass 2949 Best In Show': return 'Cutlass Black Best In Show Edition';
                    case 'Caterpillar 2949 Best in Show': return 'Caterpillar Best In Show Edition';
                    case 'Hammerhead 2949 Best in Show': return 'Hammerhead Best In Show Edition';
                    case 'Reclaimer 2949 Best in Show': return 'Reclaimer Best In Show Edition';
                }

                return hangarShipName;
            }
        }
    }
</script>

<style>
</style>
