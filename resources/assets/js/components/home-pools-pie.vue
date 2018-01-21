<script>
    import { Pie } from 'vue-chartjs'

    export default {
        extends: Pie,
        props: ['data', 'height', 'width', 'blocks'],
        mounted () {
            axios.get('/api/blocks/distribution/'+this.blocks).then((response) => {
                var data = {
                    labels: []
                };

            var blocks = {
                data: [],
                backgroundColor: ['rgb(251,180,174)','rgb(179,205,227)','rgb(204,235,197)','rgb(222,203,228)','rgb(254,217,166)','rgb(255,255,204)','rgb(229,216,189)','rgb(253,218,236)','rgb(242,242,242)'],
            };

            for(var i in response.data) {
                console.log(response.data[i]);
                data.labels.push((response.data[i].miner) ? response.data[i].miner.name:'Unknown');
                blocks.data.push(response.data[i].blocks);
            }

            data.datasets = [
                blocks
            ];

            this.renderChart(data);
        });
        }
    }
</script>