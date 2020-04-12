<template>
    <div class="ship-family-detail">
        <b-collapse v-model="showCollapse" :id="'ship-family-detail-' + index" class="ship-family-detail-variants-wrapper mb-3 card">
            <div class="">
                <div class="ship-family-detail-variants" :class="{'hide-contents': (!this.supportIndex() || selectedShipFamily === null)}"  ref="block-variants">
                    <div v-if="selectedShipVariants.length === 0">
                        <b-alert show variant="warning">No ships was found for this chassis.</b-alert>
                    </div>
                    <ShipVariant v-for="ship in selectedShipVariants" :key="ship.shipInfo.id" :shipVariant="ship"></ShipVariant>
                </div>
            </div>
        </b-collapse>
    </div>
</template>

<script>
    import ShipVariant from './ShipVariant';
    import { createNamespacedHelpers } from 'vuex';

    const { mapGetters } = createNamespacedHelpers('orga_fleet');

    export default {
        name: 'ship-family-detail',
        components: {ShipVariant},
        props: ['index', 'totalShipFamilies', 'breakpoint'],
        data() {
            return {
                showCollapse: false,
            }
        },
        computed: {
            ...mapGetters({
                selectedIndex: 'selectedIndex',
                selectedShipFamily: 'selectedShipFamily',
                selectedShipVariants: 'selectedShipVariants',
            }),
        },
        watch: {
            breakpoint() {
                this.checkCollapse(this.selectedShipFamily);
            },
            selectedShipFamily(shipFamily) {
                this.checkCollapse(shipFamily);
            },
        },
        mounted() {
            this.checkCollapse(this.selectedShipFamily);
        },
        methods: {
            checkCollapse(shipFamily) {
                const willCollapse = shipFamily !== null && this.supportIndex();
                if (!willCollapse && this.$refs['block-variants'] && this.$refs['block-variants'].clientHeight > 10) {
                    // when we collapse we want to keep the previous height for animation
                    this.$refs['block-variants'].style.height = this.$refs['block-variants'].clientHeight + 'px';
                } else if (this.$refs['block-variants']) {
                    this.$refs['block-variants'].style.height = 'initial';
                }
                this.$nextTick(() => {
                    this.showCollapse = willCollapse;
                });
            },
            supportIndex() {
                switch (this.breakpoint) {
                    case 'xl':
                        return this.selectedIndex >= (this.index % 6 === 5 ? this.index - 5 : (this.totalShipFamilies - 1) - (this.totalShipFamilies - 1) % 6)
                            && this.selectedIndex <= this.index;
                    case 'lg':
                        return this.selectedIndex >= (this.index % 4 === 3 ? this.index - 3 : (this.totalShipFamilies - 1) - (this.totalShipFamilies - 1) % 4)
                                && this.selectedIndex <= this.index;
                    case 'md':
                        return this.selectedIndex >= (this.index % 3 === 2 ? this.index - 2 : (this.totalShipFamilies - 1) - (this.totalShipFamilies - 1) % 3)
                                && this.selectedIndex <= this.index;
                    case 'sm':
                        return this.selectedIndex >= (this.index % 2 === 1 ? this.index - 1 : (this.totalShipFamilies - 1) - (this.totalShipFamilies - 1) % 2)
                                && this.selectedIndex <= this.index;
                    case 'xs':
                        return this.selectedIndex == this.index;
                }
                return false;
            },
        }
    };
</script>

<style>
</style>
