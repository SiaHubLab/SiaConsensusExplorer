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
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Hash</th>
                            <th>Type</th>
                            <th>Block</th>
                        </tr>
                    </thead>
                    <tr v-for="hash in latest">
                        <td>
                            <router-link :to="'/hash/'+hash.hash">
                                {{hash.hash}}
                            </router-link>
                        </td>
                        <td>{{hash.type | hashType}}</td>
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
</template>

<script>
    export default {
        mounted() {
            this.load();
        },
        watch: {
            search(){
                if(this.search == "") {
                    this.searchResults = [];
                    this.searchErrors = [];
                    return false;
                }

                if(this.loading) return false;

                this.searchErrors = [];
                this.error = false;
                this.loading = true;
                axios.get('/api/search/'+this.search)
                    .then((response) => {
                        this.searchResults = response.data;
                        this.loading = false;
                    })
                    .catch((error) => {
                        console.log(error);
                        this.searchErrors = error.response.data;
                        this.loading = false;
                    });
            }
        },
        methods: {
            load(){
                if(this.loading) return false;
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
            hashType(val){
                var type = "Unknown";
                switch(val){
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
        data(){
            return {
                latest: [],
                searchResults: [],
                searchErrors: [],
                search: "",
                loading: false,
                error: false
            }
        }
    }
</script>
