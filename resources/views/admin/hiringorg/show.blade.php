@extends('layouts.app')

@section('content')

    <v-subheader>
        Hiring Organization: {{ $org->name }}
    </v-subheader>

    <v-card>
        <v-card-text>
            <users-table route="/admin/hiring-org/{{ $org->id }}"></users-table>
        </v-card-text>
    </v-card>

@endsection
