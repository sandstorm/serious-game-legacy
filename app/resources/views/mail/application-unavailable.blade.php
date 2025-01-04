{{--
This template is rendered by {@see RenderApplicationUnavailable} and placed in a location
found by the ingress web server, in case the upstream does not reply (f.e. during deployments / restarts).
 --}}
<!-- to update this file, adjust application-unavailable.blade.php and run "./dev.sh render-application-unavailable" -->
<x-mail::message>
# Tut uns leid – hier klemmt's gerade!

Unsere Seite macht aktuell eine kleine technische Pause. Das kann während einer Wartung oder Aktualisierung passieren.

Keine Sorge, wir sind gleich wieder für Sie da.
Bitte versuchen Sie es in einigen Minuten erneut. Falls das Problem bestehen bleibt, können Sie sich gerne an unseren Support wenden.

<p style="text-align: center">
    <a href="javascript:window.location.reload();" class="button button-primary" rel="noopener">Erneut versuchen</a>
</p>

Vielen Dank für Ihr Verständnis,<br />
Das Team von {{ config('app.name') }}
</x-mail::message>
