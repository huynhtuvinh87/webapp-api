<template>
    <div>
        <v-layout row>
            <v-flex class="grow">
                <v-text-field
                        id="generated_password"
                        label="Password"
                        name="password"
                        browser-autocomplete="off"
                        v-model="password_field"
                ></v-text-field>
            </v-flex>
            <v-flex class="no-grow text-xs-center">
                <v-btn primary outline small @click="copy" :disabled="!password || !copyable">
                    <v-icon small>file_copy</v-icon>
                    <span class="hidden-sm-and-down">Copy</span>
                </v-btn>
                <v-btn primary outline small @click="generate">
                    <v-icon small>loop</v-icon>
                    <span class="hidden-sm-and-down">Generate</span>
                </v-btn>
            </v-flex>
        </v-layout>
    </div>
</template>

<script>
    export default {
        name: "password-gen-field",
        data(){
            return {
                copyable: true,
                password: "",
                length: 12,
                charset: "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
                valid: true
            }
        },
        computed: {
            password_field: {
                get(){
                    return this.password
                },
                set(val){
                    this.$emit('update', val)
                    this.password = val
                }
            }
        },
        methods: {
            generate(){

                this.password = ""

                for (let i = 0; i < this.length; ++i) {
                    this.password += this.charset.charAt(Math.floor(Math.random() * this.charset.length));
                }

                this.validate()

            },
            validate(){
                if (!this.valid){
                    this.generate()
                }
            },
            copy(){
                document.querySelector("#generated_password").select()

                try {
                    let command = document.execCommand('copy')

                    if (!command){
                        throw new Error('Password was not copied. Not browser supported')
                    }
                }
                catch(e){
                    this.copyable = false
                    alert(e)
                }

            }
        }
    }
</script>
<style scoped>

    .grow {
        flex-grow:1
    }

    .no-grow {
        flex-grow: 0;
        flex-shrink: 1
    }

</style>
