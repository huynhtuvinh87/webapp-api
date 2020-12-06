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
                :items="orgs"
                :rows-per-page-items="[20]"
                :pagination.sync="paginate"
                :total-items="total"
                :loading="loading"
        >
            <template slot="items" slot-scope="props">
                <td>{{ props.item.id }}</td>
                <td>{{ props.item.name }} </td>
                <td>{{ props.item.created_at }}</td>
                <td>
	                <v-btn color="primary" icon :href="`/admin/hiring-org/${props.item.id}`"><v-icon>pageview</v-icon></v-btn>
                    <v-btn title="Clear Cache" color="primary" icon :href="`/admin/hiring-org/${props.item.id}/clear-cache`">
                        <v-icon>cached</v-icon>
                    </v-btn>
                </td>
	            <td>
	                <v-checkbox
		                v-model="!props.item.is_active"
		                color="warning"
		                @change="changeStatus(props.item.id)"
	                ></v-checkbox>
                </td>
            </template>

            <template slot="actions-append">
                <create-org-modal v-on:created="find"></create-org-modal>
            </template>
        </v-data-table>
    </div>
</template>

<script>
    export default {
        name: "orgs-table",
        data(){
            return {
                paginate: {},
                orgs: [],
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
                        text: "Name",
                        value: "name",
                        sortable: false
                    },
                    {
                        text: "Created Date",
                        value: "created_at",
                        sortable: false
                    },
	                {
		                text: "Actions",
		                value: "",
		                sortable: false
	                },
	                {
		                text: "Disabled",
		                value: "is_active",
		                sortable: false
	                },
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
            find(value){
                this.search = value
                this.run_search()
            },
            async query(){
                this.loading = true

                let request_string = `/admin/hiring-org?page=${this.paginate.page}&search=${this.search}`

                try{

                    let response = await this.$http.get(request_string)

                    this.orgs = response.data.orgs.data

                    this.total = response.data.orgs.total

                }
                catch(e){
                    console.log(e)
                }
                finally{
                    this.loading = false
                }
            },
	        async changeStatus(hiring_organization){
		        this.loading = true

		        let url = `/admin/hiring-org/`+hiring_organization+`/status`

		        try{

			        await this.$http.post(url)

		        }
		        catch(e){
			        console.log(e)
		        }
		        finally{
			        this.loading = false
		        }
	        },
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
    .grow {
        flex-grow:1;
    }
    .no-grow {
        flex-grow: 0;
    }
</style>
