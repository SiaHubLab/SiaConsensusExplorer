<script>
    import { Line, mixins } from 'vue-chartjs'
    import _  from 'lodash'

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
                difficulty: {
                    label: 'Difficulty',
                    backgroundColor: '#b5b5ff',
                    borderColor: '#b5b5ff',
                    data: [{x:1, y:2}]
                },
                options: {
                    tooltips: {
                        callbacks: {
                            label: function(item){
                                return 'Difficulty: '+item.yLabel.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",")+' TH';
                            },
                            footer: function(item){
                                return 'Height: '+that.difficulty.data[item[0].index].height;
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
                axios.get('/api/metrics/difficulty/'+this.blocks).then((response) => {
                    this.difficulty.data = [];
                    var first = false;
                    for(var i in response.data) {
                        this.difficulty.data.push({
                            t: new Date(response.data[i].timestamp * 1000),
                            y: response.data[i].difficulty,
                            height: response.data[i].height
                        });

                        if(!first) {
                            first = response.data[i].difficulty;
                        }
                    }

                    var lastKey = _.findLastKey(this.difficulty.data);
                    var last = this.difficulty.data[lastKey].y;
                    var diff = ((first-last)/last)*100;
                    this.difficulty.label = "Difficulty:  "+((diff > 0) ? "+":"")+Math.round(diff)+"%";
                    this.chartData = {
                        datasets: [this.difficulty]
                    };
                });
            }
        }
    }
</script>