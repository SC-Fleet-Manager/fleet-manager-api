<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col col xs="12" sm="12" md="12" lg="8" xl="4">
                <b-card>
                    <h2 class="text-center">Monthly Cost Coverage</h2>
                    <b-progress class="mb-2" :value="amount / 100" :max="monthlyTarget / 100" variant="success" height="2rem"></b-progress>
                    <p class="text-right font-xl" id="progress-amount">
                        <i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>
                        <animated-number :value="amount / 100" :formatValue="formatAmount" :duration="500"/>
                        /
                        <i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ formatAmount(monthlyTarget / 100) }}
                    </p>
                    <h4 class="text-center">Why supporting?</h4>
                    <p class="text-center">Your support means <strong>a lot</strong>. By backing our project, you help cover the costs of <strong>hosting and maintenance</strong>.</p>
                    <div class="text-center">
                        <strong>What you get:</strong>
                        <ul>
                            <li>Supporter icon next to your name.</li>
                            <li>Your name in Supporters Ladder.</li>
                            <li>A chance to access to the FM beta version to test the next version new features and last changes.</li>
                            <li>An amount of FM Coins proportional to your backing to be used in upcoming premium features.</li>
                        </ul>
                    </div>

                    <p class="text-center my-4"><b-btn v-b-modal.modal-funding variant="primary" size="lg"><i class="fas fa-hands-helping"></i> Support Us</b-btn></p>

                    <p class="mb-3"><b-form-checkbox v-model="organizationLadder" @change="changeLaddersMode" switch size="lg">Organizations Tops</b-form-checkbox></p>

                    <b-nav tabs fill class="mb-3">
                        <b-nav-item :active="activeTab == 'monthly'" @click="activeTab = 'monthly'">Monthly Top 20</b-nav-item>
                        <b-nav-item :active="activeTab == 'alltime'" @click="activeTab = 'alltime'">All time Top 20</b-nav-item>
                    </b-nav>
                    <div v-if="activeTab == 'monthly'" class="supporting-ladder" id="ladder-monthly">
                        <b-alert variant="danger" :show="monthlyLadderErrorMessage != null" v-html="monthlyLadderErrorMessage"></b-alert>
                        <div v-if="monthlySpinner" class="mb-2 text-center">
                            <b-spinner variant="primary"></b-spinner>
                        </div>
                        <b-alert v-if="monthlyUsers.length === 0" variant="warning" :show="monthlyUsers.length === 0" class="text-center">
                            Nobody has backed this month yet. 😢 What if you would <a href="/supporters">support us</a>? 😎
                        </b-alert>
                        <b-row v-for="user in monthlyUsers" :key="user.name" class="font-xl" :class="{'font-weight-bold': !!user.me}">
                            <b-col col class="text-right pr-0">{{ user.rank }}.</b-col>
                            <b-col col>{{ user.name }}</b-col>
                            <b-col col class="text-left"><i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ formatAmount(user.amount / 100) }}</b-col>
                        </b-row>
                    </div>
                    <div v-if="activeTab == 'alltime'" id="ladder-all-time">
                        <b-alert variant="danger" :show="alltimeLadderErrorMessage != null" v-html="alltimeLadderErrorMessage"></b-alert>
                        <div v-if="alltimeSpinner" class="mb-2 text-center">
                            <b-spinner variant="primary"></b-spinner>
                        </div>
                        <b-alert v-if="allTimeUsers.length === 0" variant="warning" :show="allTimeUsers.length === 0" class="text-center">
                            Nobody has backed yet. 😢 What if you would <a href="/supporters">support us</a>? 😎
                        </b-alert>
                        <b-row v-for="user in allTimeUsers" :key="user.name" class="font-xl" :class="{'font-weight-bold': !!user.me}">
                            <b-col col class="text-right pr-0">{{ user.rank }}.</b-col>
                            <b-col col>{{ user.name }}</b-col>
                            <b-col col class="text-left"><i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ formatAmount(user.amount / 100) }}</b-col>
                        </b-row>
                    </div>
                </b-card>
            </b-col>
        </b-row>

        <b-modal id="modal-funding" ref="modalFunding" size="md" centered title="Support Us" hide-footer @shown="onModalShown">
            <b-form>
                <b-alert variant="danger" :show="errorMessage != null" v-html="errorMessage"></b-alert>
                <b-alert variant="success" :show="captureSuccessMessage != null" v-html="captureSuccessMessage"></b-alert>

                <b-row>
                    <b-col class="mb-2">
                        <b-btn type="button" block variant="primary" @click="changeAmount(2)">$2</b-btn>
                    </b-col>
                    <b-col class="mb-2">
                        <b-btn type="button" block variant="primary" @click="changeAmount(5)">$5</b-btn>
                    </b-col>
                    <b-col class="mb-2">
                        <b-btn type="button" block variant="primary" @click="changeAmount(10)">$10</b-btn>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col class="mb-2">
                        <b-btn type="button" block variant="primary" @click="changeAmount(20)">$20</b-btn>
                    </b-col>
                    <b-col class="mb-2">
                        <b-btn type="button" block variant="primary" @click="changeAmount(50)">$50</b-btn>
                    </b-col>
                    <b-col class="mb-2">
                        <b-btn type="button" block variant="primary" @click="changeAmount(100)">$100</b-btn>
                    </b-col>
                </b-row>
                <b-form-group :invalid-feedback="formViolations.amount" :state="formViolations.amount === null ? null : false">
                    <b-input-group prependHtml="<i class='fas fa-dollar-sign' aria-hidden='true'></i><span class='sr-only'>$</span>">
                        <b-form-input
                            type="number"
                            min="1"
                            step="0.01"
                            id="input-funding-amount"
                            v-model="form.amount"
                            :state="formViolations.amount === null ? null : false"
                            placeholder="Your backing amount"
                        ></b-form-input>
                    </b-input-group>
                </b-form-group>

                <div v-if="spinner" class="mb-2 text-center">
                    <b-spinner variant="primary"></b-spinner>
                </div>

                <b-row>
                    <b-col col lg="6" xl="6" offset-lg="3" offset-xl="3">
                        <div id="paypal-button-container" ref="paypalButton"></div>
                    </b-col>
                </b-row>

                <div class="text-center"><small>No payment informations are transmitted to Fleet Manager.</small></div>
            </b-form>
        </b-modal>
    </div>
