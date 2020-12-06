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

    <v-card>
        <v-card-text>
            <v-layout row>
                <v-flex xs12 md6 offset-md3>
                    <v-form action="/admin/users/{{ $user->id  }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <v-text-field
                                name="first_name"
                                label="First Name"
                                value="{{ $user->first_name  }}"
                                browser-autocomplete="off"
                        ></v-text-field>
                        <v-text-field
                                name="last_name"
                                label="Last Name"
                                value="{{ $user->last_name  }}"
                                browser-autocomplete="off"
                        ></v-text-field>
                        <v-text-field
                                name="email"
                                label="Email"
                                value="{{ $user->email  }}"
                                browser-autocomplete="off"
                        ></v-text-field>
                        <password-gen-field></password-gen-field>
                        <v-btn type="submit" color="primary">Submit</v-btn>
                        <v-btn type="button" color="primary" href="/admin/users/assume/{{$user->id}}">Login as</v-btn>
                        <v-btn type="button" color="primary" href="/api/email/send-verification/{{$user->id}}">Resend Invite</v-btn>
                    </v-form>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>

@endsection
