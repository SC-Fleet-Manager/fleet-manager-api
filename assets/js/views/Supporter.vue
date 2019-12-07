<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col col xs="12" sm="12" md="12" lg="8" xl="4">
                <b-card>
                    <h2 class="text-center">Monthly Cost Coverage</h2>
                    <b-progress class="mb-2" :value="amount" :max="monthlyTarget" variant="success" height="2rem"></b-progress>
                    <p class="text-right font-xl">
                        <i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ amount }}
                        /
                        <i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ monthlyTarget }}
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

                    <b-nav tabs fill class="mb-3">
                        <b-nav-item :active="activeTab == 'monthly'" @click="activeTab = 'monthly'">Monthly Top 20</b-nav-item>
                        <b-nav-item :active="activeTab == 'alltime'" @click="activeTab = 'alltime'">All time Top 20</b-nav-item>
                    </b-nav>
                    <div v-if="activeTab == 'monthly'" class="supporting-ladder">
                        <b-row v-for="user in monthlyUsers" :key="user.name" :class="{'font-weight-bold': !!user.me}">
                            <b-col col class="text-right pr-0">{{ user.rank }}.</b-col>
                            <b-col col>{{ user.name }}</b-col>
                            <b-col col class="text-left"><i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ formatAmount(user.amount) }}</b-col>
                        </b-row>
                    </div>
                    <div v-if="activeTab == 'alltime'" class="supporting-ladder">
                        <b-row v-for="user in allTimeUsers" :key="user.name" :class="{'font-weight-bold': !!user.me}">
                            <b-col col class="text-right pr-0">{{ user.rank }}.</b-col>
                            <b-col col>{{ user.name }}</b-col>
                            <b-col col class="text-left"><i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ formatAmount(user.amount) }}</b-col>
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
    import toastr from 'toastr';

    export default {
        name: 'supporters',
        components: {},
        data() {
            return {
                amount: 0,
                monthlyTarget: 150,
                activeTab: 'alltime',
                monthlyUsers: [ // sort on rank field
                    {rank: 1, name: 'Ioni14', amount: 3000},
                    {rank: 2, name: 'Ashuvidz', amount: 100.50},
                    {rank: 3, name: 'Tarrhen', amount: 10},
                    {rank: 3, name: 'Foobar', amount: 10},
                    {rank: 157, name: 'Toto', amount: 5, me: true},
                ],
                allTimeUsers: [ // sort on rank field
                    {rank: 1, name: 'Ioni14', amount: 200, me: true},
                    {rank: 2, name: 'Ashuvidz', amount: 57},
                ],
                form: {amount: 2},
                formViolations: {amount: null},
                errorMessage: null,
                captureSuccessMessage: null,
                spinner: false,
            };
        },
        created() {
            setTimeout(() => {
                this.amount = 100;
            }, 500);
        },
        mounted() {
            // TODO : retrieve currency + clientId from API
            let paypalScript = document.createElement('script');
            paypalScript.setAttribute('src', 'https://www.paypal.com/sdk/js?currency=USD&client-id=ATtYOovEBhFqTlYetulg1P7hJVlYwKoN3zMxQDrQYdI5_HKlfyy6Jmsn9ieY6FrdlXmsVqHzAcoVBJEq');
            document.head.appendChild(paypalScript);
        },
        methods: {
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

<style lang="scss" scoped>
    .supporting-ladder {
        font-size: x-large;
    }
</style>
