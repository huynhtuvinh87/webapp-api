<template>
    <div>

        <v-layout row>
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
            :items="invites"
            :pagination.sync="paginate"
            :total-items="total"
            :rows-per-page-items="[20]"
            :loading="loading"
        >
            <template slot="items" slot-scope="props">
                <td>{{ props.item.contractor }}</td>
                <td>{{ props.item.organization }}</td>
                <td>{{ props.item.invite_code }}</td>
                <td><a :href="`mailto:${props.item.contractor_email}?subject=Invitation to Contractor Compliance&body=Your registration link is ${props.item.invite_code}`">
                    {{ props.item.contractor_email }}
                </a></td>
            </template>

        </v-data-table>
    </div>
</template>

<script>
    export default {
        name: "pending-invites-table",
        data(){
            return {
                total: 0,
                paginate: {},
                route: '',
                loading: false,
                invites: [],
                search: "",
                headers: [
                    {
                        text: "Contractor",
                        value: "contractor",
                        sortable: false,
                        filter: false
                    },
                    {
                        text: "Organization",
                        value: "organization",
                        sortable: false,
                        filter: false
                    },
                    {
                        text: "Invite",
                        value: "invite_code",
                        sortable: false,
                        filter: false
                    },
                    {
                        text: "Email",
                        value: "contractor_email",
                        sortable: false,
                        filter: false
                    }
                ]
            }
        },
        computed: {

        },
        watch: {
            paginate(){
                this.query()
            }
        },
        methods: {
            run_search(){
                this.query()
            },
            clear_search(){
                this.search = ""
                this.query()
            },
            /**
             * Call on search change, page change and init
             * @returns {Promise<void>}
             */
            async query(){

                this.loading = true

                let request_string = `/admin/invites?page=${this.paginate.page}&search=${this.search}`


                try{
                    let response = await this.$http.get(request_string)

                    this.invites = response.data.pending_invites.data

                    this.total = response.data.pending_invites.total

                }
                catch(e){
                    console.log(e)
                }
                finally{
                    this.loading = false
                }
            }
        },
        created(){
            this.query()
        }
    }
</script>
<style scoped>
    .grow {
        flex-grow:1;
    }
    .no-grow {
        flex-grow: 0;
    }
</style>
