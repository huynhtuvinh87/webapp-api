<template>
    <div>
		<v-alert
            :value="message != null"
            :type="status == 200 ? 'success': 'error'"
        >
			{{message}}
        </v-alert>
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
                :items="contractors"
                :rows-per-page-items="[20]"
                :pagination.sync="paginate"
                :total-items="total"
                :loading="loading"
        >
            <template slot="items" slot-scope="props">
                <td>{{ props.item.id }}</td>
                <td>{{ props.item.name }}</td>
                <td>{{ props.item.ends_at}}</td>
                <td>{{ props.item.created_at }}</td>
                <td>
                    <v-btn color="primary" icon :href="`/admin/contractor/${props.item.id}`"><v-icon>pageview</v-icon></v-btn>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn
                                color="primary"
                                icon
                                @click="updateStripe(props.item.id)"
                                v-on="on"
                            >
                                <v-icon>toll</v-icon>
                            </v-btn>
                        </template>
                        <span>Resend Stripe Metadata</span>
                    </v-tooltip>
					<v-btn title="Clear Cache" color="primary" icon :href="`/admin/contractor/${props.item.id}/clear-cache`">
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

        </v-data-table>

    </div>
</template>

<script>
    export default {
        name: "contractors-table",
        data(){
            return {
				message: null,
				status: null,
                paginate: {},
                contractors: [],
                total: 0,
                loading: false,
                search: "",
                headers: [
                    {
                        text: "Contractor ID",
                        value: "id",
                        sortable: false
                    },
                    {
                        text: "Contractor Name",
                        value: "name",
                        sortable: false
                    },
                    {
                        text: "Subscription Ends",
                        value: "ends_at",
                        sortable: false
                    },
                    {
                        text: "Created",
                        value: "created_at",
                        sortable: false
                    },
                    {
                        text: "Actions",
                        value: "id",
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
            async query(){
                this.loading = true

                let request_string = `/admin/contractor?page=${this.paginate.page}&search=${this.search}`

                try{

                    let response = await this.$http.get(request_string)

                    this.contractors = response.data.contractors.data

                    this.total = response.data.contractors.total

                }
                catch(e){
                    console.log(e)
                }
                finally{
                    this.loading = false
                }
            },
            async updateStripe(contractorId){
                this.loading = true

                try{
                    let request_string = `/admin/contractor/${contractorId}/update-stripe`
					let response = await this.$http.get(request_string)
					this.status = response.status;
					this.message = response.data.message;
                }
                catch(e){
                    console.log(e)
                }
                finally{
                    this.loading = false
                }
            },
	        async changeStatus(contractor){
		        this.loading = true

		        let url = `/admin/contractor/`+contractor+`/status`

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
        watch: {
            paginate(){
                this.query()
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
