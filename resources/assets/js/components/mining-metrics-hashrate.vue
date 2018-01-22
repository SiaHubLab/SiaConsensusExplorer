<script>
    import { Line, mixins } from 'vue-chartjs'

    export default {
        extends: Line,
        mixins: [mixins.reactiveData],
        props: ['height', 'width', 'blocks'],
        mounted() {
            this.renderChart(this.chartData, this.options);
            this.build();
        },
        watch: {
            blocks: function() {
                this.build()
            }
        },
        data: function(){
            var that = this;
            return {
                chartData: {},
                hashrate: {
                    label: 'Estimated Hashrate',
                    backgroundColor: '#b6ffb6',
                    borderColor: '#b6ffb6',
                    data: [{x:1, y:2}]
                },
                options: {
                    tooltips: {
                        callbacks: {
                            label: function(item){
                                return 'Hashrate: ' + item.yLabel.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' GH/s';
                            },
                            footer: function(item){
                                return 'Height: '+that.hashrate.data[item[0].index].height;
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            type: 'time',
                            time: {
                                unit: 'day'
                            }
                        }],
                        yAxes: [{
                            id: 1,
                            type: 'linear',
                            position: 'left'
                        }]
                    }
                }
            };
        },
        methods: {
            build() {
                axios.get('/api/metrics/hashrate/'+this.blocks).then((response) => {
                    this.hashrate.data = [];
                for(var i in response.data) {
                    this.hashrate.data.push({
                        t: new Date(response.data[i].timestamp * 1000),
                        y: response.data[i].estimatedhashrate,
                        height: response.data[i].height
                    });
                }

                this.chartData = {
                    datasets: [this.hashrate]
                };
            });
            }
        }
    }
</script>