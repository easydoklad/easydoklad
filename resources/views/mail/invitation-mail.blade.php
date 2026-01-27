<x-mail::message>
# Dobrý deň,

{{ $invitation->invitedBy->name }} s vami zdieľa prístup k firme **{{ $invitation->account->company->business_name }}** v službe easyDoklad.

<x-mail::button :url="route('accept-invitation', $invitation->token)">
Akceptovať pozvánku
</x-mail::button>

Pozvánka exspiruje o **{{ config('app.invitation_expiration_hours')  }} hodín**.
</x-mail::message>
