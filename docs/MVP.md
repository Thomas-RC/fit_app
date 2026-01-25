# FIT AI - Minimal Viable Product (MVP)

## Spis treści
1. [Definicja MVP](#definicja-mvp)
2. [Cel biznesowy MVP](#cel-biznesowy-mvp)
3. [Funkcjonalności MUST-HAVE](#funkcjonalności-must-have)
4. [Funkcjonalności NICE-TO-HAVE](#funkcjonalności-nice-to-have)
5. [User Stories](#user-stories)
6. [Przepływ użytkownika](#przepływ-użytkownika)
7. [Kryteria akceptacji](#kryteria-akceptacji)
8. [Zakres techniczny MVP](#zakres-techniczny-mvp)
9. [Co NIE wchodzi w MVP](#co-nie-wchodzi-w-mvp)
10. [Metryki sukcesu](#metryki-sukcesu)

---

## Definicja MVP

**MVP (Minimum Viable Product)** dla FIT AI to **najmniejsza wersja aplikacji**, która:
- Rozwiązuje podstawowy problem użytkownika (co ugotować z produktów w lodówce)
- Pozwala na logowanie przez Google
- Wykorzystuje AI do analizy zdjęć lodówki
- Generuje dzienny plan posiłków z limitem kalorii
- Działa jako Progressive Web App (PWA)

**Czas realizacji MVP:** Pojedynczy sprint (IteracjaIteracja podstawowa)

**Target użytkownika:** Osoba świadoma zdrowia, która:
- Ma produkty w lodówce, ale nie wie co ugotować
- Chce kontrolować kalorie
- Szuka szybkich, zdrowych propozycji posiłków

---

## Cel biznesowy MVP

### Główny cel
**Walidacja założenia:** Czy użytkownicy potrzebują narzędzia, które na podstawie zdjęcia lodówki wygeneruje dzienny plan posiłków z kontrolą kalorii?

### Cele szczegółowe
1. Zweryfikować, czy AI poprawnie rozpoznaje produkty na zdjęciu lodówki
2. Sprawdzić, czy integracja z Spoonacular dostarcza wartościowe przepisy
3. Przetestować user experience (logowanie, upload, generowanie planu)
4. Zebrać feedback od pierwszych użytkowników
5. Potwierdzić wykonalność techniczną projektu

---

## Funkcjonalności MUST-HAVE

### 1. Autentykacja użytkownika
- [x] Logowanie przez Google OAuth 2.0
- [x] Automatyczne utworzenie konta przy pierwszym logowaniu
- [x] Wylogowanie użytkownika
- [x] Sesja użytkownika (pozostaje zalogowany)

### 2. Analiza zawartości lodówki (AI)
- [x] Upload zdjęcia lodówki (max 5MB, formaty: JPG, PNG)
- [x] Analiza zdjęcia przez Vertex AI (Gemini Vision)
- [x] Automatyczne rozpoznanie produktów na zdjęciu
- [x] Wyświetlenie listy wykrytych produktów
- [x] Możliwość edycji listy produktów (dodanie/usunięcie)

### 3. Preferencje żywieniowe
- [x] Wybór typu diety:
  - Z mięsem (omnivore)
  - Bez mięsa (vegetarian)
  - Wegańska (vegan)
  - Bez węglowodanów (keto)
- [x] Ustawienie dziennego limitu kalorii (domyślnie: 2000 kcal)
- [x] Zapisanie preferencji użytkownika

### 4. Generowanie planu posiłków
- [x] Wyszukiwanie przepisów na podstawie:
  - Produktów z lodówki
  - Preferencji żywieniowych
  - Limitu kalorii
- [x] Generowanie dziennego planu: śniadanie, obiad, kolacja
- [x] Wyświetlenie planu z:
  - Nazwami dań
  - Zdjęciami dań
  - Wartościami odżywczymi (kalorie)
  - Linkiem do pełnego przepisu

### 5. Szczegóły przepisu
- [x] Wyświetlenie składników
- [x] Wyświetlenie instrukcji krok po kroku
- [x] Wyświetlenie wartości odżywczych (kalorie, białko, tłuszcze, węglowodany)
- [x] Link do źródła przepisu (Spoonacular)

### 6. Progressive Web App (PWA)
- [x] Manifest PWA (możliwość instalacji)
- [x] Responsywny design (mobile-first)
- [x] Basic offline support (cache stron statycznych)

### 7. Panel konfiguracji (Admin)
- [x] Zakładka "Konfiguracja" dla administratora
- [x] Upload pliku JSON dla Vertex AI
- [x] Wprowadzenie Project ID dla Vertex AI
- [x] Test połączenia z Vertex AI

---

## Funkcjonalności NICE-TO-HAVE

### Wersja 1.1 (Post-MVP)
- [ ] **Historia planów posiłków** - zapisywanie poprzednich planów
- [ ] **Ulubione przepisy** - możliwość zapisania przepisu
- [ ] **Wykluczenie składników** - alergeny, produkty nielubiane
- [ ] **Shopping list generator** - lista zakupów na podstawie brakujących składników

### Wersja 1.2
- [ ] **Kreator własnych dań** - możliwość dodania własnego przepisu
- [ ] **Tracking kalorii** - śledzenie zjedzonych posiłków
- [ ] **Dashboard nutricyjny** - wykresy spożycia kalorii, makroskładników
- [ ] **Udostępnianie planów** - eksport PDF lub link do planu

### Wersja 2.0
- [ ] **Aplikacja mobilna** (React Native / Flutter)
- [ ] **Multi-language support** (PL, EN, DE)
- [ ] **Integracja z wearables** (Fitbit, Apple Health)
- [ ] **Social features** - udostępnianie przepisów, komentarze
- [ ] **Premium subscription** - unlimited meal plans, advanced features

---

## User Stories

### US-01: Logowanie użytkownika
```
Jako: Nowy użytkownik
Chcę: Zalogować się przez konto Google
Aby: Szybko rozpocząć korzystanie z aplikacji bez tworzenia nowego konta

Kryteria akceptacji:
- Widoczny przycisk "Zaloguj się przez Google" na stronie głównej
- Po kliknięciu przekierowanie do Google OAuth
- Po autoryzacji automatyczne utworzenie konta
- Przekierowanie do dashboardu użytkownika
```

### US-02: Upload zdjęcia lodówki
```
Jako: Zalogowany użytkownik
Chcę: Zrobić zdjęcie mojej lodówki i przesłać je
Aby: Aplikacja rozpoznała, jakie produkty mam dostępne

Kryteria akceptacji:
- Przycisk "Dodaj zdjęcie lodówki"
- Możliwość wyboru pliku z dysku lub zrobienia zdjęcia (mobile)
- Limit rozmiaru: 5MB
- Obsługa formatów: JPG, PNG
- Loader podczas analizy zdjęcia
- Komunikat o błędzie, jeśli format nieprawidłowy
```

### US-03: Analiza zdjęcia przez AI
```
Jako: Użytkownik, który przesłał zdjęcie
Chcę: Zobaczyć listę rozpoznanych produktów
Aby: Zweryfikować, czy AI poprawnie zidentyfikowało zawartość lodówki

Kryteria akceptacji:
- Lista produktów wyświetlona po analizie (max 10s)
- Możliwość edycji listy (dodanie/usunięcie produktu)
- Przycisk "Dalej" do wyboru preferencji
- Komunikat, jeśli AI nie rozpoznał produktów
```

### US-04: Wybór preferencji żywieniowych
```
Jako: Użytkownik z rozpoznanymi produktami
Chcę: Wybrać swoje preferencje (typ diety, limit kalorii)
Aby: Otrzymać spersonalizowany plan posiłków

Kryteria akceptacji:
- Dropdown z wyborem diety (omnivore, vegetarian, vegan, keto)
- Input do ustawienia limitu kalorii (domyślnie: 2000)
- Przycisk "Generuj plan posiłków"
- Zapisanie preferencji w bazie danych
```

### US-05: Generowanie planu posiłków
```
Jako: Użytkownik z ustawionymi preferencjami
Chcę: Otrzymać dzienny plan posiłków (śniadanie, obiad, kolacja)
Aby: Wiedzieć, co mogę ugotować z moich produktów

Kryteria akceptacji:
- Plan zawiera 3 posiłki (śniadanie, obiad, kolacja)
- Każdy posiłek ma: nazwę, zdjęcie, kalorie
- Suma kalorii nie przekracza limitu (±10%)
- Przepisy wykorzystują produkty z lodówki
- Loader podczas generowania (max 15s)
```

### US-06: Wyświetlenie szczegółów przepisu
```
Jako: Użytkownik przeglądający plan posiłków
Chcę: Kliknąć w danie i zobaczyć pełny przepis
Aby: Poznać składniki i instrukcję przygotowania

Kryteria akceptacji:
- Lista składników z ilościami
- Instrukcja krok po kroku
- Wartości odżywcze (kalorie, białko, tłuszcze, węglowodany)
- Przycisk "Powrót do planu"
- Link do źródła (Spoonacular)
```

---

## Przepływ użytkownika

### Flow 1: Pierwszy raz w aplikacji

```
┌─────────────────────────┐
│   Landing Page          │
│   "Zaloguj przez Google"│
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Google OAuth          │
│   Autoryzacja           │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Dashboard             │
│   "Witaj, [Imię]!"      │
│   "Dodaj zdjęcie        │
│    lodówki"             │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Upload zdjęcia        │
│   [Wybierz plik]        │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Analiza AI            │
│   [Loading...]          │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Lista produktów       │
│   - Jajka              │
│   - Mleko              │
│   - Pomidory           │
│   [Edytuj] [Dalej]     │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Preferencje           │
│   Dieta: [Vegetarian]   │
│   Kalorie: [2000]       │
│   [Generuj plan]        │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Plan posiłków         │
│   Śniadanie: 500kcal   │
│   Obiad: 800kcal       │
│   Kolacja: 700kcal     │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Szczegóły przepisu    │
│   Składniki + Instrukcja│
└─────────────────────────┘
```

### Flow 2: Powracający użytkownik

```
┌─────────────────────────┐
│   Landing Page          │
│   [Automatyczne         │
│    zalogowanie]         │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Dashboard             │
│   "Witaj ponownie!"     │
│   "Dodaj nowe zdjęcie"  │
│   lub                   │
│   "Użyj poprzednich     │
│    produktów"           │
└────────────┬────────────┘
             │
             ▼
         [Flow 1 cd.]
```

---

## Kryteria akceptacji

### Funkcjonalne

| ID | Kryterium | Status |
|----|-----------|--------|
| FA-01 | Użytkownik może zalogować się przez Google | Pending |
| FA-02 | Użytkownik może przesłać zdjęcie lodówki (JPG/PNG, max 5MB) | Pending |
| FA-03 | AI rozpoznaje min. 5 produktów na zdjęciu (accuracy >80%) | Pending |
| FA-04 | Użytkownik może edytować listę produktów | Pending |
| FA-05 | Użytkownik może wybrać typ diety (4 opcje) | Pending |
| FA-06 | Użytkownik może ustawić limit kalorii | Pending |
| FA-07 | System generuje plan 3 posiłków na dzień | Pending |
| FA-08 | Suma kalorii w planie nie przekracza limitu ±10% | Pending |
| FA-09 | Każdy przepis zawiera: składniki, instrukcję, wartości odżywcze | Pending |
| FA-10 | Aplikacja działa jako PWA (możliwość instalacji) | Pending |

### Niefunkcjonalne

| ID | Kryterium | Status |
|----|-----------|--------|
| NF-01 | Czas analizy zdjęcia: max 10 sekund | Pending |
| NF-02 | Czas generowania planu: max 15 sekund | Pending |
| NF-03 | Aplikacja responsive (mobile, tablet, desktop) | Pending |
| NF-04 | Wszystkie formularze mają walidację | Pending |
| NF-05 | Obsługa błędów z komunikatami dla użytkownika | Pending |
| NF-06 | HTTPS only (produkcja) | Pending |
| NF-07 | Dane Vertex AI zaszyfrowane w bazie | Pending |

---

## Zakres techniczny MVP

### Backend (Laravel)

#### Modele (Eloquent)
```php
- User
- UserPreference
- FridgeItem
- MealPlan
- MealPlanRecipe
- AppSetting
```

#### Kontrolery
```php
- AuthController          // Google OAuth
- DashboardController     // Strona główna po logowaniu
- FridgeController        // Upload i analiza zdjęć
- PreferenceController    // Ustawienia użytkownika
- MealPlanController      // Generowanie planów
- RecipeController        // Szczegóły przepisów
- SettingsController      // Panel admina (Vertex AI config)
```

#### Services
```php
- GoogleAuthService       // Logika OAuth
- VertexAIService         // Analiza zdjęć (Gemini Vision)
- SpoonacularService      // Pobieranie przepisów
- MealPlannerService      // Logika planowania posiłków
```

#### Routes
```php
// Autentykacja
GET  /login                      // Strona logowania
GET  /auth/google                // Redirect do Google OAuth
GET  /auth/google/callback       // Callback po autoryzacji
POST /logout                     // Wylogowanie

// Dashboard
GET  /dashboard                  // Strona główna użytkownika

// Lodówka
GET  /fridge                     // Formularz upload zdjęcia
POST /fridge/upload              // Upload i analiza zdjęcia
POST /fridge/products            // Zapisz/edytuj produkty

// Preferencje
GET  /preferences                // Formularz preferencji
POST /preferences                // Zapisz preferencje

// Plan posiłków
POST /meal-plan/generate         // Generuj plan
GET  /meal-plan/{id}             // Wyświetl plan
GET  /recipe/{id}                // Szczegóły przepisu

// Admin
GET  /admin/settings             // Panel konfiguracji
POST /admin/settings/vertex-ai   // Upload Vertex AI JSON
POST /admin/settings/test        // Test połączenia
```

### Frontend (Blade + Alpine.js + Tailwind)

#### Widoki (Blade Templates)
```
resources/views/
├── layouts/
│   ├── app.blade.php           // Główny layout
│   └── guest.blade.php         // Layout dla niezalogowanych
├── auth/
│   └── login.blade.php         // Strona logowania
├── dashboard.blade.php         // Dashboard użytkownika
├── fridge/
│   ├── upload.blade.php        // Upload zdjęcia
│   └── products.blade.php      // Lista produktów (edycja)
├── preferences.blade.php       // Formularz preferencji
├── meal-plan/
│   ├── show.blade.php          // Wyświetlenie planu
│   └── recipe.blade.php        // Szczegóły przepisu
└── admin/
    └── settings.blade.php      // Panel konfiguracji
```

#### Komponenty Alpine.js
```javascript
- imageUploadComponent      // Drag & drop upload
- productListComponent      // Edycja listy produktów
- preferencesFormComponent  // Formularz preferencji
- mealPlanCardComponent     // Karta posiłku w planie
```

### Baza danych (MySQL)

#### Migracje MVP
```
database/migrations/
├── 2024_01_01_000001_create_users_table.php
├── 2024_01_01_000002_create_user_preferences_table.php
├── 2024_01_01_000003_create_fridge_items_table.php
├── 2024_01_01_000004_create_meal_plans_table.php
├── 2024_01_01_000005_create_meal_plan_recipes_table.php
└── 2024_01_01_000006_create_app_settings_table.php
```

### Docker

#### Kontenery MVP
```yaml
services:
  nginx:       // Web server
  app:         // PHP 8.3 + Laravel
  db:          // MySQL 8.0
```

#### Volumes
```
- ./laravel:/var/www/html
- ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
- mysql_data:/var/lib/mysql
```

---

## Co NIE wchodzi w MVP

### Funkcjonalności wykluczone z MVP
- Historia planów posiłków (tylko aktualny plan)
- Ulubione przepisy
- Kreator własnych dań (zakładka pusta w MVP)
- Shopping list generator
- Tracking zjedzonych posiłków
- Dashboard nutricyjny z wykresami
- Udostępnianie planów (PDF, link)
- Multi-language (tylko PL w MVP)
- Aplikacja mobilna natywna
- Integracja z wearables
- Social features
- Premium subscription
- Notifications (email, push)
- Advanced search & filters
- Recipe ratings & reviews

### Ograniczenia techniczne w MVP
- Redis cache (nie jest potrzebny na start)
- Queue system (wszystko synchronicznie)
- Elasticsearch (MySQL wystarczy)
- CDN dla obrazów (local storage)
- Advanced monitoring (Telescope tylko w dev)
- Auto-scaling (pojedynczy serwer)
- Multi-region deployment
- Advanced security (CSRF + HTTPS wystarczy)

---

## Metryki sukcesu

### Metryki techniczne (MVP)

| Metryka | Cel | Pomiar |
|---------|-----|--------|
| Czas analizy zdjęcia | <10s | Vertex AI response time |
| Czas generowania planu | <15s | Spoonacular API response |
| Accuracy rozpoznawania AI | >80% | Manual testing (20 zdjęć) |
| Uptime aplikacji | >95% | Monitoring |
| Mobile responsiveness | 100% | Chrome DevTools (5 devices) |

### Metryki biznesowe (Post-MVP)

| Metryka | Cel | Pomiar |
|---------|-----|--------|
| Liczba rejestracji | 50 użytkowników | Google Analytics |
| Retention rate (7 dni) | >30% | Database query |
| Średnia liczba planów/użytkownik | >3 | Database query |
| User satisfaction | >4/5 | Survey po wygenerowaniu planu |
| Completion rate (end-to-end) | >70% | Funnel analysis |

---

## Testowanie MVP

### Scenariusze testowe

#### Scenariusz 1: Happy Path
1. Użytkownik loguje się przez Google
2. Upload zdjęcia lodówki (zdjęcie z 10 produktami)
3. AI rozpoznaje min. 8/10 produktów
4. Użytkownik edytuje listę (dodaje 1, usuwa 1)
5. Wybiera preferencje (vegetarian, 2000 kcal)
6. Generuje plan posiłków
7. Plan zawiera 3 posiłki, suma <2200 kcal
8. Klika w przepis i widzi szczegóły

#### Scenariusz 2: Error Handling
1. Próba uploadu pliku PDF (błąd: "Nieprawidłowy format")
2. Upload zdjęcia >5MB (błąd: "Plik za duży")
3. Zdjęcie bez produktów (błąd: "Nie wykryto produktów")
4. Błąd Spoonacular API (błąd: "Nie można wygenerować planu")
5. Brak połączenia z Vertex AI (błąd: "Skonfiguruj Vertex AI")

#### Scenariusz 3: Edge Cases
1. Zdjęcie z 1 produktem (plan z minimalnymi składnikami)
2. Limit kalorii 1000 kcal (plan z małymi porcjami)
3. Dieta wegańska + keto (strict filtering)
4. Ponowne generowanie planu (nowe przepisy)

---

## Roadmap MVP → Produkcja

### Sprint 1: Setup & Auth (Foundation)
- Docker Compose setup
- Laravel installation
- MySQL migrations
- Google OAuth integration
- Basic UI (landing page, login)

### Sprint 2: Core Features (AI + Spoonacular)
- Vertex AI integration (image analysis)
- Spoonacular integration (recipes)
- Upload zdjęcia lodówki
- Analiza i wyświetlenie produktów
- Panel admina (Vertex AI config)

### Sprint 3: Meal Planning
- Formularz preferencji
- Logika generowania planu posiłków
- Wyświetlenie planu (3 posiłki)
- Szczegóły przepisu
- Edycja listy produktów

### Sprint 4: Polish & PWA
- Responsywny design (mobile-first)
- PWA configuration (manifest + service worker)
- Error handling & validation
- UI/UX improvements
- Testing & bug fixes

### Sprint 5: Deployment & Testing
- Deployment na serwer produkcyjny
- HTTPS configuration
- User acceptance testing
- Performance optimization
- Dokumentacja użytkownika

---

## Deliverables MVP

### Kod
- Repozytorium Git z pełnym kodem
- Docker Compose configuration
- Laravel application (backend)
- Blade templates + Alpine.js (frontend)
- Migracje bazy danych

### Dokumentacja
- README.md (jak uruchomić projekt)
- DEPLOYMENT.md (instrukcja wdrożeniowa)
- TECH_STACK.md (stack technologiczny)
- MVP.md (ten dokument)
- API_DOCUMENTATION.md (endpointy Laravel)

### Testy
- Manual testing checklist
- Unit tests (Services)
- Feature tests (Controllers)

### Deployment
- Działająca aplikacja na serwerze produkcyjnym
- HTTPS + domena
- Google OAuth production credentials
- Vertex AI production account
- Spoonacular API production key

---

## Następne kroki po MVP

### Faza 1: User Feedback
1. Zbieranie feedbacku od 20-50 użytkowników testowych
2. Analiza metrryk (retention, completion rate)
3. Identyfikacja problemów UX/UI
4. Priorytetyzacja feature requests

### Faza 2: Iteration
1. Poprawki błędów krytycznych
2. Optymalizacja performance
3. Dodanie 1-2 najważniejszych feature'ów (np. historia planów)
4. A/B testing różnych UI flows

### Faza 3: Scale
1. Migracja na lepszą infrastrukturę (load balancer, CDN)
2. Implementacja cache (Redis)
3. Queue system (async processing)
4. Monitoring i alerting (Sentry, New Relic)

### Faza 4: Monetization
1. Analiza możliwości monetyzacji (freemium, ads, premium)
2. Implementacja systemu płatności (Stripe)
3. Premium features (unlimited plans, advanced filters)
4. Marketing i growth hacking

---

**Wersja dokumentu:** 1.0
**Data ostatniej aktualizacji:** 2026-01-24
**Status:** MVP Ready to Build
