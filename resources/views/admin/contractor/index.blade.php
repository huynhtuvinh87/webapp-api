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
        Contractors
    </v-subheader>
    <v-card>
        <v-card-text>
            <contractors-table></contractors-table>
        </v-card-text>
    </v-card>
@endsection
