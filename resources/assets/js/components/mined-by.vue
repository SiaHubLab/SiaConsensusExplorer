<template>
    <div>
        <p v-if="miner.miner">
            <span class="label label-primary">Mined by: {{miner.miner}} (<router-link :to="'/hash/'+unlockhash" class="label-link-white">{{unlockhash}}</router-link>)</span>
        </p>
        <p v-else>
            <span class="label label-primary">Mined by: <router-link :to="'/hash/'+unlockhash" class="label-link-white">{{unlockhash}}</router-link></span>
        </p>
    </div>
</template>

<script>
    export default {
        mounted() {
            axios.get('/api/miner/' + this.unlockhash + '/' + this.block)
                .then((response) => {
                    this.miner = response.data;
                })
                .catch((error) => {
                   console.log(error);
                });
        },
        props: ['unlockhash', 'block'],
        data: function() {
            return {
                miner: false
            }
        }
    }
</script>