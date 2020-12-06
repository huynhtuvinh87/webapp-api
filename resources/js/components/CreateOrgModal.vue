<template>
    <v-dialog
            fullscreen
            v-model="open">

        <v-btn slot="activator" icon dark color="primary"><v-icon>add</v-icon></v-btn>

        <v-card>
            <v-toolbar color="primary" dark>
                <v-btn icon @click="open = false">
                    <v-icon>close</v-icon>
                </v-btn>
                <v-toolbar-title>Create New Hiring Organization</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-toolbar-items>
                    <v-btn dark flat @click="submit()">Save</v-btn>
                </v-toolbar-items>
            </v-toolbar>
            <v-card-text>
                <v-alert type="error" :value="!!error" v-html="error"></v-alert>
                <v-form>
                    <v-subheader>
                        Organization Details
                    </v-subheader>
                    <v-text-field
                        label="Hiring Organization Name"
                        v-model="form.hiring_organization.name"
                    ></v-text-field>
                    <v-subheader>
                        Admin User Details
                    </v-subheader>
                    <v-text-field
                            label="Email"
                            v-model="form.user.email"
                    ></v-text-field>
                    <v-text-field
                            label="First Name"
                            v-model="form.user.first_name"
                    ></v-text-field>
                        <v-text-field
                                label="Last Name"
                                v-model="form.user.last_name"
                        ></v-text-field>
                    <password-gen-field v-on:update="updatePassword"></password-gen-field>
                    <hr>
                    <v-subheader>Optional Fields</v-subheader>
                    <v-text-field
                        label="Address"
                        v-model="form.user.address"
                        ></v-text-field>
                    <v-text-field
                        label="State"
                        v-model="form.user.state"
                        ></v-text-field>
                    <v-text-field
                        label="Country"
                        v-model="form.user.country"
                    >
                    </v-text-field>
                    <v-text-field
                        label="Postal/Zip Code"
                        v-model="form.user.postal_code">
                    </v-text-field>
                    <v-text-field
                        label="Website"
                        v-model="form.user.website"
                        ></v-text-field>
                </v-form>
            </v-card-text>
        </v-card>

    </v-dialog>
</template>

<script>

    let init_form = function(){
        return {
            hiring_organization : {
                name: ""
            },
            user : {
                email: "",
                first_name: "",
                last_name: "",
                password: "",
                address: "",
                city: "",
                state: "",
                country: "",
                postal_code: "",
                website: "",
            }
        }
    }

    export default {
        name: "create-org-modal",
        data(){
            return {
                error: "",
                open: false,
                form: init_form()
            }
        },
        methods: {
            clear_form(){
                this.form = Object.assign(this.form, init_form())
                this.open = false
            },
            updatePassword(pwd){
                this.form.user.password = pwd
            },
            async submit(){
                let form_data = {

                    name: this.form.hiring_organization.name,
                    email: this.form.user.email,
                    first_name: this.form.user.first_name,
                    last_name: this.form.user.last_name,
                    password: this.form.user.password

                }

                try {
                    let response = await this.$http('/admin/hiring-org', {
                        data: form_data,
                        withCredentials: true,
                        method: "post"
                    })

                    this.$emit('created', this.form.hiring_organization.name)

                    this.clear_form()

                }
                catch(e){

                    console.log(e.response)
                    if (e.response.data.errors){
                        for (let key in e.response.data.errors){
                            this.error += e.response.data.errors[key][0]
                            this.error += " "
                        }
                    }

                    else {
                        this.error = e.response.data.message
                    }

                    setTimeout(() => {
                        this.error = ""
                    }, 8000)
                }
                finally{

                }
            }
        }
    }
</script>

<style scoped>

</style>
