<template>
    <div>
        <div class="row">
            <div class="col-md-6 col-md-offset-3 text-center">
                <div class="btn-group btn-group-lg">
                    <button type="button" :class="'btn btn-default '+((this.blocks == 100) ? 'active':'')" @click.prevent="blocks=100">100 blocks</button>
                    <button type="button" :class="'btn btn-default '+((this.blocks == 500) ? 'active':'')" @click.prevent="blocks=500">500 blocks</button>
                    <button type="button" :class="'btn btn-default '+((this.blocks == 1000) ? 'active':'')" @click.prevent="blocks=1000">1000 blocks</button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h3>Difficulty</h3>
                <mining-metrics-difficulty :blocks="blocks"></mining-metrics-difficulty>
            </div>
            <div class="col-md-6">
                <h3>Estimated Hashrate</h3>
                <mining-metrics-hashrate :blocks="blocks"></mining-metrics-hashrate>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-4 col-md-offset-2">
                <h3>Mining distribution last 100 blocks</h3>
                <home-pools-pie blocks="100"></home-pools-pie>
            </div>
            <div class="col-md-4">
                <h3>Mining distribution last 1000 blocks</h3>
                <home-pools-pie blocks="1000"></home-pools-pie>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {

        },
        data() {
            return {blocks: 100}
        }
    }
</script>