## Intrukcja uruchamiania


## Docker

Aplikacja jest postawiona na dockerze.
Projekt możemy odpalić za pomocą komendy `docker compose up` lub `./vendor/bin/sail up`.
Aplikacja uruchamia się na porcie `8085`. Ścieżka do api to np. `http://localhost:8085/api/`.
Dla bazy danych jest utworzony adminer na porcie `8086`. Aby się do niego dostać należy wejść na `http://localhost:8086/` i zalogować się danymi ustawionymi w pliku `.env`.

## Migracje i seedery

Aby uruchomić migracje należy wykonać polecenie `./vendor/bin/sail artisan migrate`.
Stworzyłem jeden seeder dla Produktu, który insertuje 5 rekordów do bazy danych.
Aby uruchomić seedery należy wykonać polecenie `./vendor/bin/sail artisan db:seed`.

## Testy

Stworzyłem kilka testów integracyjnych dla enpointów API oraz testy dla całkowitej ceny zamówienia i eventu.
Testy uruchamiają się za pomocą polecenia `./vendor/bin/sail artisan test`.

## Dokumentacja

Dokumentacja Swagger jest dostępna pod adresem `http://localhost:8085/api/documentation`.

