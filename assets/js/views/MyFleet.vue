<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card>
                    <h4 v-if="!isMyProfile">Citizen <i v-if="isSupporter()" class="fas fa-hands-helping"></i> {{ userHandle }}</h4>
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
                                    <template v-if="ship.insured">
                                        <strong>Insurance</strong>: <b-badge variant="success">Lifetime</b-badge><br/>
                                    </template>
                                    <template v-else>
                                        <template v-if="ship.insuranceDuration != null"><strong>Insurance</strong>: <b-badge variant="info">{{ ship.insuranceDuration }} months</b-badge><br/></template>
                                        <template v-else><strong>Insurance</strong>: <b-badge variant="danger">No</b-badge><br/></template>
                                    </template>
                                    <span v-if="ship.cost !== undefined && ship.cost > 0"><strong>Cost</strong>: <i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ formatNumber(ship.cost) }}<br/></span>
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
    import moment from 'moment-timezone';
    import UpdateFleetFile from './UpdateFleetFile';

    export default {
        name: 'my-fleet',
        props: ['userHandle'],
        components: {UpdateFleetFile},
        data() {
            return {
                publicProfile: null,
                user: null,
                citizen: null,
                isMyProfile: false,
                ships: null,
                shipInfos: [],
                showError: false,
                errorMessage: '',
                shipNames: {},
            };
        },
        created() {
            axios.get('/api/profile', {
                params: {}
            }).then(response => {
                this.user = response.data;
                this.citizen = response.data.citizen;
                this.$store.commit('updateProfile', response.data.citizen);
                this.isMyProfile = this.citizen.actualHandle.handle === this.userHandle;
            }).catch(err => {
                if (err.response.status === 401) {
                    // not connected
                    return;
                }
                this.$toastr.e('Cannot retrieve your profile.');
            }).then(_ => {
                this.refreshMyFleet();
            });
            axios.get(`/api/public-profile/${this.userHandle}`).then(response => {
                this.publicProfile = response.data;
            });
            axios.get('/api/ship-names').then(response => {
                this.shipNames = response.data.shipNames;
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
                if (this.citizen !== null) {
                    this.isMyProfile = this.citizen.actualHandle.handle === this.userHandle;
                }
                this.refreshMyFleet();
            }
        },
        methods: {
            formatNumber(value) {
                return new Intl.NumberFormat('en-US', { style: 'decimal', maximumFractionDigits: 0 }).format(value);
            },
            isSupporter() {
                return this.publicProfile !== null ? this.publicProfile.supporter : false;
            },
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
                        this.$toastr.e('Cannot retrieve your fleet.');
                    }
                });
            },
            onUploadSuccess() {
                this.refreshMyFleet();
                this.$refs.modalUploadFleet.hide();
            },
            getShipInfo(shipName) {
                for (let shipInfo of this.shipInfos) {
                    if (shipInfo.name.toLowerCase().trim() === shipName.toLowerCase().trim()) {
                        return shipInfo;
                    }
                }

                return {mediaThumbUrl: '/build/images/static/placeholder_ship.svg'};
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
                if (!this.shipNames[hangarShipName]) {
                    return hangarShipName;
                }

                return this.shipNames[hangarShipName].shipMatrixName;
            }
        }
    }
</script>
