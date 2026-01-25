# FIT AI - Dokumentacja Wdrożeniowa

## Spis treści
1. [Wymagania wstępne](#wymagania-wstępne)
2. [Konfiguracja środowiska Docker](#konfiguracja-środowiska-docker)
3. [Instalacja i konfiguracja Laravel](#instalacja-i-konfiguracja-laravel)
4. [Konfiguracja Google OAuth](#konfiguracja-google-oauth)
5. [Konfiguracja Vertex AI](#konfiguracja-vertex-ai)
6. [Konfiguracja Spoonacular API](#konfiguracja-spoonacular-api)
7. [Konfiguracja PWA](#konfiguracja-pwa)
8. [Uruchomienie aplikacji](#uruchomienie-aplikacji)
9. [Deployment produkcyjny](#deployment-produkcyjny)

---

## Wymagania wstępne

### Krok 1: Instalacja Docker i Docker Compose
```bash
# Weryfikacja instalacji Docker
docker --version

# Weryfikacja instalacji Docker Compose
docker-compose --version
```

### Krok 2: Klonowanie repozytorium
```bash
git clone <repository-url> fit-ai
cd fit-ai
```

### Krok 3: Przygotowanie pliku środowiskowego
```bash
cp .env.example .env
```

**Podsumowanie:** Masz zainstalowany Docker, sklonowane repozytorium i przygotowany plik `.env` do konfiguracji.

---

## Konfiguracja środowiska Docker

### Krok 1: Sprawdzenie struktury plików Docker
Upewnij się, że masz następujące pliki:
- `docker-compose.yml` - główna konfiguracja kontenerów
- `docker/nginx/nginx.conf` - konfiguracja serwera web
- `docker/php/Dockerfile` - obraz PHP z rozszerzeniami

### Krok 2: Budowanie kontenerów
```bash
docker-compose build
```

### Krok 3: Uruchomienie kontenerów w tle
```bash
docker-compose up -d
```

**Podsumowanie:** Kontenery Docker (Nginx, PHP-FPM, MySQL) są zbudowane i uruchomione. Aplikacja dostępna na `http://localhost:8000`.

---

## Instalacja i konfiguracja Laravel

### Krok 1: Instalacja zależności Composer
```bash
docker-compose exec app composer install
```

### Krok 2: Generowanie klucza aplikacji
```bash
docker-compose exec app php artisan key:generate
```

### Krok 3: Uruchomienie migracji bazy danych
```bash
docker-compose exec app php artisan migrate
```

**Podsumowanie:** Laravel jest skonfigurowany, klucz aplikacji wygenerowany, struktura bazy danych utworzona.

---

## Konfiguracja Google OAuth

### Krok 1: Utworzenie projektu w Google Cloud Console
1. Przejdź do [Google Cloud Console](https://console.cloud.google.com/)
2. Utwórz nowy projekt lub wybierz istniejący
3. Włącz **Google+ API** w sekcji "APIs & Services"

### Krok 2: Konfiguracja OAuth 2.0 Credentials
1. Przejdź do **APIs & Services > Credentials**
2. Kliknij **Create Credentials > OAuth 2.0 Client ID**
3. Wybierz typ aplikacji: **Web application**
4. Dodaj Authorized redirect URIs:
   ```
   http://localhost:8000/auth/google/callback
   https://twoja-domena.pl/auth/google/callback
   ```
5. Skopiuj **Client ID** i **Client Secret**

### Krok 3: Dodanie konfiguracji do .env
```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

**Podsumowanie:** Google OAuth jest skonfigurowany, credentials dodane do `.env`, użytkownicy mogą logować się przez Google.

---

## Konfiguracja Vertex AI

### Krok 1: Utworzenie Service Account w Google Cloud
1. W Google Cloud Console przejdź do **IAM & Admin > Service Accounts**
2. Kliknij **Create Service Account**
3. Nadaj nazwę (np. "fit-ai-vertex")
4. Przypisz rolę: **Vertex AI User**

### Krok 2: Wygenerowanie klucza JSON
1. Kliknij na utworzony Service Account
2. Przejdź do zakładki **Keys**
3. Kliknij **Add Key > Create new key**
4. Wybierz format **JSON**
5. Zapisz pobrany plik (np. `vertex-ai-key.json`)

### Krok 3: Włączenie Vertex AI API
1. W Google Cloud Console przejdź do **APIs & Services**
2. Wyszukaj i włącz **Vertex AI API**
3. Zanotuj **Project ID** z Cloud Console

**Podsumowanie:** Service Account utworzony, klucz JSON pobrany, Vertex AI API włączone. Klucz zostanie wgrany przez panel administracyjny w aplikacji.

---

## Konfiguracja Spoonacular API

### Krok 1: Rejestracja w Spoonacular
1. Przejdź do [Spoonacular](https://spoonacular.com/food-api)
2. Zarejestruj konto (Free tier: 150 zapytań/dzień)
3. Przejdź do **Profile > Show API Key**

### Krok 2: Skopiowanie API Key
Skopiuj wygenerowany klucz API

### Krok 3: Dodanie do .env
```env
SPOONACULAR_API_KEY=your-spoonacular-api-key
```

**Podsumowanie:** Spoonacular API skonfigurowane, klucz dodany do `.env`, aplikacja może pobierać przepisy i plany posiłków.

---

## Konfiguracja PWA

### Krok 1: Instalacja zależności frontend
```bash
npm install
```

### Krok 2: Kompilacja assetów (Tailwind CSS + Alpine.js)
```bash
npm run build
```

### Krok 3: Weryfikacja plików PWA
Sprawdź czy istnieją:
- `public/manifest.json` - manifest PWA
- `public/service-worker.js` - service worker do offline
- Ikony aplikacji w `public/icons/`

**Podsumowanie:** PWA skonfigurowane, assety skompilowane, aplikacja może działać offline i być instalowana na urządzeniach mobilnych.

---

## Uruchomienie aplikacji

### Krok 1: Restart kontenerów (jeśli potrzeba)
```bash
docker-compose down
docker-compose up -d
```

### Krok 2: Sprawdzenie logów
```bash
# Logi wszystkich kontenerów
docker-compose logs -f

# Logi tylko aplikacji Laravel
docker-compose logs -f app
```

### Krok 3: Weryfikacja działania
Otwórz przeglądarkę i przejdź do:
- `http://localhost:8000` - strona główna
- `http://localhost:8000/login` - logowanie przez Google

**Podsumowanie:** Aplikacja działa lokalnie, wszystkie serwisy są uruchomione, możesz testować funkcjonalności.

---

## Deployment produkcyjny

### Krok 1: Konfiguracja zmiennych produkcyjnych
Edytuj `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://twoja-domena.pl

DB_HOST=twoj-db-host
DB_DATABASE=twoja-baza
DB_USERNAME=twoj-user
DB_PASSWORD=twoje-haslo

GOOGLE_REDIRECT_URI=https://twoja-domena.pl/auth/google/callback
```

### Krok 2: Optymalizacja Laravel
```bash
# Cache konfiguracji
docker-compose exec app php artisan config:cache

# Cache routingu
docker-compose exec app php artisan route:cache

# Cache widoków
docker-compose exec app php artisan view:cache

# Optymalizacja autoloadera
docker-compose exec app composer install --optimize-autoloader --no-dev
```

### Krok 3: Konfiguracja HTTPS i domeny
1. Skonfiguruj certyfikat SSL (Let's Encrypt)
2. Zaktualizuj `docker/nginx/nginx.conf` o konfigurację SSL
3. Ustaw przekierowanie HTTP → HTTPS
4. Zaktualizuj Authorized redirect URIs w Google Cloud Console

**Podsumowanie:** Aplikacja jest zoptymalizowana do produkcji, cache'e są wygenerowane, HTTPS skonfigurowane, aplikacja gotowa do uruchomienia.

---

## Panel Administracyjny - Konfiguracja Vertex AI

### Krok 1: Zalogowanie jako administrator
Przejdź do panelu konfiguracji:
```
https://twoja-domena.pl/admin/settings
```

### Krok 2: Upload klucza JSON Vertex AI
1. Kliknij zakładkę **"Konfiguracja"**
2. W sekcji **Vertex AI** kliknij **"Wybierz plik JSON"**
3. Wybierz pobrany wcześniej plik `vertex-ai-key.json`
4. Wprowadź **Project ID** z Google Cloud Console
5. Kliknij **"Zapisz konfigurację"**

### Krok 3: Test połączenia
1. Kliknij przycisk **"Testuj połączenie Vertex AI"**
2. Upload testowe zdjęcie z produktami
3. Sprawdź czy API zwraca rozpoznane produkty

**Podsumowanie:** Vertex AI jest skonfigurowane przez panel admina, credentials zaszyfrowane w bazie danych, aplikacja może analizować zdjęcia lodówek.

---

## Najczęstsze problemy

### Problem: Kontenery nie startują
```bash
# Sprawdź logi
docker-compose logs

# Sprawdź porty (czy 8000, 3306 są wolne)
netstat -tuln | grep -E '8000|3306'
```

### Problem: Błąd migracji bazy danych
```bash
# Reset migracji (UWAGA: usuwa dane!)
docker-compose exec app php artisan migrate:fresh

# Sprawdź połączenie z MySQL
docker-compose exec db mysql -u root -p
```

### Problem: Google OAuth nie działa
- Sprawdź czy Authorized redirect URI są poprawne
- Sprawdź czy Google+ API jest włączone
- Sprawdź czy `GOOGLE_CLIENT_ID` i `GOOGLE_CLIENT_SECRET` są poprawne w `.env`

### Problem: Vertex AI zwraca błędy
- Sprawdź czy plik JSON jest prawidłowy
- Sprawdź czy Project ID jest poprawny
- Sprawdź czy Vertex AI API jest włączone w Google Cloud
- Sprawdź logi: `docker-compose logs app`

---

## Kontakt i wsparcie

- **Dokumentacja Laravel:** https://laravel.com/docs
- **Dokumentacja Vertex AI:** https://cloud.google.com/vertex-ai/docs
- **Dokumentacja Spoonacular:** https://spoonacular.com/food-api/docs
- **Dokumentacja Google OAuth:** https://developers.google.com/identity/protocols/oauth2

---

**Wersja dokumentacji:** 1.0
**Data ostatniej aktualizacji:** 2026-01-24
