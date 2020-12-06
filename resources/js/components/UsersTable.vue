<template>
    <div>
        <v-layout row>
            <v-flex class="grow">
                <v-text-field
                        v-model="search"
                        label="Search"
                        v-on:keydown.enter="run_search"
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
                :items="users"
                :pagination.sync="paginate"
                :loading="loading"
				:total-items="paginate.totalItems"
        >
            <template slot="items" slot-scope="props">
                <td>{{ props.item.email }}</td>
                <td>{{ props.item.role }}</td>
                <td>{{ props.item.entity_key }}</td>
                <td>{{ props.item.hiring_org || "NA" }}</td>
                <td>{{ props.item.contractor || "NA" }}</td>
                <td>{{ props.item.sub_expiring }}</td>
                <td>
                    <v-btn color="primary" title="Edit User" icon :href="`/admin/users/${props.item.user_id}`">
                        <v-icon>edit</v-icon>
                    </v-btn>
                    <v-btn title="Login as User" color="primary" target="_blank" icon :href="`/admin/users/assume/${props.item.user_id}`">
                        <v-icon>account_circle</v-icon>
                    </v-btn>
                    <v-btn title="Clear Cache" color="primary" icon :href="`/admin/users/${props.item.user_id}/clear-cache`">
                        <v-icon>cached</v-icon>
                    </v-btn>
                </td>
            </template>
        </v-data-table>
    </div>
</template>

<script>
    export default {
        name: "users-table",
        data(){
            return {
                paginate: {
					descending: false,
					page: 1,
					rowsPerPage: 10,
					sortBy: 'id',
					totalItems: 0
				},
                loading: false,
                users: [],
                search: "",
                headers: [
                    {
                        text: "Email",
                        value: "email",
                        sortable: false
                    },
                    {
                        text: "role",
                        value: "role",
                        sortable: false
                    },
                    {
                        text: "User Type",
                        value: "entity_key",
                        sortable: false,
                        filter: true
                    },
                    {
                        text: "Hiring Org",
                        value: "hiring_org",
                        sortable: false
                    },
                    {
                        text: "Contractor",
                        value: "contractor",
                        sortable: false
                    },
                    {
                        text: "Sub Expiration",
                        value: "sub_expiring",
                        sortable: false
                    },
                    {
                        text: "Actions",
                        value: "user_id",
                        sortable: false
                    }
                ]
            }
        },
        props: {
            route: {
                type: String,
                default: "/admin/users"
            }
		},
		mounted(){
			this.query();
		},
        computed: {

        },
        components: {

        },
        //Will watch paginate object for init/changes
        watch: {
            'paginate.page': function(){
                this.query();
			},
            'paginate.rowsPerPage': function(){
                this.query();
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

                let request_string = `${this.route}?page=${this.paginate.page}&rowsPerPage=${this.paginate.rowsPerPage}&search=${this.search}`

                try{
                    let response = await this.$http.get(request_string);

                    this.users = response.data.users.data;
					this.paginate.totalItems = response.data.users.total;
					this.paginate.rowsPerPage = response.data.users.per_page;
                }
                catch(e){
                    console.error(e)
                }
                finally{
                    this.loading = false
                }
			}
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
