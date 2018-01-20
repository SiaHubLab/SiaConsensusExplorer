<script>
    import { Line } from 'vue-chartjs'

    export default {
        extends: Line,
        props: ['data', 'height', 'width'],
        mounted () {
            axios.get('/api/health/main').then((response) => {
                var data = {
                    type: 'line',
                };
                var requests = {
                    label: 'Requests #',
                    backgroundColor: '#b5b5ff',
                    borderColor: '#b5b5ff',
                    yAxisID: 1,
                    data: []
                };

                var responseTime = {
                    label: 'Avg response time (ms)',
                    backgroundColor: 'rgba(182, 255, 182, 0.66)',
                    borderColor: '#b6ffb6',
                    yAxisID: 2,
                    data: []
                };

                for(var i in response.data) {
                    requests.data.push({t: new Date(response.data[i].date * 1000), y: response.data[i].requests});
                    responseTime.data.push({t: new Date(response.data[i].date * 1000), y: Math.round(response.data[i].execution_time*1000)});
                }

                data.datasets = [
                    responseTime,
                    requests
                ];

                this.renderChart(data,
                    {
                        scales: {
                            xAxes: [{
                                type: 'time',
                                time: {
                                    unit: 'minute'
                                }
                            }],
                            yAxes: [{
                                id: 1,
                                type: 'linear',
                                position: 'left',
                            }, {
                                id: 2,
                                type: 'linear',
                                position: 'right'
                            }]
                        }
                    }
                );
            });
        }
    }
</script>