<script>
    import { Bar } from 'vue-chartjs';
    import { CustomTooltips } from '@coreui/coreui-plugin-chartjs-custom-tooltips';

    export default {
        extends: Bar,
        methods: {
            setData({ xAxis, yAxis }) {
                this.renderChart(
                    {
                        labels: xAxis,
                        datasets: [
                            {
                                label: 'Number of citizens per owned ships',
                                backgroundColor: '#12386B',
                                data: yAxis,
                            }
                        ]
                    },
                    {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            yAxes: [{
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Num of citizens'
                                }
                            }],
                            xAxes: [{
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Num of owned ships'
                                }
                            }],
                        },
                        tooltips: {
                            enabled: false,
                            custom: CustomTooltips,
                            intersect: true,
                            mode: 'index',
                            position: 'nearest',
                            callbacks: {
                                labelColor: function (tooltipItem, chart) {
                                    return { backgroundColor: chart.data.datasets[tooltipItem.datasetIndex].backgroundColor }
                                }
                            }
                        }
                    }
                );
            }
        },
    }
</script>
