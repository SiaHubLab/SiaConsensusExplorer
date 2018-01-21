<template>
    <div style="word-wrap: break-word;">
        <p>
            Mined by: <span v-if="name">{{name}}</span> <router-link :to="'/hash/'+unlockhash">{{unlockhash}}</router-link>
        </p>
    </div>
</template>

<script>
    export default {
        mounted() {
            if(!this.block.hash_data || !this.block.hash_data.miner_id) {
                axios.get('/api/miner/' + this.unlockhash + '/' + this.block.blockheight)
                    .then((response) => {
                        this.miner = response.data;
                })
                .catch((error) => {
                        console.log(error);
                });
            }
        },
        computed: {
            name: function () {
                if(this.block.hash_data && this.block.hash_data.miner_id)
                {
                    return this.block.hash_data.miner.name;
                }

                if(this.miner.miner) {
                    return this.miner.miner;
                }

                return false;
            }
        },
        props: ['unlockhash', 'block'],
        data: function() {
            return {
                miner: false
            }
        }
    }
</script>