<template>
    <div>
        <v-card-title class="title">
            Users
        </v-card-title>
        <v-form v-on:submit.prevent="generate()">
            <v-layout row>
                <v-flex xs12 md12>
                    <v-subheader>
                        Fields
                    </v-subheader>
                </v-flex>
            </v-layout>
            <v-layout row>
                <v-flex xs6 md3>
                    <v-switch
                            label="email"
                            name="fields[email]"
                            v-model="selected.email"></v-switch>
                </v-flex>
                <v-flex xs6 md3>
                    <v-switch
                            label="First Name"
                            name="fields[first_name]"
                            v-model="selected.first_name"></v-switch>
                </v-flex>
                <v-flex xs6 md3>
                    <v-switch
                            label="User Type"
                            name="fields[user_type]"
                            v-model="selected.user_type"></v-switch>
                </v-flex>
                <v-flex xs6 md3>
                    <v-switch
                            label="Role"
                            name="fields[role]"
                            v-model="selected.role"></v-switch>
                </v-flex>
            </v-layout>
            <v-layout row>
                <v-flex xs12>
                    <v-subheader>
                        Filters
                    </v-subheader>
                </v-flex>
            </v-layout>
            <v-layout row>
                <v-flex xs6 md3>
                    <v-switch
                            label="Subscribed Contractors"
                            name="fields[subscribed]"
                            v-model="switch_subscribed">
                        >
                    </v-switch>
                </v-flex>
                <v-flex xs6 md3>
                    <v-switch
                            label="All Contractors"
                            name="fields[contractors]"
                            v-model="switch_contractor">
                        >
                    </v-switch>
                </v-flex>
                <v-flex xs6 md3>
                    <v-switch
                            label="Hiring Organizations"
                            name="fields[hiring_organization]"
                            v-model="filtered.hiring_organization">
                        >
                    </v-switch>
                </v-flex>
                <v-flex xs6 md3>
                    <v-switch
                            label="Marketing Consent"
                            name="fields[marketing_consent]"
                            v-model="filtered.marketing_consent">
                        >
                    </v-switch>
                </v-flex>
            </v-layout>
            <v-layout row>
                <v-flex xs12>
                    <v-btn type="submit" color="primary" outline>Submit</v-btn>
                </v-flex>
            </v-layout>
        </v-form>
    </div>
</template>

<script>
    export default {
        name: "exports-selector",
        data: function () {
            return {
                selected: {
                    email: true,
                    first_name: true,
                    user_type: true,
                    role: true
                },
                filtered: {
                    subscribed: false,
                    contractor: true,
                    hiring_organization: true,
                    marketing_consent: true
                }
            }
        },
        computed: {
            switch_subscribed: {
                get() {
                    return this.filtered.subscribed
                },
                set(val) {
                    if (val === true) {
                        this.filtered.contractor = false
                    }
                    this.filtered.subscribed = val
                }
            },
            switch_contractor: {
                get() {
                    return this.filtered.contractor
                },
                set(val) {
                    if (val === true) {
                        this.filtered.subscribed = false
                    }
                    this.filtered.contractor = val
                }
            }
        },
        methods: {
            generate() {

                //Form will submit to a new tab using a post request, but display settings here for now
                alert(JSON.stringify({
                    filters: this.filtered,
                    columns: this.selected
                }))

            }
        }
    }
</script>

<style scoped>

</style>