# FIT AI - Komendy Makefile

## Spis treści
1. [Wprowadzenie](#wprowadzenie)
2. [Instalacja](#instalacja)
3. [Docker](#docker)
4. [Laravel](#laravel)
5. [Frontend](#frontend)
6. [Baza danych](#baza-danych)
7. [Testing](#testing)
8. [Development](#development)
9. [Deployment](#deployment)
10. [Przykłady użycia](#przykłady-użycia)

---

## Wprowadzenie

Projekt FIT AI wykorzystuje **Makefile** do uproszczenia zarządzania aplikacją. Wszystkie operacje wykonujesz jedną prostą komendą.

### Podstawowe użycie

```bash
# Wyświetl wszystkie dostępne komendy
make help

# Lub po prostu
make
```

---

## Instalacja

### Pierwsza instalacja projektu

```bash
# Pełna instalacja (buduje Docker, instaluje zależności, migruje bazę)
make install
```

**Co robi:**
1. Buduje kontenery Docker
2. Uruchamia kontenery
3. Instaluje zależności Composer
4. Generuje klucz aplikacji Laravel
5. Instaluje zależności NPM
6. Uruchamia migracje bazy danych
7. Buduje assety frontend

**Alternatywa:**
```bash
make setup  # Alias dla make install
```

---

## Docker

### Zarządzanie kontenerami

```bash
# Budowanie kontenerów
make build

# Uruchomienie kontenerów (w tle)
make up

# Zatrzymanie kontenerów
make down

# Restart kontenerów
make restart

# Zatrzymanie bez usuwania
make stop
```

### Logi kontenerów

```bash
# Wszystkie logi (live view)
make logs

# Logi aplikacji Laravel
make logs-app

# Logi serwera Nginx
make logs-nginx

# Logi bazy MySQL
make logs-db
```

### Status kontenerów

```bash
# Pokaż status wszystkich kontenerów
make ps

# Sprawdź status aplikacji
make status

# Sprawdź health aplikacji
make health
```

---

## Laravel

### Composer - Zarządzanie pakietami

```bash
# Instalacja zależności
make composer-install

# Aktualizacja pakietów
make composer-update

# Własna komenda composer
make composer CMD="require package/name"

# Przykład: instalacja pakietu
make composer CMD="require laravel/telescope"
```

### Artisan - Komendy Laravel

```bash
# Generowanie klucza aplikacji
make key-generate

# Wywołanie dowolnej komendy artisan
make artisan CMD="route:list"

# Przykłady:
make artisan CMD="make:controller UserController"
make artisan CMD="make:model Product -m"
make artisan CMD="storage:link"

# Lista wszystkich routów
make routes

# Otwórz Tinker (REPL)
make tinker
```

### Migracje bazy danych

```bash
# Uruchom migracje
make migrate

# Uruchom migracje + seeders
make migrate-seed

# Resetuj bazę i uruchom migracje (USUWA DANE!)
make migrate-fresh

# Resetuj + seeduj (USUWA DANE!)
make fresh

# Cofnij ostatnią migrację
make migrate-rollback

# Tylko seedowanie
make seed
```

### Cache i optymalizacja

```bash
# Wyczyść wszystkie cache
make cache-clear

# Optymalizuj aplikację (cache config, routes, views)
make optimize

# Do użycia przed deploymentem produkcyjnym
```

---

## Frontend

### NPM - Zarządzanie pakietami JavaScript

```bash
# Instalacja zależności
make npm-install

# Aktualizacja pakietów
make npm-update
```

### Budowanie assetów (Vite)

```bash
# Build produkcyjny (zminifikowane)
make npm-build

# Build developerski
make npm-dev

# Watch mode z Hot Module Replacement
make npm-watch
```

---

## Baza danych

### Backup i restore

```bash
# Stwórz backup bazy danych
make db-backup

# Przywróć z backupu
make db-restore FILE=backups/backup_20240124_120000.sql

# Otwórz konsolę MySQL
make db-console
```

**Przykład backup:**
```bash
# Backup jest zapisywany automatycznie w folderze backups/
# Nazwa pliku: backup_YYYYMMDD_HHMMSS.sql

make db-backup
# Utworzy: backups/backup_20240124_153045.sql
```

**Przykład restore:**
```bash
# Przywróć konkretny backup
make db-restore FILE=backups/backup_20240124_153045.sql
```

---

## Testing

### Uruchamianie testów

```bash
# Wszystkie testy
make test

# Tylko testy jednostkowe
make test-unit

# Tylko testy funkcjonalne
make test-feature

# Z pokryciem kodu
make test-coverage
```

### Jakość kodu

```bash
# Sprawdź formatowanie kodu (Pint)
make lint

# Napraw automatycznie formatowanie
make lint-fix

# Static analysis (PHPStan)
make phpstan
```

---

## Development

### Narzędzia deweloperskie

```bash
# Otwórz shell w kontenerze
make shell

# Shell jako root (do naprawy uprawnień)
make shell-root

# Napraw uprawnienia do katalogów
make permissions
```

### Logi aplikacji

```bash
# Obserwuj logi Laravel na żywo
make watch-logs

# Wyczyść pliki logów
make clear-logs
```

### Tryb deweloperski

```bash
# Uruchom tryb dev (up + watch + logs)
make dev
```

To uruchomi:
- Kontenery Docker
- Live reload assetów (Vite)
- Live view logów aplikacji

---

## Deployment

### Wdrożenie produkcyjne

```bash
# Pełne wdrożenie na produkcję
make deploy-prod
```

**Co robi:**
1. Pyta o potwierdzenie
2. Instaluje zależności produkcyjne
3. Uruchamia migracje
4. Optymalizuje aplikację
5. Buduje assety produkcyjne
6. Restartuje kontenery

**Przygotowanie bez deployu:**
```bash
make deploy-prepare
```

---

## Cleanup

### Czyszczenie projektu

```bash
# Wyczyść pliki tymczasowe
make clean

# Usuń kontenery Docker + wolumeny (WSZYSTKO!)
make clean-docker
```

---

## Quick Commands

### Szybkie operacje

```bash
# Szybkie uruchomienie (up + migrate)
make quick-start

# Szybki restart z czyszczeniem cache
make quick-restart
```

---

## Git

### Operacje na repozytorium

```bash
# Status repozytorium
make git-status

# Pobierz zmiany
make git-pull

# Wyślij zmiany
make git-push
```

---

## Info

### Informacje o projekcie

```bash
# Wyświetl informacje o projekcie
make info

# Wersje użytych technologii
make version
```

---

## Przykłady użycia

### Scenariusz 1: Pierwsza instalacja projektu

```bash
# Krok 1: Sklonuj repozytorium
git clone <repository-url> fit-ai
cd fit-ai

# Krok 2: Skopiuj .env.example
cp .env.example .env

# Krok 3: Edytuj .env (ustaw dane do bazy, API keys)
nano .env

# Krok 4: Zainstaluj projekt
make install

# Gotowe! Aplikacja działa na http://localhost:8000
```

### Scenariusz 2: Codzienna praca nad projektem

```bash
# Rano - uruchom projekt
make up

# Włącz tryb deweloperski (live reload)
make dev

# Pracujesz nad kodem...

# Wieczorem - zatrzymaj projekt
make down
```

### Scenariusz 3: Dodanie nowej funkcjonalności

```bash
# Stwórz migrację
make artisan CMD="make:migration create_recipes_table"

# Edytuj migrację...

# Uruchom migrację
make migrate

# Stwórz model
make artisan CMD="make:model Recipe"

# Stwórz kontroler
make artisan CMD="make:controller RecipeController"

# Uruchom testy
make test
```

### Scenariusz 4: Debugging problemu

```bash
# Sprawdź status kontenerów
make ps

# Obejrzyj logi aplikacji
make logs-app

# Sprawdź logi Laravel
make watch-logs

# Otwórz shell w kontenerze
make shell

# Sprawdź routing
make routes

# Otwórz Tinker do testowania
make tinker
```

### Scenariusz 5: Backup przed zmianami

```bash
# Przed dużymi zmianami - zrób backup
make db-backup

# Wykonaj zmiany...
make migrate

# Jeśli coś poszło nie tak - przywróć backup
make db-restore FILE=backups/backup_20240124_153045.sql
```

### Scenariusz 6: Deploy na produkcję

```bash
# Sprawdź status przed deployem
make status

# Uruchom testy
make test

# Deploy na produkcję
make deploy-prod

# Sprawdź czy wszystko działa
make health
```

### Scenariusz 7: Resetowanie projektu

```bash
# Wyczyść bazę i załaduj dane testowe
make fresh

# Lub pełne czyszczenie + reinstalacja
make clean
make install
```

### Scenariusz 8: Problem z uprawnieniami

```bash
# Napraw uprawnienia do katalogów
make permissions

# Wyczyść cache
make cache-clear

# Restart
make restart
```

### Scenariusz 9: Instalacja nowego pakietu

```bash
# Backend (PHP)
make composer CMD="require guzzlehttp/guzzle"

# Frontend (JavaScript)
npm install axios
make npm-build
```

### Scenariusz 10: Monitoring aplikacji

```bash
# Status wszystkiego
make status

# Health check
make health

# Live logi
make logs

# Wersje technologii
make version
```

---

## Najczęstsze problemy i rozwiązania

### Problem: Kontenery nie startują

```bash
# Sprawdź logi
make logs

# Przebuduj kontenery
make down
make build
make up
```

### Problem: Błąd migracji

```bash
# Resetuj bazę
make migrate-fresh

# Jeśli to nie pomaga, sprawdź connection
make db-console
```

### Problem: Assety nie się ładują

```bash
# Przebuduj assety
make npm-build

# Wyczyść cache
make cache-clear
```

### Problem: Błędy uprawnień

```bash
# Napraw uprawnienia
make permissions
```

### Problem: Port 8000 zajęty

Edytuj `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8080:80"  # Zmień 8000 na 8080
```

### Problem: Brak miejsca na dysku

```bash
# Wyczyść Docker
make clean-docker

# Wyczyść cache aplikacji
make clean
```

---

## Tips & Tricks

### Alias dla szybszego użycia

Dodaj do `~/.bashrc` lub `~/.zshrc`:

```bash
alias mi="make install"
alias mu="make up"
alias md="make down"
alias ml="make logs"
alias mt="make test"
alias ms="make shell"
```

### Łączenie komend

```bash
# Można łączyć komendy Make
make down && make build && make up

# Lub tworzyć własne kombinacje
make cache-clear && make restart && make logs-app
```

### Parametry dla komend

Niektóre komendy przyjmują parametry:

```bash
# Artisan z parametrem
make artisan CMD="migrate:status"

# Composer z parametrem
make composer CMD="show --installed"

# Restore z plikiem
make db-restore FILE=backups/backup.sql
```

---

## Cheat Sheet

### Najczęściej używane komendy

| Komenda | Opis |
|---------|------|
| `make help` | Pomoc |
| `make install` | Pierwsza instalacja |
| `make up` | Uruchom aplikację |
| `make down` | Zatrzymaj aplikację |
| `make logs` | Pokaż logi |
| `make shell` | Otwórz terminal |
| `make migrate` | Uruchom migracje |
| `make fresh` | Resetuj bazę + seeduj |
| `make test` | Uruchom testy |
| `make npm-build` | Zbuduj assety |
| `make cache-clear` | Wyczyść cache |
| `make status` | Status aplikacji |

### One-liners dla typowych zadań

```bash
# Restart z czyszczeniem
make cache-clear && make restart

# Fresh start z logami
make fresh && make logs-app

# Pełny rebuild
make down && make build && make up

# Test i deploy
make test && make deploy-prod
```

---

**Wersja dokumentu:** 1.0
**Data ostatniej aktualizacji:** 2026-01-24
