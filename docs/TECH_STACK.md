# FIT AI - Stack Technologiczny

## Spis treści
1. [Architektura aplikacji](#architektura-aplikacji)
2. [Backend](#backend)
3. [Frontend](#frontend)
4. [Baza danych](#baza-danych)
5. [Integracje zewnętrzne](#integracje-zewnętrzne)
6. [Infrastructure & DevOps](#infrastructure--devops)
7. [Narzędzia deweloperskie](#narzędzia-deweloperskie)
8. [Wymagania systemowe](#wymagania-systemowe)

---

## Architektura aplikacji

```
┌─────────────────────────────────────────────────────────────┐
│                         UŻYTKOWNIK                          │
│                      (Przeglądarka PWA)                     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         │ HTTPS
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                         NGINX                               │
│                    (Reverse Proxy)                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                    LARAVEL 11                               │
│                   (PHP 8.3 + FPM)                           │
│                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐    │
│  │   Blade      │  │   Alpine.js  │  │  Tailwind    │    │
│  │  Templates   │  │ (Frontend)   │  │     CSS      │    │
│  └──────────────┘  └──────────────┘  └──────────────┘    │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │              Services Layer                          │ │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────────┐        │ │
│  │  │  Google  │ │ Vertex   │ │ Spoonacular  │        │ │
│  │  │  OAuth   │ │   AI     │ │     API      │        │ │
│  │  └──────────┘ └──────────┘ └──────────────┘        │ │
│  └──────────────────────────────────────────────────────┘ │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                      MySQL 8.0                              │
│                   (Relational DB)                           │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│               ZEWNĘTRZNE SERWISY (API)                      │
│                                                             │
│  Google Cloud     │    Vertex AI      │   Spoonacular      │
│  (OAuth 2.0)      │  (Image Analysis) │  (Recipes & Meals) │
└─────────────────────────────────────────────────────────────┘
```

---

## Backend

### PHP Framework
| Technologia | Wersja | Zastosowanie |
|------------|--------|--------------|
| **Laravel** | 11.x | Główny framework aplikacji |
| **PHP** | 8.3+ | Język programowania backendu |
| **Composer** | 2.x | Menedżer pakietów PHP |

### Kluczowe pakiety Laravel

#### Autentykacja i autoryzacja
- **Laravel Sanctum** - Token-based API authentication
- **Laravel Socialite** - Integracja z Google OAuth 2.0

#### Integracje i API
- **Guzzle HTTP** - HTTP client do komunikacji z zewnętrznymi API
- **Google Cloud PHP SDK** - Oficjalne SDK do Vertex AI
  ```bash
  composer require google/cloud-ai-platform
  ```

#### Pomocnicze
- **Laravel Debugbar** (dev) - Debugging i profiling
- **Laravel IDE Helper** (dev) - Autocomplete dla IDE

### Struktura Service Layer

```php
app/Services/
├── GoogleAuthService.php      // Obsługa OAuth 2.0
├── VertexAIService.php        // Analiza zdjęć lodówki
├── SpoonacularService.php     // Pobieranie przepisów i meal planów
└── MealPlannerService.php     // Logika planowania posiłków
```

---

## Frontend

### Core Technologies
| Technologia | Wersja | Zastosowanie |
|------------|--------|--------------|
| **Blade** | - | Template engine (Laravel native) |
| **Tailwind CSS** | 3.x | Utility-first CSS framework |
| **Alpine.js** | 3.x | Lightweight JavaScript framework |
| **Vite** | 5.x | Frontend build tool |

### Frontend Architecture

#### Blade + Alpine.js + Tailwind
- **Blade Templates** - server-side rendering (SEO-friendly)
- **Alpine.js** - reaktywność i interaktywność (zamiennik Vue/React dla prostych case'ów)
- **Tailwind CSS** - szybkie stylowanie bez pisania CSS

#### Progressive Web App (PWA)
```
public/
├── manifest.json              // PWA manifest
├── service-worker.js          // Offline support
└── icons/
    ├── icon-72x72.png
    ├── icon-96x96.png
    ├── icon-128x128.png
    ├── icon-192x192.png
    └── icon-512x512.png
```

### Package.json - Dependencies
```json
{
  "devDependencies": {
    "@tailwindcss/forms": "^0.5",
    "alpinejs": "^3.13",
    "autoprefixer": "^10.4",
    "laravel-vite-plugin": "^1.0",
    "postcss": "^8.4",
    "tailwindcss": "^3.4",
    "vite": "^5.0"
  },
  "dependencies": {
    "workbox-webpack-plugin": "^7.0"
  }
}
```

---

## Baza danych

### MySQL 8.0

| Komponent | Technologia | Uzasadnienie |
|-----------|-------------|--------------|
| **RDBMS** | MySQL 8.0 | Relacyjna baza danych, stabilna, szeroko wspierana |
| **ORM** | Eloquent (Laravel) | Wbudowany ORM, łatwy w użyciu, migrations |
| **Migracje** | Laravel Migrations | Wersjonowanie struktury bazy danych |

### Struktura tabel

```sql
-- Użytkownicy (autentykacja przez Google)
users
  - id (PK)
  - google_id (unique)
  - name
  - email (unique)
  - avatar
  - created_at
  - updated_at

-- Preferencje żywieniowe użytkownika
user_preferences
  - id (PK)
  - user_id (FK)
  - diet_type (enum: omnivore, vegetarian, vegan, keto, etc.)
  - daily_calories (int, default: 2000)
  - allergies (json)
  - exclude_ingredients (json)
  - created_at
  - updated_at

-- Produkty w lodówce użytkownika
fridge_items
  - id (PK)
  - user_id (FK)
  - product_name
  - quantity
  - unit
  - added_at
  - expires_at (nullable)

-- Plany posiłków
meal_plans
  - id (PK)
  - user_id (FK)
  - date
  - total_calories
  - created_at
  - updated_at

-- Przepisy w planie posiłków
meal_plan_recipes
  - id (PK)
  - meal_plan_id (FK)
  - spoonacular_recipe_id
  - meal_type (enum: breakfast, lunch, dinner, snack)
  - recipe_title
  - calories
  - recipe_data (json)

-- Własne dania użytkownika
custom_dishes
  - id (PK)
  - user_id (FK)
  - title
  - ingredients (json)
  - instructions (text)
  - calories
  - created_at

-- Konfiguracja aplikacji (Vertex AI credentials)
app_settings
  - id (PK)
  - key (unique)
  - value (encrypted text)
  - created_at
  - updated_at
```

---

## Integracje zewnętrzne

### 1. Google Cloud Platform

#### Google OAuth 2.0
- **Zastosowanie:** Logowanie i rejestracja użytkowników
- **Protokół:** OAuth 2.0
- **Scope:** `email`, `profile`
- **Endpoint:** `https://accounts.google.com/o/oauth2/v2/auth`

**Dane wymagane:**
- Client ID
- Client Secret
- Redirect URI

#### Vertex AI (Gemini Vision)
- **Zastosowanie:** Analiza zdjęć lodówki, rozpoznawanie produktów
- **Model:** Gemini 1.5 Pro / Gemini 1.5 Flash
- **API:** Vertex AI Vision API
- **Autentykacja:** Service Account (JSON key file)

**Endpoint przykład:**
```
POST https://aiplatform.googleapis.com/v1/projects/{PROJECT_ID}/locations/{LOCATION}/publishers/google/models/gemini-1.5-pro:generateContent
```

**Funkcjonalności:**
- Multimodal input (text + image)
- Object detection (rozpoznawanie produktów)
- Text extraction (etykiety, daty ważności)

### 2. Spoonacular API

- **Zastosowanie:** Przepisy, wartości odżywcze, planowanie posiłków
- **Wersja:** v1
- **Autentykacja:** API Key
- **Base URL:** `https://api.spoonacular.com`

#### Główne endpointy wykorzystywane w FIT AI:

| Endpoint | Metoda | Zastosowanie |
|----------|--------|--------------|
| `/recipes/findByIngredients` | GET | Wyszukiwanie przepisów po składnikach z lodówki |
| `/recipes/{id}/information` | GET | Szczegóły przepisu + wartości odżywcze |
| `/mealplanner/generate` | GET | Generowanie dziennego planu posiłków |
| `/recipes/complexSearch` | GET | Zaawansowane filtrowanie (diety, alergeny, kalorie) |
| `/food/ingredients/search` | GET | Wyszukiwanie składników |

**Rate Limits:**
- Free tier: 150 requests/day
- Paid plans: od 500 do unlimited

---

## Infrastructure & DevOps

### Docker Compose

```yaml
services:
  # Nginx (Web Server)
  nginx:
    image: nginx:alpine
    ports: 8000:80

  # PHP-FPM (Application)
  app:
    build: ./docker/php
    php: 8.3-fpm
    extensions:
      - pdo_mysql
      - gd
      - zip
      - opcache

  # MySQL (Database)
  db:
    image: mysql:8.0
    ports: 3306:3306
```

### Struktura Docker

```
docker/
├── nginx/
│   └── nginx.conf              // Konfiguracja Nginx
├── php/
│   ├── Dockerfile              // PHP 8.3 + extensions
│   └── php.ini                 // PHP configuration
└── mysql/
    └── my.cnf                  // MySQL configuration
```

### Konfiguracja PHP Extensions

```dockerfile
# docker/php/Dockerfile
FROM php:8.3-fpm

RUN docker-php-ext-install \
    pdo_mysql \
    gd \
    zip \
    opcache \
    bcmath

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Google Cloud SDK (dla Vertex AI)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    && rm -rf /var/lib/apt/lists/*
```

---

## Narzędzia deweloperskie

### Development Tools

| Narzędzie | Zastosowanie |
|-----------|--------------|
| **Laravel Telescope** | Debugging, monitoring requestów |
| **Laravel Pint** | Code formatting (PSR-12) |
| **PHPStan / Larastan** | Static analysis, type checking |
| **Pest / PHPUnit** | Unit & Feature testing |

### IDE Recommendations
- **PhpStorm** - najlepsze dla Laravel
- **VS Code** - z extensionami:
  - Laravel Extension Pack
  - Blade Formatter
  - Tailwind CSS IntelliSense
  - Alpine.js IntelliSense

### Git Workflow
```
main              // Produkcja
├── develop       // Development
├── feature/*     // Nowe funkcjonalności
└── hotfix/*      // Pilne poprawki
```

---

## Wymagania systemowe

### Minimalne wymagania (Development)

| Komponent | Minimalna wersja |
|-----------|------------------|
| Docker | 20.x |
| Docker Compose | 2.x |
| RAM | 4 GB |
| Dysk | 5 GB wolnego miejsca |
| System | Linux / macOS / Windows 10+ (WSL2) |

### Rekomendowane (Development)

| Komponent | Rekomendowana wersja |
|-----------|---------------------|
| Docker | Latest |
| Docker Compose | Latest |
| RAM | 8 GB+ |
| Dysk | 10 GB+ SSD |
| Procesor | 4 rdzenie+ |

### Wymagania produkcyjne

| Komponent | Specyfikacja |
|-----------|--------------|
| CPU | 2+ vCPU |
| RAM | 4 GB+ |
| Dysk | 20 GB+ SSD |
| OS | Ubuntu 22.04 LTS / Debian 11+ |
| PHP | 8.3+ (z OPcache) |
| MySQL | 8.0+ |
| Nginx | 1.24+ |
| SSL | Let's Encrypt (Certbot) |

---

## Koszty i limity zewnętrznych serwisów

### Google Cloud (Vertex AI)

**Gemini 1.5 Flash:**
- Input: $0.00001875 / 1K characters
- Output: $0.000075 / 1K characters
- Images: $0.00001315 / image

**Gemini 1.5 Pro:**
- Input: $0.0003125 / 1K characters
- Output: $0.00125 / 1K characters
- Images: $0.000263 / image

**Free tier:** $300 kredytów na start (3 miesiące)

### Spoonacular API

| Plan | Requests/day | Cena/miesiąc |
|------|--------------|--------------|
| Free | 150 | $0 |
| Basic | 500 | $19 |
| Mega | 5,000 | $79 |
| Ultra | 25,000 | $249 |

### Google OAuth 2.0
- **Koszt:** Darmowy
- **Limit:** Unlimited (w praktyce)

---

## Bezpieczeństwo

### Praktyki bezpieczeństwa

1. **Environment Variables**
   - Wszystkie klucze API w `.env` (nigdy w kodzie)
   - `.env` w `.gitignore`

2. **Szyfrowanie danych wrażliwych**
   - Vertex AI credentials: `encrypt()` w Laravel
   - Dane użytkownika: HTTPS only

3. **Authentication**
   - CSRF protection (Laravel native)
   - Rate limiting na login endpointy
   - Session security (httpOnly cookies)

4. **File Upload**
   - Walidacja typu plików (MIME type)
   - Limit rozmiaru (max 5 MB dla zdjęć)
   - Sanityzacja nazw plików

5. **API Security**
   - API throttling (Laravel rate limiter)
   - Input validation (Form Requests)
   - SQL injection prevention (Eloquent ORM)

---

## Roadmap techniczny

### Faza 1: MVP (Minimal Viable Product)
- Docker setup
- Laravel installation
- Google OAuth login
- Basic UI (Blade + Tailwind + Alpine)
- Vertex AI integration (image analysis)
- Spoonacular integration (recipes)
- Basic meal planning

### Faza 2: Enhancement
- Advanced filtering (allergies, dietary restrictions)
- Shopping list generator
- Recipe favorites & history
- Nutritional tracking dashboard

### Faza 3: Scale
- Multi-language support
- Mobile app (React Native / Flutter)
- Admin panel (user management)
- Analytics & reporting

---

## Linki i zasoby

### Dokumentacja oficjalna
- [Laravel 11](https://laravel.com/docs/11.x)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev/start-here)
- [Vertex AI](https://cloud.google.com/vertex-ai/docs)
- [Spoonacular API](https://spoonacular.com/food-api/docs)
- [Google OAuth 2.0](https://developers.google.com/identity/protocols/oauth2)

### Community Resources
- [Laravel Daily](https://laraveldaily.com/)
- [Laracasts](https://laracasts.com/)
- [Laravel News](https://laravel-news.com/)

---

**Wersja dokumentu:** 1.0
**Data ostatniej aktualizacji:** 2026-01-24
**Autor:** FIT AI Development Team
