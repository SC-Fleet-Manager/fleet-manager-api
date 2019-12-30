<template>
    <div class="ship-family-detail-variant mb-3">
        <div class="mb-2 text-center position-relative">
            <svg v-if="!imgLazyLoaded" class="img-fluid" viewBox="0 0 351 210"><rect width="351" height="210" style="fill:rgb(128,128,128)"></rect></svg> <!---->
            <img v-show="imgLazyLoaded" :src="shipVariant.shipInfo.mediaThumbUrl" :alt="shipVariant.shipInfo.name + ' ship picture'" class="img-fluid" @load="imgLazyLoaded = true" />
            <div class="ship-family-detail-variant-counter">{{ shipVariant.countTotalShips }}</div>
        </div>
        <h4>
            <a v-once v-if="shipVariant.shipInfo.pledgeUrl" :href="shipVariant.shipInfo.pledgeUrl" target="_blank" title="Go to pledge">{{ shipVariant.shipInfo.name }}</a>
            <template v-once v-else>{{ shipVariant.shipInfo.name }}</template>
        </h4>
<!--        <div class="mb-3"><b-form-input type="text" v-model="search[ship.shipInfo.id]" placeholder="Search citizen"></b-form-input></div>&ndash;&gt;-->
        <div class="ship-family-detail-variant-ownerlist" @scroll="onUsersScroll">
            <div v-for="user in shipVariantUsers">
                <a :href="'/citizen/'+user[0].citizen.actualHandle.handle" target="_blank"><img v-if="user[0].supporter" src="../../img/icon_supporter.svg" alt="Supporter" class="supporter-badge" style="height: 1.4rem" /> {{ user[0].citizen.actualHandle.handle }}</a>
                : {{ user.countShips }}
            </div>
            <i v-if="shipVariantHiddenUsers > 0">
                <template v-if="shipVariantHiddenUsers == 1">+ {{ shipVariantHiddenUsers }} hidden owner</template>
                <template v-else>+ {{ shipVariantHiddenUsers }} hidden owners</template>
            </i>
        </div>
    </div>
</template>

<script>
    import { createNamespacedHelpers } from 'vuex';

    const { mapState, mapGetters, mapActions } = createNamespacedHelpers('orga_fleet');

    export default {
        name: 'ship-variant',
        components: {},
        props: ['shipVariant'],
        data() {
            return {
                shipVariantUsers: [],
                shipVariantHiddenUsers: null,
                page: 2,
                atBottom: false,
                imgLazyLoaded: false
            }
        },
        computed: {
            ...mapGetters({
                selectedSid: 'selectedSid',
                usersInfos: 'usersInfos',
            }),
            ...mapState({
                shipVariantUsersTrackChanges: 'shipVariantUsersTrackChanges',
            }),
        },
        watch: {
            shipVariantUsersTrackChanges() {
                this.shipVariantUsers = this.$store.getters['orga_fleet/shipVariantUser'](this.shipVariant.shipInfo.id);
                this.shipVariantHiddenUsers = this.$store.state.orga_fleet.shipVariantHiddenUsers[this.shipVariant.shipInfo.id];
            },
        },
        methods: {
            ...mapActions(['loadShipVariantUsers']),
            onUsersScroll(ev) {
                const tar = ev.target;
                if (tar.scrollTop / tar.scrollTopMax >= 1) {
                    if (!this.atBottom) {
                        this.atBottom = true;

                        this.loadShipVariantUsers({
                            ship: this.shipVariant,
                            page: this.page
                        });
                        ++this.page; // Beware, the loading is asynchronous, not the page increment.
                    }
                } else {
                    this.atBottom = false;
                }
            }
        }
    };
</script>

<style lang="scss">
    @import '../../css/vendors/variables';

    .ship-family-detail-variant-counter {
        font-family: "Josefin Sans", Helvetica Neue, Arial, sans-serif;
        font-size: 4rem;
        position: absolute;
        bottom: 0;
        right: 0.6rem;
        line-height: 1;
        color: $gray-200;
        text-shadow: 0 0 4px rgba(30, 30, 30, 1);
    }
</style>
