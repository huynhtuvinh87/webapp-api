<template>
    <v-dialog
        v-model="dialog"
        width="500"
    >
        <v-btn
            slot="activator"
            color="primary" icon><v-icon>add</v-icon></v-btn>

        <v-card>
            <v-card-title headline primary-title>Create New Setting</v-card-title>
            <v-card-text>
                <v-form v-on:submit.prevent="save">

                    <v-text-field
                        v-model="key"
                        label="Key"
                        v-on:keydown.enter="save"
                    ></v-text-field>
                    <v-text-field
                        v-model="val"
                        label="Value"
                        v-on:keydown.enter="save"
                    ></v-text-field>
                    <v-btn v-on:click="save" primary>Save</v-btn>

                </v-form>
            </v-card-text>
        </v-card>

    </v-dialog>
</template>

<script>
    export default {
        name: "create-setting",
        data(){
            return {
                key: "",
                val: "",
                dialog: false
            }
        },
        methods:{
            async save(){

                if (!this.key || !this.val){
                    return
                }

                try{
                    let response = await this.$http.post('/admin/setting', {
                        key: this.key.toLowerCase(),
                        value: this.val
                    })
                    this.key = ""
                    this.val = ""
                    this.$emit('created', this.key)
                    this.dialog = false
                }
                catch(e){
                    console.log(e.response)
                    alert(e)
                }

            }
        }
    }
</script>

<style scoped>

</style>