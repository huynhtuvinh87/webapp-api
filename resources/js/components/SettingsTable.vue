<template>
    <div>
        <!--<v-layout row>
            <v-flex class="grow">
                <v-text-field
                        v-on:keydown.enter="run_search"
                        v-model="search"
                        label="Search"
                >
                </v-text-field>
            </v-flex>
            <v-flex class="no-grow">
                <v-btn color="primary" v-on:click="run_search">Search</v-btn>
                <v-btn color="primary" v-on:click="clear_search">Clear</v-btn>
            </v-flex>
        </v-layout>

        <v-data-table
                :headers="headers"
                :items="settings"
                :rows-per-page-items="[20]"
                :pagination.sync="paginate"
                :total-items="total"
                :loading="loading"
        >
            <template slot="items" slot-scope="props">
                <td>{{ props.item.id }}</td>
                <td>{{ props.item.key }}</td>
                <td>
                    <v-text-field
                        v-model="props.item.value"
                    ></v-text-field>
                </td>
                <td>
                    <v-btn color="primary" v-on:click="save(props.item.id, props.item.value)" icon><v-icon>save</v-icon></v-btn>
                    <v-btn color="primary" icon v-on:click="remove(props.item.id, props.item.key)"><v-icon>delete</v-icon></v-btn>
                </td>
            </template>
            <template slot="actions-append">
                <create-setting v-on:created="created"></create-setting>
            </template>
        </v-data-table>-->
        <v-layout row>
            <v-flex xs12 class="text-xs-center">
                <v-subheader class="mb-3">Application Test Environment Settings:</v-subheader>
            </v-flex>
        </v-layout>
        <v-layout row>
            <v-flex xs12 md6>
                <v-card height="100%" class="flex-card mx-2">
                    <v-card-text class="grow">

                        <p>
                            Populate database with some test data. This will add users, contractors, hiring organizations and roles to the database with relationships.
                            Recommend not to use this in production.
                        </p>

                    </v-card-text>
                    <v-card-actions class="justify-end">
                        <v-btn color="primary" v-on:click="seed_data()" v-bind:disabled="lock_seed">Seed Data</v-btn>
                    </v-card-actions>
                </v-card>
            </v-flex>
        </v-layout>
    </div>
</template>

<script>
    export default {
        name: "orgs-table",
        data(){
            return {
                lock_deploy: false,
                lock_seed: false,
                paginate: {},
                settings: [],
                total: 0,
                loading: false,
                search: "",
                headers: [
                    {
                        text: "ID",
                        value: "id",
                        sortable: false
                    },
                    {
                        text: "Key",
                        value: "key",
                        sortable: false
                    },
                    {
                        text: "Value",
                        value: "value",
                        sortable: false
                    },
                    {
                        text: "Actions",
                        value: "actions",
                        sortable: false
                    }
                ]
            }
        },
        methods: {
            //TODO, pagination and search should call ajax, backend should paginate.
            run_search(){
                this.query()
            },
            clear_search(){
                this.search = ""
                this.query()
            },
            async query(){
                this.loading = true

                let request_string = `/admin/settings?page=${this.paginate.page}&search=${this.search}`

                try{

                    let response = await this.$http.get(request_string)

                    this.settings = response.data.settings.data

                    this.total = response.data.settings.total

                }
                catch(e){
                    console.log(e)
                }
                finally{
                    this.loading = false
                }
            },
            async save(id, val){

                this.loading = true

                let request_string = `/admin/setting/${id}`

                try {
                    this.$http.patch(request_string, {
                        value: val
                    });
                }
                catch(e){
                    alert(e)
                    console.log(e)
                }
                finally {
                    this.loading = false
                }
            },
            created(key){
                this.search = key
                this.query()
            },
            async remove(id, key){

                this.loading = true

                if (!confirm(`Are you sure you want to delete setting: ${key}?`)){
                    this.loading = false
                    return
                }

                try {
                    let response = await this.$http.delete(`/admin/setting/${id}`)

                    //if successful request, remove item
                    for (let i = 0; i < this.settings.length; i++){

                        if(this.settings[i].id === id){
                            this.settings.splice(i, 1)
                            break
                        }
                    }

                }
                catch(e){
                    console.log("Something went wrong, could not delete setting")
                }
                finally {
                    this.loading = false
                }

            },
            async seed_data(){

                if (this.lock_seed){
                    return
                }

                if (!confirm("This will add data to the database. Are you sure?")){
                    return
                }

                this.lock_seed = true

                try {
                    let response = await this.$http.get('/admin/settings/seed-data')

                    alert('New data has been added to the database')

                }
                catch(e){

                    alert('Something went wrong')
                    console.log(e)

                }
                finally{
                    this.lock_seed = false
                }

            }
        },
        //Will watch paginate object for init/changes
        watch: {
            paginate(){
                this.query()
            }
        },
    }
</script>

<style scoped>

</style>
