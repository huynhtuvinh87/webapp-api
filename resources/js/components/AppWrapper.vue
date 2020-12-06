<template>
    <div>
        <v-navigation-drawer app v-model="drawer">
            <v-list>
                <v-subheader class="title">
                    Administration
                </v-subheader>
                <v-list-tile ripple href="/admin/users">
                    Manage Users
                </v-list-tile>
                <v-list-tile ripple href="/admin/hiring-org">
                    Manage Hiring Organizations
                </v-list-tile>
                <v-list-tile ripple href="/admin/contractor">
                    Manage Contractor Organizations
                </v-list-tile>
                <v-list-tile ripple href="/admin/contractor-invites">
                    Pending Contractor Invites
                </v-list-tile>
                <v-list-tile ripple href="/admin/modules">
                    Modules
                </v-list-tile>
                <!--<v-list-tile ripple disabled href="/admin/exports">
                    Exports
                </v-list-tile>
                <v-list-tile ripple disabled href="/admin/imports">
                    Imports
                </v-list-tile>
                <v-list-tile ripple disabled href="/admin/notification-templates">
                    Email Templates
                </v-list-tile>
                <v-list-tile ripple disabled href="/admin/promotion-codes">
                    Promo Codes
                </v-list-tile>
                <v-list-tile ripple disabled href="/admin/position-templates">
                    Position/Requirement Templates
                </v-list-tile>-->
                <!--<v-list-tile ripple href="/admin/settings">
                    Application Settings
                </v-list-tile>-->
                <!--
                <v-subheader class="title">
                    User Services
                </v-subheader>
                <v-list-tile ripple disabled href="/user/api-docs">
                    REST API Documentation
                </v-list-tile>
                <v-list-tile ripple disabled href="/user/tokens">
                    Generate API Tokens
                </v-list-tile>
                <v-list-tile ripple disabled href="/user/support">
                    Support
                </v-list-tile>
                -->
            </v-list>
        </v-navigation-drawer>


        <v-toolbar app class="white--text">
            <v-toolbar-side-icon v-on:click="drawer = !drawer"></v-toolbar-side-icon>
            <v-toolbar-title>
                <img style="width: 145px;" src="/images/logo.png" alt="Contractor Compliance Admin">
            </v-toolbar-title>
        </v-toolbar>
        <slot>

        </slot>
        <v-footer color="secondary" app>
            <v-layout justify-space-around>
                <v-icon dark v-on:click="back">arrow_back</v-icon>
                <v-icon dark v-on:click="home">home</v-icon>
                <v-icon dark v-on:click="logout">exit_to_app</v-icon>
            </v-layout>
        </v-footer>
        <form ref="logout" method="POST" action="/admin/logout">
            <input type="hidden" name="_token" :value="csrf">
        </form>
    </div>
</template>

<script>
    export default {
        name: "app-wrapper",
        data() {
            return {
                drawer: undefined,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        },
        methods: {
            home() {
                window.location.href = '/admin'
            },
            back() {
                window.history.back()
            },
            logout() {
                this.$refs.logout.submit()
            }
        }
    }
</script>

<style scoped>

</style>
