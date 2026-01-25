# FIT AI - Inteligentny Planer Posiłków 🍽️🤖

> Aplikacja do planowania posiłków z wykorzystaniem AI (Vertex AI Gemini Vision) i API Spoonacular

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker)](https://docker.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

## 📋 Spis treści

- [O projekcie](#-o-projekcie)
- [Funkcjonalności](#-funkcjonalności)
- [Stack technologiczny](#-stack-technologiczny)
- [Wymagania](#-wymagania)
- [Instalacja lokalna](#-instalacja-lokalna)
- [Wdrożenie na VPS](#-wdrożenie-na-vps)
- [Dokumentacja](#-dokumentacja)
- [Komendy make](#-komendy-make)
- [Rozwój projektu](#-rozwój-projektu)

---

## 🚀 O projekcie

**FIT AI** to nowoczesna aplikacja webowa, która pomaga użytkownikom planować zdrowe posiłki na podstawie:
- 📸 **Zdjęcia lodówki** (rozpoznawanie produktów przez Vertex AI)
- 🎯 **Preferencji żywieniowych** (wegetariańskie, wegańskie, bezglutenowe, etc.)
- 📊 **Celów dietetycznych** (utrata wagi, przyrost masy, zdrowy styl życia)
- 🍳 **Przepisów z API Spoonacular** (tysiące przepisów z wartościami odżywczymi)

---

## ✨ Funkcjonalności

### 🔐 Autoryzacja
- **Google OAuth 2.0** - szybkie logowanie przez Google
- Bezpieczne zarządzanie sesjami

### 📸 Analiza lodówki
- **Upload zdjęcia lodówki**
- **Vertex AI (Gemini Vision)** - automatyczne rozpoznawanie produktów
- Ręczna edycja listy składników

### 🍽️ Plany posiłków
- **Generowanie spersonalizowanych planów posiłków**
- Filtrowanie według diety (wegetariańska, wegańska, keto, paleo)
- Limity kaloryczne i makroskładniki
- Przepisy krok po kroku z wartościami odżywczymi

### 📊 Panel użytkownika
- Historia planów posiłków
- Ulubione przepisy
- Lista zakupów

### 📱 PWA (Progressive Web App)
- Instalacja na urządzeniach mobilnych
- Działanie offline
- Powiadomienia push

---

## 🛠️ Stack technologiczny

### Backend
- **Laravel 11** - PHP Framework
- **PHP 8.2+** - Język programowania
- **MySQL 8.0** - Baza danych
- **Laravel Socialite** - Google OAuth

### Frontend
- **Blade Templates** - Silnik szablonów Laravel
- **Alpine.js** - Lekki framework JavaScript
- **Tailwind CSS** - Utility-first CSS
- **Vite** - Build tool

### AI & APIs
- **Google Vertex AI** (Gemini Vision) - Rozpoznawanie produktów na zdjęciach
- **Spoonacular API** - Przepisy i wartości odżywcze

### Infrastruktura
- **Docker** & **Docker Compose** - Konteneryzacja
- **Nginx** - Web server
- **Let's Encrypt** - Certyfikaty SSL

---

## 📦 Wymagania

### Lokalne środowisko deweloperskie
- Docker 20.10+
- Docker Compose 2.0+
- Node.js 20+ i npm
- Git

### Produkcja (VPS)
- Ubuntu 22.04 LTS+
- 2GB RAM minimum (zalecane 4GB)
- 20GB SSD minimum
- Domena i dostęp do DNS

### API Keys (wymagane)
- Google Cloud Console (OAuth + Vertex AI)
- Spoonacular API Key

---

## 🏃 Instalacja lokalna

### Metoda 1: Quick Start (Makefile)

```bash
# Klonuj repozytorium
git clone https://github.com/YOUR_USERNAME/fit-ai.git
cd fit-ai

# Pełna instalacja (build, up, composer, npm, migrate)
make install

# Aplikacja dostępna na: http://localhost:8000
```

### Metoda 2: Manualna instalacja

```bash
# 1. Klonuj repozytorium
git clone https://github.com/YOUR_USERNAME/fit-ai.git
cd fit-ai

# 2. Skopiuj i skonfiguruj .env
cd laravel
cp .env.example .env
nano .env  # Edytuj zmienne środowiskowe

# 3. Zbuduj kontenery Docker
cd ..
docker-compose build

# 4. Uruchom kontenery
docker-compose up -d

# 5. Zainstaluj zależności PHP
docker-compose exec app composer install

# 6. Wygeneruj klucz aplikacji
docker-compose exec app php artisan key:generate

# 7. Uruchom migracje
docker-compose exec app php artisan migrate

# 8. Zainstaluj zależności frontend
cd laravel
npm install
npm run build

# 9. Gotowe!
open http://localhost:8000
```

---

## 🚀 Wdrożenie na VPS

### Quick Start

Kompleksowy przewodnik wdrożenia na VPS OVH (lub inny):

```bash
# 1. Przeczytaj Quick Start
cat docs/deployment/QUICK-START-VPS.md

# 2. Sprawdź checklistę
cat docs/deployment/DEPLOYMENT-CHECKLIST.md

# 3. Pełna dokumentacja
cat docs/deployment/VPS-DEPLOYMENT.md
```

### Automatyczne wdrożenie

Po skonfigurowaniu VPS, użyj skryptu automatycznego wdrożenia:

```bash
# Na VPS
cd /var/www/fit-ai
./deploy.sh
```

Skrypt automatycznie:
- ✅ Tworzy backup bazy danych
- ✅ Pobiera nowe zmiany z Git
- ✅ Buduje i uruchamia kontenery
- ✅ Instaluje zależności
- ✅ Uruchamia migracje
- ✅ Regeneruje cache
- ✅ Restartuje serwisy

---

## 📚 Dokumentacja

| Dokument | Opis |
|----------|------|
| **Deployment** | |
| [docs/deployment/QUICK-START-VPS.md](docs/deployment/QUICK-START-VPS.md) | Szybki przewodnik wdrożenia na VPS (10 kroków) |
| [docs/deployment/VPS-DEPLOYMENT.md](docs/deployment/VPS-DEPLOYMENT.md) | Szczegółowa dokumentacja wdrożenia na VPS |
| [docs/deployment/DEPLOYMENT-CHECKLIST.md](docs/deployment/DEPLOYMENT-CHECKLIST.md) | Kompleksowa checklist przed wdrożeniem |
| [docs/deployment/DEPLOYMENT.md](docs/deployment/DEPLOYMENT.md) | Konfiguracja API (OAuth, Vertex AI, Spoonacular) |
| **Dokumentacja techniczna** | |
| [docs/TECH_STACK.md](docs/TECH_STACK.md) | Stack technologiczny projektu |
| [docs/DATABASE.md](docs/DATABASE.md) | Struktura bazy danych |
| [docs/MAKEFILE_COMMANDS.md](docs/MAKEFILE_COMMANDS.md) | Lista wszystkich komend Makefile |
| [docs/STYLE-GUIDE.md](docs/STYLE-GUIDE.md) | Przewodnik stylu kodu |

---

## 🎯 Komendy make

FIT AI posiada wbudowany **Makefile** z wieloma przydatnymi komendami:

### Podstawowe
```bash
make help              # Wyświetl wszystkie dostępne komendy
make install           # Pełna instalacja projektu
make up                # Uruchom kontenery
make down              # Zatrzymaj kontenery
make restart           # Restart kontenerów
make logs              # Logi wszystkich kontenerów
make status            # Status aplikacji
```

### Laravel
```bash
make migrate           # Uruchom migracje
make migrate-fresh     # Resetuj bazę danych (usuwa dane!)
make seed              # Wypełnij bazę testowymi danymi
make cache-clear       # Wyczyść cache Laravel
make optimize          # Optymalizuj dla produkcji
make tinker            # Laravel Tinker (REPL)
```

### Frontend
```bash
make npm-install       # Zainstaluj zależności npm
make npm-build         # Zbuduj assety produkcyjne
make npm-dev           # Zbuduj assety developerskie
```

### Baza danych
```bash
make db-backup         # Utwórz backup bazy danych
make db-restore        # Przywróć z backupu
make db-console        # Otwórz konsolę MySQL
```

### Deployment
```bash
make deploy-prod       # Wdrożenie produkcyjne (z potwierdzeniem)
make deploy-prepare    # Przygotuj do wdrożenia
```

### Testy i jakość kodu
```bash
make test              # Uruchom wszystkie testy
make lint              # Sprawdź jakość kodu (Pint)
make lint-fix          # Napraw problemy z jakością kodu
```

**Pełna lista:** `make help` lub sprawdź [docs/MAKEFILE_COMMANDS.md](docs/MAKEFILE_COMMANDS.md)

---

## 🔧 Konfiguracja

### Zmienne środowiskowe (.env)

Skopiuj plik `.env.example` do `.env` i skonfiguruj następujące zmienne:

```env
# Aplikacja
APP_URL=http://localhost:8000  # lub https://twoja-domena.pl

# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Vertex AI
GOOGLE_CLOUD_PROJECT_ID=your-project-id

# Spoonacular API
SPOONACULAR_API_KEY=your-spoonacular-api-key

# Baza danych
DB_DATABASE=fit_ai
DB_USERNAME=fit_ai_user
DB_PASSWORD=your-secure-password
```

**Produkcja:** Użyj [docs/deployment/.env.production.example](docs/deployment/.env.production.example) jako szablon.

---

## 🧪 Testy

```bash
# Uruchom wszystkie testy
make test

# Tylko testy jednostkowe
make test-unit

# Tylko testy funkcjonalne
make test-feature

# Z pokryciem kodu
make test-coverage
```

---

## 📊 Monitoring i Backup

### Automatyczne backupy

Skonfiguruj automatyczne backupy bazy danych:

```bash
# Uruchom skrypt konfiguracji
sudo ./setup-backups.sh

# Backupy będą tworzone codziennie o 3:00 AM
# Stare backupy (>7 dni) są automatycznie usuwane
```

### Ręczny backup

```bash
# Utwórz backup
make db-backup

# Przywróć z backupu
make db-restore FILE=backups/backup_20260125.sql
```

---

## 🐛 Rozwiązywanie problemów

### Kontenery nie startują
```bash
docker-compose logs        # Sprawdź logi
docker-compose ps          # Sprawdź status
make restart               # Restart
```

### Błąd 502 Bad Gateway
```bash
make logs-app              # Sprawdź logi aplikacji
make restart               # Restart aplikacji
```

### Problemy z bazą danych
```bash
make db-console            # Otwórz konsolę MySQL
make migrate-fresh         # Resetuj bazę (UWAGA: usuwa dane!)
```

---

## 🤝 Rozwój projektu

### Struktura projektu

```
fit-ai/
├── laravel/                # Kod aplikacji Laravel
│   ├── app/               # Kod PHP (Models, Controllers, Services)
│   ├── resources/         # Frontend (Blade, CSS, JS)
│   ├── routes/            # Routing
│   ├── database/          # Migracje i seeders
│   └── public/            # Publiczne pliki (assety)
├── docker/                # Konfiguracja Docker
│   ├── nginx/            # Konfiguracja Nginx
│   ├── php/              # Dockerfile PHP
│   └── mysql/            # Konfiguracja MySQL
├── docs/                  # Dokumentacja
├── backups/              # Backupy bazy danych
├── docker-compose.yml    # Docker Compose (dev)
├── docker-compose.prod.yml # Docker Compose (prod)
├── Makefile              # Komendy pomocnicze
├── deploy.sh             # Skrypt wdrożenia
└── setup-backups.sh      # Konfiguracja backupów
```

### Git workflow

```bash
# Utwórz nową feature branch
git checkout -b feature/new-feature

# Commituj zmiany
git add .
git commit -m "feat: add new feature"

# Push do remote
git push origin feature/new-feature

# Utwórz Pull Request na GitHub/GitLab
```

---

## 📝 TODO / Roadmap

- [ ] Integracja z więcej API przepisów
- [ ] Wsparcie dla więcej języków
- [ ] Aplikacja mobilna (React Native / Flutter)
- [ ] Współdzielenie planów posiłków
- [ ] Kalkulator BMI i TDEE
- [ ] Integracja z aplikacjami fitness (Strava, MyFitnessPal)

---

## 📄 Licencja

Ten projekt jest licencjonowany na zasadach MIT License.

---

## 👥 Autorzy

- **Twoje Imię** - [GitHub](https://github.com/YOUR_USERNAME)

---

## 🙏 Podziękowania

- [Laravel](https://laravel.com) - PHP Framework
- [Tailwind CSS](https://tailwindcss.com) - CSS Framework
- [Alpine.js](https://alpinejs.dev) - JavaScript Framework
- [Google Vertex AI](https://cloud.google.com/vertex-ai) - AI Platform
- [Spoonacular](https://spoonacular.com) - Recipe API

---

## 📞 Kontakt

- **GitHub Issues:** [Issues](https://github.com/YOUR_USERNAME/fit-ai/issues)
- **Email:** your-email@example.com

---

**Zbudowane z ❤️ przy użyciu Laravel, AI i kawy ☕**
