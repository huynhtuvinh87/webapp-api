<template>
	<div v-if="m_module != null">
		<v-alert :value="true" type="warning">
            Be advised that this page is still incomplete. Currently all controls only display the current state, and do not actually update the settings in the system.
		</v-alert>
		<v-layout>
			<v-flex shrink class="my-auto">
				<h1>
					{{ m_module.name }}
				</h1>
			</v-flex>
			<v-spacer></v-spacer>
			<v-flex shrink class="my-auto">
				<v-checkbox
					label="Default Visibility"
					v-on:change="updateDefaultVisibility"
					:value="m_module.visible"
					:disabled="m_module == null"
				/>
			</v-flex>
			<v-flex shrink class="my-auto">
				<v-btn @click="save()">Save</v-btn>
			</v-flex>
		</v-layout>
		<v-layout>
			<!-- Hiring Organization Permissions -->
			<v-flex
				v-if="
					m_visibilities != null &&
						m_visibilities.hiring_organization_visibilities != null
				"
			>
				<h2>Hiring Organizations</h2>
				<v-data-table
					:headers="m_headers.hiring_orgs"
					:items="m_visibilities.hiring_organization_visibilities"
				>
					<template slot="items" slot-scope="props">
						<td>{{ props.item.id }}</td>
						<td>{{ props.item.entity_id }}</td>
						<td>
							<v-switch
								:label="
									props.item.visible
										? 'Visible'
										: 'Not Visible'
								"
								v-on:change="
									toggleHiringOrgVisibility(props.item)
								"
								:input-value="props.item.visible"
								value
							/>
						</td>
					</template>
				</v-data-table>
			</v-flex>
			<v-spacer></v-spacer>
			<!-- Contractor Permissions -->
			<v-flex
				v-if="
					m_visibilities != null &&
						m_visibilities.contractor_visibilities != null
				"
			>
				<h2>Contractors</h2>
				<v-data-table
					:headers="m_headers.contractors"
					:items="m_visibilities.contractor_visibilities"
				>
					<template slot="items" slot-scope="props">
						<td>{{ props.item.id }}</td>
						<td>{{ props.item.entity_id }}</td>
						<td>
							<v-switch
								:label="
									props.item.visible
										? 'Visible'
										: 'Not Visible'
								"
								v-on:change="
									toggleHiringOrgVisibility(props.item)
								"
								:input-value="props.item.visible"
								value
							/>
						</td>
					</template>
				</v-data-table>
			</v-flex>
			<v-spacer></v-spacer>

			<!-- Role Permissions -->
			<v-flex
				v-if="
					m_visibilities != null &&
						m_visibilities.role_visibilities != null
				"
			>
				<h2>Roles</h2>
				<v-data-table
					:headers="m_headers.roles"
					:items="m_visibilities.role_visibilities"
				>
					<template slot="items" slot-scope="props">
						<td>{{ props.item.id }}</td>
						<td>{{ props.item.entity_id }}</td>
						<td>
							<v-switch
								:label="
									props.item.visible
										? 'Visible'
										: 'Not Visible'
								"
								v-on:change="
									toggleHiringOrgVisibility(props.item)
								"
								:input-value="props.item.visible"
								value
							/>
						</td>
					</template>
				</v-data-table>
			</v-flex>
		</v-layout>
	</div>
</template>

<script>
export default {
	props: {
		id: {
			type: Number | String,
		},
		route: {
			type: String,
			default: "/admin/modules",
		},
	},
	data() {
		return {
			m_module: null,
			m_paginate: {
				page: 0,
			},
			m_headers: {
				hiring_orgs: [
					{
						text: "Visibility ID",
						value: "id",
						sortable: false,
					},
					{
						text: "Entity ID",
						value: "entity_id",
						sortable: false,
					},
					{
						text: "Visible",
						value: "visible",
						sortable: false,
					},
				],
				contractors: [
					{
						text: "Visibility ID",
						value: "id",
						sortable: false,
					},
					{
						text: "Entity ID",
						value: "entity_id",
						sortable: false,
					},
					{
						text: "Visible",
						value: "visible",
						sortable: false,
					},
				],
				roles: [
					{
						text: "Visibility ID",
						value: "id",
						sortable: false,
					},
					{
						text: "Entity ID",
						value: "entity_id",
						sortable: false,
					},
					{
						text: "Visible",
						value: "visible",
						sortable: false,
					},
				],
			},
			m_visibilities: null,
		};
	},
	mounted() {
		this.loadModule();
		this.loadVisibilities();
	},
	methods: {
		async loadModule() {
			let request_string = `${this.route}/${this.id}`;

			try {
				let response = await this.$http.get(request_string);
				this.m_module = response.data.module;
			} catch (e) {
				console.error(e);
			} finally {
				this.loading = false;
			}
		},
		async loadVisibilities() {
			let request_string = `${this.route}/${this.id}/visibilities`;

			try {
				let response = await this.$http.get(request_string);
				this.m_visibilities = response.data;
			} catch (e) {
				console.error(e);
			} finally {
				this.loading = false;
			}
		},
		save() {
			console.log("Saving");
		},
		updateDefaultVisibility(newVal) {
			this.m_module.visible = newVal === true;
		},
		toggleHiringOrgVisibility(moduleVisibility) {
			// Find moduleVis in array of visibilities
			let visObjIndex = this.m_visibilities.hiring_organization_visibilities.findIndex(
				vis => vis.id == moduleVisibility.id,
			);
			let visObj = this.m_visibilities.hiring_organization_visibilities[
				visObjIndex
			];
			// Set new val to be inverse
			this.m_visibilities.hiring_organization_visibilities[
				visObjIndex
			].visible = !visObj.visible;
		},
	},
};
</script>
