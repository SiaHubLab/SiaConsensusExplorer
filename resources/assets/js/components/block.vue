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

    <div class="row" v-if="block.blockheight >= 0">
        <nav aria-label="...">
          <ul class="pager">
            <li>
                <router-link :to="'/block/'+(block.blockheight-1)">Previous block #{{block.blockheight-1}}</router-link>
            </li>

            <li>
                <router-link :to="'/block/'+(block.blockheight+1)">Next block #{{block.blockheight+1}}</router-link>
            </li>
          </ul>
        </nav>
    </div>

    <div class="row" v-if="block.blockheight >= 0">

        <div class="col-md-8 col-md-offset-2">
            <h1>Block height #{{block.blockheight}}</h1>
            <p style="word-wrap: break-word;">Block hash: {{block.id}}</p>
            <p>Maturity Timestamp: {{moment.unix(block.blockheader.timestamp).fromNow()}} | {{moment.unix(block.blockheader.timestamp).format('MMMM DD YYYY, HH:mm:ss')}}</p>

            <div class="row" v-if="block.minerpayouts">
                <div class="col-md-12" v-for="scoutput in block.minerpayouts">
                    <p>
                        <span class="label label-success">Block Reward {{scoutput.value | currency}} SC</span>
                    </p>
                    <mined-by :unlockhash="scoutput.unlockhash" :block="block"></mined-by>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h4>
                        <span class="label label-info">Transactions: {{length(block.transactions)}}</span>
                    </h4>
                    <h4>
                        <span class="label label-info">SC Outputs: {{scoutputs.amount | currency}} SC ({{scoutputs.counter}})</span>
                    </h4>
                    <h4>
                        <span class="label label-info">SF Outputs: {{sfoutputs.amount | sfCurrency}} SF ({{sfoutputs.counter}})</span>
                    </h4>
                    <h4>
                        <span class="label label-info">New file contracts: {{filecontracts.counter}}</span>
                    </h4>
                    <h4>
                        <span class="label label-info">File contract revisions: {{filecontractsrevisions.counter}}</span>
                    </h4>
                </div>
                <div class="col-md-6">
                    <h4>
                        <span class="label label-info">Difficulty: {{block.difficulty | difficulty}} TH</span>
                    </h4>
                    <h4>
                        <span class="label label-info">Estimated hashrate: {{block.estimatedhashrate | hashrate}} GH/s</span>
                    </h4>
                    <h4>
                        <span class="label label-info">Total Coins: {{block.totalcoins | currency}} SC</span>
                    </h4>
                </div>
            </div>

            <!--<div class="row">-->
                <!--<div class="col-md-12"><h3>Siacoin outputs</h3></div>-->
                <!--<div class="col-md-12">-->
                    <!--<div class="table-responsive">-->
                        <!--<table class="table table-hover">-->
                            <!--<tbody>-->
                                <!--<tr v-for="output in scoutputs.data">-->
                                    <!--<td><router-link :to="'/hash/'+output.hash">{{output.hash}}</router-link></td>-->
                                    <!--<td>{{output.value | currency}} SC</td>-->
                                <!--</tr>-->
                            <!--</tbody>-->
                        <!--</table>-->
                    <!--</div>-->
                <!--</div>-->
            <!--</div>-->
            <div class="row">
                <div class="col-md-12"><h3>Siacoin outputs</h3></div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tx ID</th>
                                    <th>Receiver</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tx in this.block.transactions">
                                    <td v-if="lodash.size(tx.siacoininputs) == 1 && lodash.size(tx.siacoinoutputs) == 2">
                                        <router-link :to="'/hash/'+tx.siacoininputs[lodash.keys(tx.siacoininputs)[0]].parentid">
                                            {{tx.siacoininputs[lodash.keys(tx.siacoininputs)[0]].parentid}}
                                        </router-link>
                                    </td>
                                    <td v-if="lodash.size(tx.siacoininputs) == 1 && lodash.size(tx.siacoinoutputs) == 2">
                                        <router-link class="hashCut" :to="'/hash/'+tx.siacoinoutputs[lodash.keys(tx.siacoinoutputs)[0]].unlockhash">
                                            {{tx.siacoinoutputs[lodash.keys(tx.siacoinoutputs)[0]].unlockhash}}
                                        </router-link>
                                        <span title="Internal SC split transaction">{{tx.siacoinoutputs[lodash.keys(tx.siacoinoutputs)[0]].value | currency}} SC</span>
                                        <br />
                                        <router-link class="hashCut" :to="'/hash/'+tx.siacoinoutputs[lodash.keys(tx.siacoinoutputs)[1]].unlockhash">
                                            {{tx.siacoinoutputs[lodash.keys(tx.siacoinoutputs)[1]].unlockhash}}
                                        </router-link>
                                        <span class="label label-success">+{{tx.siacoinoutputs[lodash.keys(tx.siacoinoutputs)[1]].value | currency}} SC</span>
                                    </td>

                                    <td v-if="lodash.size(tx.siacoininputs) == 1 && lodash.size(tx.siacoinoutputs) == 1">
                                        <router-link :to="'/hash/'+tx.siacoininputs[lodash.keys(tx.siacoininputs)[0]].parentid">
                                            {{tx.siacoininputs[lodash.keys(tx.siacoininputs)[0]].parentid}}
                                        </router-link>
                                    </td>
                                    <td v-if="lodash.size(tx.siacoininputs) == 1 && lodash.size(tx.siacoinoutputs) == 1">
                                        <router-link class="hashCut" :to="'/hash/'+tx.siacoinoutputs[lodash.keys(tx.siacoinoutputs)[0]].unlockhash">
                                            {{tx.siacoinoutputs[lodash.keys(tx.siacoinoutputs)[0]].unlockhash}}
                                        </router-link>
                                        <span class="label label-success">+{{tx.siacoinoutputs[lodash.keys(tx.siacoinoutputs)[0]].value | currency}} SC</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" v-if="error">
        <div class="col-md-8 col-md-offset-2">
            <div class="alert alert-danger">
                <p v-for="err in error">{{err}}</p>
                <p>Maybe this block doesn't exists yet? Go back to <router-link :to="'/block/'+($route.params.height-1)">#{{$route.params.height-1}}</router-link></p>
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
import difficulty from '../filters/difficulty.js'
import hashrate from '../filters/hashrate.js'
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
            var data = [];
            _.each(this.block.minerpayouts, (o) => {
                amount += parseInt(o.value);
                data.push({hash:o.unlockhash, value:parseInt(o.value)});
                counter++;
            });
            _.each(this.block.transactions, (t) => {
                _.each(t.siacoinoutputs, (o) => {
                    amount += parseInt(o.value);
                    data.push({hash:o.unlockhash, value:parseInt(o.value)});
                    counter++;
                });
                _.each(t.filecontracts, (fc) => {
                    _.each(fc.validproofoutputs, (vp) => {
                        amount += parseInt(vp.value);
                        data.push({hash:vp.unlockhash, value:parseInt(vp.value)});
                        counter++;
                    });
                });
            });
            data = _.orderBy(data, 'value', 'desc');
            return {amount, counter, data};
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
                _.each(t.filecontractrevisions, (o) => {
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
        filesize,
        difficulty,
        hashrate,
    },
    data() {
        return {
            block: {},
            loading: false,
            error: false,
            moment: moment,
            lodash: _
        }
    }
}
</script>
