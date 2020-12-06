@extends('layouts.app')

@section('content')

<v-subheader>
    Module
</v-subheader>
<v-card>
    <v-card-text>
        <module-editor id="{{$module->id}}"></module-editor>
    </v-card-text>
</v-card>

@endsection