</template>

<script>
    import axios from 'axios';
    import AnimatedNumber from 'animated-number-vue';

    export default {
        name: 'supporters',
        components: {AnimatedNumber},
        data() {
            return {
                amount: 0,
                monthlyTarget: 0,
                activeTab: 'alltime',
                monthlySpinner: false,
                alltimeSpinner: false,
                monthlyUsers: [],
                allTimeUsers: [],
                form: {amount: 2},
                formViolations: {amount: null},
                errorMessage: null,
                captureSuccessMessage: null,
                spinner: false,
                monthlyLadderErrorMessage: null,
                alltimeLadderErrorMessage: null,
                organizationLadder: false,
            };
        },
        created() {
            this.refreshProgress();
            this.refreshAlltimeLadder();
            this.refreshMonthlyLadder();
        },
        mounted() {
            axios.get('/api/funding/configuration').then(response => {
                let paypalScript = document.createElement('script');
                paypalScript.setAttribute('src', `https://www.paypal.com/sdk/js?currency=${response.data.currency}&client-id=${response.data.paypalClientId}`);
                document.head.appendChild(paypalScript);
            });
        },
        methods: {
            changeLaddersMode(orgaLadder) {
                this.organizationLadder = orgaLadder;
                this.refreshAlltimeLadder();
                this.refreshMonthlyLadder();
            },
            refreshAlltimeLadder() {
                this.alltimeSpinner = true;
                axios({
                    method: 'get',
                    url: `/api/funding/ladder-alltime?orgaMode=${this.organizationLadder}`,
                }).then(response => {
                    this.alltimeSpinner = false;
                    this.allTimeUsers = response.data.topFundings;
                }).catch(err => {
                    this.alltimeSpinner = false;
                    this.alltimeLadderErrorMessage = 'Sorry, we cannot retrieve this ladder for the moment.';
                });
            },
            refreshMonthlyLadder() {
                this.monthlySpinner = true;
                axios({
                    method: 'get',
                    url: `/api/funding/ladder-monthly?orgaMode=${this.organizationLadder}`,
                }).then(response => {
                    this.monthlySpinner = false;
                    this.monthlyUsers = response.data.topFundings;
                }).catch(err => {
                    this.monthlySpinner = false;
                    this.monthlyLadderErrorMessage = 'Sorry, we cannot retrieve this ladder for the moment.';
                });
            },
            refreshProgress() {
                axios({
                    method: 'get',
                    url: '/api/funding/progress',
                }).then(response => {
                    this.monthlyTarget = response.data.target;
                    setTimeout(() => {
                        this.amount = response.data.progress;
                    }, 500);
                }).catch(err => {
                    this.$toastr.e('Sorry, cannot retrieve the current progress cost coverage.');
                });
            },
            changeAmount(newAmount) {
                this.form.amount = newAmount;
            },
            onModalShown() {
                this.renderPayPalButton();
            },
            renderPayPalButton() {
                const button = document.querySelector('#paypal-button-container');
                if (!window.paypal || !button || button.hasChildNodes()) {
                    return;
                }
                window.paypal.Buttons({
                    style: {
                        label: 'pay',
                        layout: 'horizontal',
                        fundingicons: 'true',
                    },
                    funding: {
                        allowed: [ window.paypal.FUNDING.CARD ],
                        disallowed: [ window.paypal.FUNDING.CREDIT ]
                    },
                    createOrder: async () => {
                        this.errorMessage = null;
                        this.formViolations = {amount: null};

                        return axios({
                            method: 'post',
                            url: '/api/funding/payment',
                            data: {
                                amount: this.form.amount ? Math.floor(this.form.amount * 100) : null,
                            },
                        }).then(response => {
                            this.form = {amount: null};

                            return response.data.paypalOrderId;
                        }).catch(err => {
                            if (err.response.status === 401) {
                                this.errorMessage = 'Sorry, but we need you to be connected to support us. Please click on "Login" top-right the page.';
                            } else if (err.response.data.formErrors) {
                                for (let violation of err.response.data.formErrors.violations) {
                                    this.$set(this.formViolations, violation.propertyPath, violation.title);
                                }
                            } else if (err.response.data.error === 'paypal_error') {
                                this.errorMessage = 'An error has occurred when submitting your backing to PayPal:';
                                this.errorMessage += '<ul class="mb-0">';
                                if (err.response.data.paypalError.details.length > 0) {
                                    for (let errorDetail of err.response.data.paypalError.details) {
                                        this.errorMessage += `<li>${errorDetail.description}</li>`;
                                    }
                                } else {
                                    this.errorMessage += `<li>${err.response.data.paypalError.message}</li>`;
                                }
                                this.errorMessage += '</ul>';
                            } else {
                                this.errorMessage = 'An unexpected error has occurred. Please try again in a moment.';
                            }
                        });
                    },
                    onApprove: async data => {
                        this.captureSuccessMessage = null;
                        this.errorMessage = null;
                        this.spinner = true;

                        return axios({
                            method: 'post',
                            url: '/api/funding/capture-transaction',
                            data: data,
                        }).then(response => {
                            this.spinner = false;
                            this.captureSuccessMessage = 'Thank you very much for your backing!<br/>You can review it in <a href="/my-backings">My backings</a>.';
                        }).catch(err => {
                            this.spinner = false;
                            if (err.response.data.errorMessage) {
                                this.errorMessage = err.response.data.errorMessage;
                            } else if (err.response.data.error === 'paypal_error') {
                                this.errorMessage = `An error has occurred when validating your backing to PayPal.<br/>
                                    Please contact us to fleet-manager [at] protonmail.com.<br/>
                                    We apologize for the inconvenience.`;
                            } else {
                                this.errorMessage = 'Sorry, but an unexpected error has occurred. Please try again in a moment.';
                            }
                        });
                    },
                }).render('#paypal-button-container');
            },
            formatAmount(amount) {
                return new Intl.NumberFormat('en-US', { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);
            },
        }
    }
</script>
