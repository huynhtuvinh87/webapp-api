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
			:items="modules"
			:rows-per-page-items="[20]"
			:pagination.sync="paginate"
			:total-items="total"
			:loading="loading"
		>
			<template slot="items" slot-scope="props">
				<td>{{ props.item.id }}</td>
				<td>{{ props.item.name }}</td>
				<td>
					<v-btn
						color="primary"
						icon
						:href="`/admin/modules/${props.item.id}`"
						><v-icon>pageview</v-icon></v-btn
					>
				</td>
			</template>
		</v-data-table>
	</div>
</template>
<script>
export default {
	name: "modules-table",
	data() {
		return {
			paginate: {},
			modules: [],
			total: 0,
			loading: false,
			search: "",
			headers: [
				{
					text: "Module ID",
					value: "id",
					sortable: false,
				},
				{
					text: "Module Name",
					value: "name",
					sortable: false,
				},
				// {
				// 	text: "Subscription Ends",
				// 	value: "ends_at",
				// 	sortable: false,
				// },
				// {
				// 	text: "Created",
				// 	value: "created_at",
				// 	sortable: false,
				// },
				{
					text: "Actions",
					value: "id",
					sortable: false,
				},
			],
		};
    },
    mounted(){
        this.run_search();
    },
	methods: {
		run_search() {
			this.query();
		},
		clear_search() {
			this.search = "";
			this.query();
		},
		async query() {
			this.loading = true;

			let request_string = `/admin/modules?page=${this.paginate.page}&search=${this.search}`;

			try {
                console.log("Request string");
                let response = await this.$http.get(request_string);

                console.log(response);

				this.modules = response.data.modules.data;

				this.total = response.data.modules.total;
			} catch (e) {
				console.log(e);
			} finally {
				this.loading = false;
			}
		},
	},
};
</script>
