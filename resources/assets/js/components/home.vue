<style scoped>
.table
{
    background-color: #fff;
}

.table tr td {
    padding-left: 8px;
}
</style>
<template>
<div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <input class="form-control" placeholder="Search hash" type="text" v-model="search">
            <div v-if="searchErrors">
                <p v-for="error in searchErrors">{{error[0]}}</p>
            </div>
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

    <div class="row" v-if="searchResults.length">
        <div class="col-md-8 col-md-offset-2">
            <h3>Search result:</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Hash</th>
                        <th>Type</th>
                        <th>Block</th>
                    </tr>
                </thead>
                <tr v-for="hash in searchResults">
                    <td>
                        <router-link :to="'/hash/'+hash.hash">
                            {{hash.hash}}
                        </router-link>
                    </td>
                    <td>{{hash.type | hashType}}</td>
                    <td>
                        <router-link :to="'/block/'+hash.blocks[0].height">
                            {{hash.blocks[0].height}}
                        </router-link>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <h3>Latest operations</h3>

            <div class="row">
                <div class="col-md-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Siafund Transaction</th>
                                <th>Block</th>
                            </tr>
                        </thead>
                        <tr v-for="hash in latestSfOutput">
                            <td>
                                <router-link class="hashCut" :to="'/hash/'+hash.hash">
                                    {{hash.hash}}
                                </router-link>
                            </td>
                            <td>
                                <router-link :to="'/block/'+hash.height">
                                    {{hash.height}}
                                </router-link>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Siacoin Transaction</th>
                                <th>Block</th>
                            </tr>
                        </thead>
                        <tr v-for="hash in latestOutput">
                            <td>
                                <router-link class="hashCut" :to="'/hash/'+hash.hash">
                                    {{hash.hash}}
                                </router-link>
                            </td>
                            <td>
                                <router-link :to="'/block/'+hash.height">
                                    {{hash.height}}
                                </router-link>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Address</th>
                                <th>Block</th>
                            </tr>
                        </thead>
                        <tr v-for="hash in latestUnlock">
                            <td>
                                <router-link class="hashCut" :to="'/hash/'+hash.hash">
                                    {{hash.hash}}
                                </router-link>
                            </td>
                            <td>
                                <router-link :to="'/block/'+hash.height">
                                    {{hash.height}}
                                </router-link>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>File Contract ID</th>
                                <th>Block</th>
                            </tr>
                        </thead>
                        <tr v-for="hash in latestFileContract">
                            <td>
                                <router-link class="hashCut" :to="'/hash/'+hash.hash">
                                    {{hash.hash}}
                                </router-link>
                            </td>
                            <td>
                                <router-link :to="'/block/'+hash.height">
                                    {{hash.height}}
                                </router-link>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import _ from 'lodash';

export default {
    mounted() {
        this.load();
    },
    computed: {
        latestSfOutput() { return _.filter(this.latest, (o) => { return o.type == 'siafundoutputid'; }); },
        latestOutput() { return _.filter(this.latest, (o) => { return o.type == 'siacoinoutputid'; }); },
        latestUnlock() { return _.filter(this.latest, (o) => { return o.type == 'unlockhash'; }); },
        latestFileContract() { return _.filter(this.latest, (o) => { return o.type == 'filecontractid'; }); },
    },
    watch: {
        search() {
            var that = this;

            _.debounce(function() {
                if (that.search == "") {
                    that.searchResults = [];
                    that.searchErrors = [];
                    return false;
                }

                if (that.loading) return false;

                that.searchErrors = [];
                that.error = false;
                that.loading = true;
                axios.get('/api/search/' + that.search)
                    .then((response) => {
                        that.searchResults = response.data;
                        that.loading = false;
                    })
                    .catch((error) => {
                        console.log(error);
                        that.searchErrors = error.response.data;
                        that.loading = false;
                    });
            }, 500)();
        }
    },
    methods: {
        load() {
            if (this.loading) return false;
            this.error = false;
            this.loading = true;
            axios.get('/api/latest')
                .then((response) => {
                    this.latest = response.data;
                    this.loading = false;
                })
                .catch((error) => {
                    console.log(error);
                    this.error = error.response.data;
                    this.loading = false;
                });
        }
    },
    filters: {
        hashType(val) {
            var type = "Unknown";
            switch (val) {
                case 'blockid':
                    type = "Block"
                    break;

                case 'transactionid':
                    type = "Transaction ID"
                    break;

                case 'unlockhash':
                    type = "Unlock hash"
                    break;

                case 'siacoinoutputid':
                    type = "SiaCoin Output"
                    break;

                case 'filecontractid':
                    type = "File Contract"
                    break;

                case 'siafundoutputid':
                    type = "SiaFund Output"
                    break;
            }
            return type;
        }
    },
    data() {
        return {
            latest: [],
            searchResults: [],
            searchErrors: [],
            search: '',
            loading: false,
            error: false
        }
    }
}
</script>
