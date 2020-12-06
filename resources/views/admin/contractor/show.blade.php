@extends('layouts.app')

@section('content')

    <v-subheader>
        Contractor: {{ $contractor->name }}
    </v-subheader>
    @isset($alert)
        <v-alert
            :value="true"
            type="{!! $alert['type'] !!}"
        >
            {!! $alert['text'] !!}
        </v-alert>
    @endisset

    <v-card>
        <v-card-text>
            <users-table route="/admin/contractor/{{ $contractor->id }}"></users-table>
        </v-card-text>
    </v-card>

    <v-subheader>
        <contractor-compliance contractor="{{ $contractor->id }}"></contractor-compliance>
    </v-subheader>


@endsection
