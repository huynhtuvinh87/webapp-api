@extends('layouts.app')

@section('content')

    @isset($success)
        <v-alert
            :value="{{ $success }}"
            type="success"
        >
            {{isset($message) ? $message : 'Action completed successfully!'}}
        </v-alert>
    @endisset

    <v-subheader>
        Hiring Organizations
    </v-subheader>
    <v-card>
        <v-card-text>
            <orgs-table></orgs-table>
        </v-card-text>
    </v-card>

@endsection
