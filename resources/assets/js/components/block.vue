<style scoped>
.btn-collapse {
    border-bottom: 0px;
    border-radius: 4px 4px 0px 0px;
}

.alert {
    border-radius: 0px 4px 4px 4px;
}

.flying {
    position: fixed;
    bottom: 0px;
    right: 20px;
}
</style>
<template>
<div>
    <div class="row" v-if="loading">
        <div class="col-md-8 col-md-offset-2">
            <div class="progress">
                <div class="progress-bar progress-bar-striped active" style="width: 100%">
                    <span>Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row blocksList" v-if="block.blockheight >= 0">
        <div class="col-md-8 col-md-offset-2">
            <h1>Block height #{{block.blockheight}}</h1>
            <h3>Maturity Timestamp: {{moment.unix(block.blockheader.timestamp).fromNow()}} | {{moment.unix(block.blockheader.timestamp).format('MMMM DD YYYY, HH:mm:ss')}}</h3>

            <div class="row" v-if="block.minerpayouts">
                <div class="col-md-12" v-for="scoutput in block.minerpayouts">
                    <p>
                        <span class="label label-success">Block Reward {{scoutput.value | currency}} SC</span>
                    </p>
                    <p>
                        <span class="label label-primary">Mined by: <router-link :to="'/hash/'+scoutput.unlockhash" class="label-link-white">{{scoutput.unlockhash}}</router-link></span>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <h4>
                        <span class="label label-info">Transactions: {{length(block.transactions)}}</span>
                    </h4>
                </div>
                <div class="col-md-3">
                    <h4>
                        <span class="label label-info">SC Outputs: {{scoutputs.amount | currency}} SC ({{scoutputs.counter}})</span>
                    </h4>
                    <h4>
                        <span class="label label-info">SF Outputs: {{sfoutputs.amount | sfCurrency}} SF ({{sfoutputs.counter}})</span>
                    </h4>
                </div>
                <div class="col-md-3">
                    <h4>
                        <span class="label label-info">New file contracts: {{filecontracts.counter}}</span>
                    </h4>
                    <h4>
                        <span class="label label-info">File contract revisions: {{filecontractsrevisions.counter}}</span>
                    </h4>
                </div>
            </div>

            <div class="row">
                <nav aria-label="...">
                  <ul class="pager">
                    <li>
                        <router-link :to="'/block/'+(block.blockheight-1)">Previous block #{{block.blockheight-1}}</router-link>
                    </li>

                    <li><router-link :to="'/block/'+(block.blockheight+1)">Next block #{{block.blockheight+1}}</router-link></li>
                  </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="row" v-if="error">
        <div class="col-md-8 col-md-offset-2">
            <div class="alert alert-danger">
                <p v-for="err in error">{{err}}</p>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import hashType from '../filters/hashType.js'
import currency from '../filters/currency.js'
import sfCurrency from '../filters/sfCurrency.js'
import filesize from '../filters/filesize.js'
import _ from 'lodash'
import moment from 'moment'

export default {
    mounted() {
        this.load();
    },
    watch: {
        "$route.params.height": function() {
            this.load();
        }
    },

    computed: {
        uniqueBlocks: function() {
            if (this.block && typeof this.block !== "undefined") {
                return _.uniqBy(this.block, 'height');
            } else {
                return [];
            }
        },
        scoutputs: function(){
            var amount = 0;
            var counter = 0;
            _.each(this.block.minerpayouts, (o) => {
                amount += parseInt(o.value);
                counter++;
            });
            _.each(this.block.transactions, (t) => {
                _.each(t.siacoinoutputs, (o) => {
                    amount += parseInt(o.value);
                    counter++;
                });
                _.each(t.filecontracts, (fc) => {
                    _.each(fc.validproofoutputs, (vp) => {
                        amount += parseInt(vp.value);
                        counter++;
                    });
                });
            });

            return {amount, counter};
        },
        scinputs: function(){
            var counter = 0;
            _.each(this.block.transactions, (t) => {
                _.each(t.siacoininputs, (o) => {
                    counter++;
                });
            });

            return {counter};
        },
        filecontracts: function(){
            var counter = 0;
            _.each(this.block.transactions, (t) => {
                _.each(t.filecontracts, (o) => {
                    counter++;
                });
            });

            return {counter};
        },
        filecontractsrevisions: function(){
            var counter = 0;
            _.each(this.block.transactions, (t) => {
                _.each(t.filecontractsrevisions, (o) => {
                    counter++;
                });
            });

            return {counter};
        },
        sfoutputs: function(){
            var amount = 0;
            var counter = 0;
            _.each(this.block.transactions, (t) => {
                _.each(t.siafundoutputs, (o) => {
                    amount += parseInt(o.value);
                    counter++;
                });
            });

            return {amount, counter};
        },
    },
    methods: {
        length(o) {
            return _.size(o);
        },
        load() {
            if (this.loading) return false;

            this.block = {};
            this.error = false;
            this.loading = true;
            axios.get('/api/block/' + this.$route.params.height)
                .then((response) => {
                    this.block = response.data;
                    this.loading = false;
                })
                .catch((error) => {
                    console.log(error);
                    this.error = error.response.data;
                    this.loading = false;
                });
        },
    },
    filters: {
        hashType,
        currency,
        sfCurrency,
        filesize
    },
    data() {
        return {
            block: {},
            loading: false,
            error: false,
            moment: moment,
        }
    }
}
</script>
