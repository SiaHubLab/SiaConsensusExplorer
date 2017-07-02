<template>
    <div>
        <div class="row" v-if="hash.hash">
            <div class="col-md-8 col-md-offset-2">
                <h3>Hash: <router-link :to="'/hash/'+hash.hash">{{hash.hash}}</router-link></h3>
                <h5>Hash type: {{hash.type | hashType}}</h5>
                <h5>Found in block: <router-link :to="'/block/'+hash.blocks[0].height">{{hash.blocks[0].height}}</router-link></h5>
            </div>
        </div>

        <div class="row" v-if="loading">
            <div class="col-md-8 col-md-offset-2">
                <div class="progress">
                  <div class="progress-bar progress-bar-striped active" style="width: 100%">
                    <span>Loading...</span>
                  </div>
                </div>
            </div>
        </div>

        <div class="row" v-if="chunks.length > 0">
            <div class="col-md-6 col-md-offset-3">
                <div class="progress">
                  <div :class="'progress-bar '+((currentChunk < 10)?'progress-bar-striped active':'')" style="width: 100%">
                    <span>Loading {{currentChunk*10}} of {{chunks.length*10}} known blocks...</span>
                  </div>
                </div>
                <div class="alert alert-info" v-if="tooManyChunks">
                    <p>Too many known blocks.</p>
                    <p>Limit 100 blocks // Loaded {{currentChunk*10}}.</p>
                    <p><button @click="loadMore()" class="btn btn-success">Load 100 more</button></p>
                </div>
            </div>
        </div>

        <div class="row" v-if="minerpayouts">
            <div class="col-md-8 col-md-offset-2">

                <div class="row" v-if="minerpayouts">
                    <div class="col-md-12" v-for="scoutput in minerpayouts">
                        <div class="alert alert-info">
                            <p><span class="label label-info">Miner payout</span></p>
                            <p>Output ID: <router-link :to="'/hash/'+scoutput.id">{{scoutput.id}}</router-link></p>
                            <p>Amount: {{scoutput.value | currency}} SC</p>
                            <p>Reciever: <router-link :to="'/hash/'+scoutput.unlockhash">{{scoutput.unlockhash}}</router-link></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" v-if="scoutputs">
            <div class="col-md-8 col-md-offset-2">
                <div class="alert alert-info" v-for="scoutput in scoutputs">
                    <p><span class="label label-info">Siacoin transaction</span></p>
                    <p>Transaction ID: <router-link :to="'/hash/'+scoutput.transaction">{{scoutput.transaction}}</router-link></p>
                    <p>Output ID: <router-link :to="'/hash/'+scoutput.id">{{scoutput.id}}</router-link></p>
                    <p>Amount: {{scoutput.value | currency}} SC</p>
                    <p>Reciever: <router-link :to="'/hash/'+scoutput.unlockhash">{{scoutput.unlockhash}}</router-link></p>
                </div>
            </div>
        </div>

        <div class="row" v-if="sfoutputs">
            <div class="col-md-8 col-md-offset-2">
                <div class="alert alert-info" v-for="sfoutput in sfoutputs">
                    <p><span class="label label-info">Siafund transaction</span></p>
                    <p>Transaction ID: <router-link :to="'/hash/'+sfoutput.transaction">{{sfoutput.transaction}}</router-link></p>
                    <p>Output ID: <router-link :to="'/hash/'+sfoutput.id">{{sfoutput.id}}</router-link></p>
                    <p>Amount: {{sfoutput.value | currency}} SF</p>
                    <p>Reciever: <router-link :to="'/hash/'+sfoutput.unlockhash">{{sfoutput.unlockhash}}</router-link></p>
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
import hashType from '../filters/hashType'
import currency from '../filters/currency'
import _ from 'lodash'

    export default {
        mounted() {
            this.load();
        },
        watch: {
            hash(){
                if(this.hash){
                    this.loadBlocks();
                }
            },
            blocks(){
                if(this.blocks){
                    console.log('find ops');
                    this.findTransaction();
                }
            },
            "$route.params.hash": function(){
                this.load();
            }
        },
        methods: {
            load(){
                if(this.loading) return false;

                this.hash = {},
                this.block = {},
                this.transaction = false,
                this.filecontract = false,
                this.scoutputs = [],
                this.sfoutputs = [],
                this.minerpayouts = [],
                this.error = false;
                this.loading = true;
                axios.get('/api/hash/'+this.$route.params.hash)
                    .then((response) => {
                        this.hash = response.data;
                        this.loading = false;
                    })
                    .catch((error) => {
                        console.log(error);
                        this.error = error.response.data;
                        this.loading = false;
                    });
            },
            loadBlocks(){
                if(this.loading) return false;
                this.error = false;
                this.loading = true;
                var blocks = _.flatMap(this.hash.blocks, (b) => { return b.height; });
                this.chunks = _.chunk(blocks, 10);
                this.tooManyChunks = false;
                this.currentChunk = 0;
                this.loadChunk();
            },
            loadMore(){
                this.tooManyChunks = false;
                axios.post('/api/blocks/', {blocks: this.chunks[this.currentChunk], type: this.hash.type, hash: this.hash.hash})
                    .then((response) => {
                        this.currentChunk++;
                        this.loadMorePosition++;
                        _.each(response.data, (b) => { this.blocks.push(b); })
                        if(this.loadMorePosition < 10) {
                            _.delay(this.loadMore, 500);
                        } else {
                            this.tooManyChunks = true;
                            this.loadMorePosition = 0;
                        }
                        this.loading = false;
                    })
                    .catch((error) => {
                        console.log(error);

                        if(this.loadMorePosition < 10) {
                            _.delay(this.loadMore, 500);
                        } else {
                            this.tooManyChunks = true;
                            this.loadMorePosition = 0;
                        }

                        this.loading = false;
                    });
            },
            loadChunk(){
                axios.post('/api/blocks/', {blocks: this.chunks[this.currentChunk], type: this.hash.type, hash: this.hash.hash})
                    .then((response) => {
                        this.currentChunk++;
                        _.each(response.data, (b) => { this.blocks.push(b); })
                        if(this.currentChunk < 10) {
                            _.delay(this.loadChunk, 500);
                        } else {
                            if(this.chunks.length <= 10) {
                                this.chunks = [];
                            } else {
                                this.tooManyChunks = true;
                            }
                        }
                        this.loading = false;
                    })
                    .catch((error) => {
                        console.log(error);
                        //this.error = error.response.data;

                        if(this.chunks.length > this.currentChunk) {
                            _.delay(this.loadChunk, 500);
                        } else {
                            this.chunks = [];
                        }

                        this.loading = false;
                    });
            },
            findTransaction(){
                 switch (this.hash.type) {
                     case 'transactionid':
                        _.each(this.blocks, (b) => {
                            var op = _.find(b.transactions, (tr, id) => { return id == this.hash.hash; });
                            if(op) {
                                this.transaction = op;
                            }
                        });
                        break;

                     case 'unlockhash':
                        this.scoutputs = [];
                        _.each(this.blocks, (b) => {
                            _.each(b.transactions, (o, trid) => {
                                _.each(o.siacoinoutputs, (sco, id) => { this.scoutputs.push(sco); });
                            });

                            _.each(b.minerpayouts, (o, trid) => {
                                this.minerpayouts.push(o);
                            });
                        });

                        break;

                     case 'siacoinoutputid':
                        this.scoutputs = [];
                        _.each(this.blocks, (b) => {
                            _.each(b.transactions, (o, trid) => {
                                var op_index = _.findKey(o.siacoinoutputs, (sco, id) => { return id == this.hash.hash; });
                                if(op_index) {
                                    var op = o.siacoinoutputs[op_index];
                                    op.id = op_index;
                                    op.transaction = trid;
                                    this.scoutputs.push(op);
                                }
                            });
                        });
                        break;

                     case 'filecontractid':
                        _.each(this.blocks, (b) => {
                            _.each(b.transactions, (o, trid) => {
                                var op_index = _.findKey(o.filecontracts, (fc, id) => { return id == this.hash.hash; });
                                if(op_index) {
                                    var op = o.filecontracts[op_index];
                                    op.id = op_index;
                                    op.transaction = trid;
                                    this.filecontract = op;
                                }
                            });
                        });
                        break;

                     case 'siafundoutputid':
                        this.sfoutputs = [];
                        _.each(this.blocks, (b) => {
                            _.each(b.transactions, (o, trid) => {
                                var op_index = _.findKey(o.siafundoutputs, (sfo, id) => { return id == this.hash.hash; });
                                if(op_index) {
                                    var op = o.siafundoutputs[op_index];
                                    op.id = op_index;
                                    op.transaction = trid;
                                    this.sfoutputs.push(op);
                                }
                            });
                        });
                        break;
                 }
            }
        },
        filters: {
            hashType,
            currency
        },
        data(){
            return {
                hash: {},
                blocks: [],
                chunks: [],
                currentChunk: 0,
                loadMorePosition: 0,
                transaction: false,
                filecontract: false,
                tooManyChunks: false,
                scoutputs: false,
                sfoutputs: false,
                minerpayouts: false,
                loading: false,
                error: false
            }
        }
    }
</script>
