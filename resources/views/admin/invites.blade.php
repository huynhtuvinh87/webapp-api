@extends('layouts.app')

@section('content')

    <v-subheader>
        Pending Invites (Unregistered)
    </v-subheader>

    <v-card>
        <v-card-text>
            <pending-invites-table></pending-invites-table>
        </v-card-text>
    </v-card>

@endsection
