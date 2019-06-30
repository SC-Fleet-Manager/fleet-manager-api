<script>
    import { Doughnut } from 'vue-chartjs';

    export default {
        extends: Doughnut,
        methods: {
            setData({ xAxis, yAxis }) {
                this.renderChart(
                    {
                        labels: xAxis,
                        datasets: [
                            {
                                backgroundColor: [
                                    // http://tristen.ca/hcl-picker/#/hlc/6/1.05/E6796C/7EEE9B
                                    '#E6796C',
                                    '#EB85AA',
                                    '#C2A6E0',
                                    '#72C8F1',
                                    '#33E1D4',
                                    '#7EEE9B',
                                ],
                                data: yAxis,
                            }
                        ]
                    },
                    {
                        responsive: true,
                        maintainAspectRatio: false,
                    }
                );
            }
        },
    }
</script>
