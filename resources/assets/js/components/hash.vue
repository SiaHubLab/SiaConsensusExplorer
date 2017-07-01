<template>
    <div>
        <div class="row" v-if="hash.hash">
            <div class="col-md-8 col-md-offset-2">
                <h3>Hash: <router-link :to="'/hash/'+hash.hash">{{hash.hash}}</router-link></h3>
                <h5>Hash type: {{hash.type | hashType}}</h5>
                <h5>Found in block: <router-link :to="'/block/'+hash.blocks[0].height">{{hash.blocks[0].height}}</router-link></h5>
            </div>
        </div>

        <div class="row" v-if="scoutputs">
            <div class="col-md-8 col-md-offset-2">
                <div class="alert alert-info" v-for="scoutput in scoutputs">
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
                axios.post('/api/blocks/', {blocks: blocks})
                    .then((response) => {
                        this.blocks = response.data;
                        this.loading = false;
                    })
                    .catch((error) => {
                        console.log(error);
                        this.error = error.response.data;
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
                                var op_index = _.findKey(o.siacoinoutputs, (sco, id) => { return sco.unlockhash == this.hash.hash; });
                                if(op_index) {
                                    var op = o.siacoinoutputs[op_index];
                                    op.id = op_index;
                                    op.transaction = trid;
                                    this.scoutputs.push(op);
                                }
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
                transaction: false,
                filecontract: false,
                scoutputs: false,
                sfoutputs: false,
                loading: false,
                error: false
            }
        }
    }
</script>
