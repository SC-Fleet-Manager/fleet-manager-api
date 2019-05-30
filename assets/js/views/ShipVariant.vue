<template>
    <div class="ship-family-detail-variant mb-3">
        <div class="mb-2 text-center"><img :src="shipVariant.shipInfo.mediaThumbUrl" class="img-fluid" /></div>
        <h4>{{ shipVariant.shipInfo.name }} <!--<a href="#" title="Go to pledge"><i class="fa fa-link"></i></a>--></h4>
        <div class="mb-3"><strong>{{ shipVariant.countTotalShips }}</strong> owned by <strong>{{ shipVariant.countTotalOwners }}</strong> citizens</div>
<!--        <div class="mb-3"><b-form-input type="text" v-model="search[ship.shipInfo.id]" placeholder="Search citizen"></b-form-input></div>&ndash;&gt;-->
        <div class="ship-family-detail-variant-ownerlist" @scroll="onUsersScroll">
            <div v-for="user in shipVariantUsers">{{ user[0].actualHandle.handle }} : {{ user.countShips }}</div>
        </div>
    </div>
</template>

<script>
    import { createNamespacedHelpers } from 'vuex';

    const { mapState, mapActions } = createNamespacedHelpers('orga_fleet');

    export default {
        name: 'ship-variant',
        components: {},
        props: ['shipVariant'],
        data() {
            return {
                shipVariantUsers: [],
                page: 2,
                atBottom: false,
            }
        },
        computed: {
            ...mapState({
                shipVariantUsersTrackChanges: 'shipVariantUsersTrackChanges'
            }),
        },
        watch: {
            shipVariantUsersTrackChanges() {
                this.shipVariantUsers = this.$store.getters['orga_fleet/shipVariantUser'](this.shipVariant.shipInfo.id);
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
            },
        }
    };
</script>

<style>
</style>
