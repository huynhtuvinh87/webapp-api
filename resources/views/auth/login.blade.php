@extends('layouts.app')

@section('content')
<v-card>
    <v-card-text>
        <v-layout row>
            <v-flex xs12 md6 offset-md3>
                <v-form method="POST" action="{{ route('login') }}">
                    @csrf
                    <v-text-field
                            type="email"
                            name="email"
                            label="email"
                    ></v-text-field>
                    <v-text-field
                            type="password"
                            name="password"
                            label="password"
                    ></v-text-field>
                    <v-btn type="submit" color="primary">
                        Login
                    </v-btn>
                </v-form>
            </v-flex>
        </v-layout>
    </v-card-text>
</v-card>
@endsection
