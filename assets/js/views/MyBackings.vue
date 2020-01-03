<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col col lg="8" xl="6">
                <b-card title="My Backings">
                    <p>Total backed <span id="total-backed"><i class="fas fa-dollar-sign" aria-hidden="true"></i> <span class="sr-only">$</span>{{ formatNumber(totalEffectiveAmount) }}</span> <span v-if="user != null" id="count-fm-coins"><img src="../../img/coin.svg" title="FM Coins" alt="FM Coins" height="30"> {{ user.coins }}</span></p>
                    <b-table id="backings-table" striped hover :items="backings" :fields="fields"
                             :per-page="perPage"
                             :current-page="currentPage"
                             sort-by="createdAt" :sort-desc="true" responsive="sm" show-empty>
                        <template v-slot:empty="scope">
                            <div role="alert" aria-live="polite">
                                <div class="text-center my-2">You have no backings! 😢 Feel free to <a href="/supporters">support us</a>. 😎</div>
                            </div>
                        </template>
                        <template v-slot:cell(paypalStatus)="data">
                            <span v-html="formatStatus(data.value)"></span>
                        </template>
                    </b-table>
                    <b-pagination v-model="currentPage" :total-rows="backings.length" :per-page="perPage" aria-controls="backings-table"></b-pagination>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import moment from 'moment-timezone';
    import {mapGetters} from "vuex";

    export default {
        name: 'my-backings',
        props: [],
        components: {},
        data() {
            return {
                perPage: 10,
                currentPage: 1,
                fields: [
                    {
                        key: 'createdAt',
                        label: 'Date',
                        sortable: true,
                        formatter: this.formatCreatedAt,
                    },
                    {
                        key: 'paypalStatus',
                        label: 'Status',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Backed amount',
                        sortable: true,
                        formatter: this.formatAmount,
                    },
                    {
                        key: 'coins',
                        label: 'Earned FM Coins',
                        sortable: true,
                    },
                    {
                        key: 'coinsBalance',
                        label: 'FM Coins balance',
                        sortable: true,
                    },
                ],
                backings: [],
                totalEffectiveAmount: 0,
            }
        },
        created() {
            this.refreshBackings();
        },
        computed: {
            ...mapGetters(['user']),
        },
        watch: {
        },
        methods: {
            formatNumber(value) {
                return new Intl.NumberFormat('en-US', { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value / 100);
            },
            formatAmount(value, key, item) {
                if (value === null) {
                    return null;
                }

                const amount = new Intl.NumberFormat('en-US', { style: 'currency', currency: item.currency }).format(value / 100);
                const refundedAmount = new Intl.NumberFormat('en-US', { style: 'currency', currency: item.currency }).format(item.refundedAmount / 100);
                const diff = new Intl.NumberFormat('en-US', { style: 'currency', currency: item.currency }).format((value - item.refundedAmount) / 100);

                if (item.paypalStatus === 'PARTIALLY_REFUNDED' || item.paypalStatus === 'REFUNDED') {
                    return `${diff} (${amount} - ${refundedAmount})`;
                }
                return amount;
            },
            formatStatus(value) {
                switch (value) {
                    case 'COMPLETED':
                        return `<span class="badge badge-success">Completed</span>`;
                    case 'REFUNDED':
                        return `<span class="badge badge-info">Refunded</span>`;
                    case 'PARTIALLY_REFUNDED':
                        return `<span class="badge badge-info">Partially refunded</span>`;
                    case 'PENDING':
                        return `<span class="badge badge-warning">Pending</span>`;
                    case 'CREATED':
                        return `<span class="badge badge-warning">Created</span>`;
                    case 'DECLINED':
                        return `<span class="badge badge-danger">Declined</span>`;
                    default:
                        return `<span class="badge badge-secondary">${value}</span>`;
                }
            },
            formatCreatedAt(value) {
                return moment(value).format('LLL');
            },
            refreshBackings() {
                axios.get('/api/funding/my-backings').then(response => {
                    this.backings = this.computeBalances(response.data);
                }).catch(err => {
                    this.$toastr.e('Unable to retrieve your backings list. Please retry in a moment.');
                });
            },
            computeBalances(backings) {
                backings.sort((item1, item2) => {
                    return item1.createdAt < item2.createdAt ? -1 : 1;
                });

                let coinsBalance = 0;
                this.totalEffectiveAmount = 0;
                for (let b of backings) {
                    if (b.paypalStatus === 'COMPLETED' || b.paypalStatus === 'REFUNDED' || b.paypalStatus === 'PARTIALLY_REFUNDED') {
                        b.coins = b.effectiveAmount;
                        coinsBalance += b.coins;
                        this.totalEffectiveAmount += b.effectiveAmount;
                    }
                    b.coinsBalance = coinsBalance;
                }

                return backings;
            },
        }
    }
</script>
