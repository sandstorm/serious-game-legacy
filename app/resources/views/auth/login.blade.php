<x-layout>
    <x-slot:title>Anmelden</x-slot:title>
    <x-slot:footer>
        <x-footer.footer/>
    </x-slot:footer>
    <div class="login">
        <div class="login__header">
            <img class="login__logo" src="{{asset('./images/legacy-logo.svg')}}" alt="LeGacy Logo">
            <h1>Hallo bei LeGacy</h1>
            <p class="text-align--center font-size--xl">
                LeGacy vermittelt finanzielle Bildung auf spielerische Weise. Das von der Universität Konstanz
                entwickelte Spiel unterstützt Schülerinnen und Schüler dabei, finanzielle Entscheidungen zu verstehen
                und zu reflektieren und ist gezielt für den Einsatz im Unterricht konzipiert. Weitere Informationen
                finden sich auf der Projektwebseite der Universität Konstanz: <a
                    href="https://www.legacy-finanzbildung.de/" target="_blank">Zum Forschungsprojekt LeGacy</a>
            </p>
        </div>

        <form class="login__form" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-group__label text-align--center" for="soscisurveyId">SoSciSurvey ID</label>
                <input class="form-group__input form__textfield" id="soscisurveyId" type="text" name="soscisurveyId" value="{{ old('soscisurveyId') }}" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-group__label text-align--center" for="password">Passwort</label>
                <input class="form-group__input form__textfield" id="password" type="password" name="password" required>
            </div>
            @error('soscisurveyId') <span class="form-error">{{ $message }}</span> @enderror
            @error('password') <span class="form-error">{{ $message }}</span> @enderror

            <button type="submit" class="button button--type-primary">Anmelden</button>
        </form>

        <a href="/admin" class="font-size--xl">Login für Lehrpersonen</a>
    </div>
</x-layout>
