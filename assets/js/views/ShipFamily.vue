<template>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
        <div class="card mb-3 card-ship js-card-ship" :class="{'card-ship-active': (selectedShipFamily !== null && selectedShipFamily.chassisId === shipFamily.chassisId)}" @click="selectShipFamily({index, shipFamily})">
            <div class="position-relative">
                <svg v-if="!imgLazyLoaded" class="card-img-top" viewBox="0 0 351 210"><rect width="351" height="210" style="fill:rgb(128,128,128)"></rect></svg>
                <img v-show="imgLazyLoaded" :src="shipFamily.mediaThumbUrl" :alt="shipFamily.name + ' ship picture'" class="card-img-top" @load="imgLazyLoaded = true" />
                <div class="card-ship-counter">{{ shipFamily.count }}</div>
            </div>
            <div class="card-body">
                <h4 class="card-title">{{ shipFamily.manufacturerCode + ' - ' + shipFamily.name }}</h4>
            </div>
            <!--<div v-show="selectedShipFamily != null && selectedShipFamily.chassisId !== shipFamily.chassisId" style="
                position: absolute;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.2);"
            ></div>-->
        </div>
    </div>
</template>

<script>
    import { createNamespacedHelpers } from 'vuex';

    const { mapGetters, mapActions } = createNamespacedHelpers('orga_fleet');

    export default {
        name: 'ship-family',
        components: {},
        props: ['index', 'shipFamily'],
        data() {
            return {
                imgLazyLoaded: false
            }
        },
        computed: {
            ...mapGetters({
                selectedShipFamily: 'selectedShipFamily',
            }),
        },
        methods: {
            ...mapActions(['selectShipFamily']),
        }
    };
</script>

<style>
</style>
