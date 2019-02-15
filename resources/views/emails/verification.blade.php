@component('mail::message')
Your email has been used to register on {{ config('app.name') }}.<br>
Kindly enter the verificiation code {{ $email_code }} on the app to confirm ownership.
@endcomponent
