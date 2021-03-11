<template>
    <div class="animated fadeIn">
        <b-row>
            <b-col>
                <b-card>

                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import axios from 'axios';
    import moment from 'moment-timezone';
    import placeholderShipUri from '../../img/static/placeholder_ship.svg';

    export default {
        name: 'my-fleet',
        props: ['userHandle'],
        components: {},
        data() {
            return {
                placeholderShipUri,
            };
        },
        created() {

        },
        methods: {
        }
    }
</script>
