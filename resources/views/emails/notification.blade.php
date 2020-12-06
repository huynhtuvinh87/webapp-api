@extends('emails.layout')

@section('content')

    <p>You've received a note from Contractor Compliance.</p>

    @if($sender)

        <p>From: {{ $sender['first_name'] }} {{ $sender['last_name'] }}</p>

    @endif

    <p><em>{{ $note->message }}</em></p>

    @component('vendor.mail.html.button', ['url' => $note->action[0] === '/' ? config('client.web_ui') . ltrim($note->action, '/'): $note->action])
        {{ $note->action_text }}
    @endcomponent

@endsection
