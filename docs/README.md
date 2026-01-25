# Dokumentacja FIT AI

Witaj w dokumentacji projektu FIT AI - inteligentnego planera posiłków z wykorzystaniem AI.

---

## 📁 Struktura Dokumentacji

### 🚀 [deployment/](deployment/)
**Dokumentacja wdrożenia aplikacji na VPS**

Kompletne przewodniki, checklisty i konfiguracje do wdrożenia aplikacji na produkcji.

**Pliki:**
- [QUICK-START-VPS.md](deployment/QUICK-START-VPS.md) - Szybki start (10 kroków)
- [VPS-DEPLOYMENT.md](deployment/VPS-DEPLOYMENT.md) - Szczegółowy przewodnik
- [DEPLOYMENT-CHECKLIST.md](deployment/DEPLOYMENT-CHECKLIST.md) - Checklist wdrożenia
- [DEPLOYMENT.md](deployment/DEPLOYMENT.md) - Konfiguracja API
- [.env.production.example](deployment/.env.production.example) - Przykładowy .env

👉 **[Przejdź do dokumentacji deployment](deployment/)**

---

### 🛠️ Dokumentacja Techniczna

#### [TECH_STACK.md](TECH_STACK.md)
**Stack technologiczny projektu**
- Backend (Laravel, PHP, MySQL)
- Frontend (Blade, Alpine.js, Tailwind CSS)
- AI & APIs (Vertex AI, Spoonacular)
- Infrastruktura (Docker, Nginx)

#### [DATABASE.md](DATABASE.md)
**Struktura bazy danych**
- Schemat tabel
- Relacje między tabelami
- Migracje

#### [MAKEFILE_COMMANDS.md](MAKEFILE_COMMANDS.md)
**Lista wszystkich komend Makefile**
- Zarządzanie Docker
- Komendy Laravel
- Frontend (npm)
- Testy i jakość kodu
- Deployment

#### [STYLE-GUIDE.md](STYLE-GUIDE.md)
**Przewodnik stylu kodu**
- Konwencje nazewnictwa
- Struktura kodu
- Best practices

#### [MEAL_PLANNER_IMPLEMENTATION_PLAN.md](MEAL_PLANNER_IMPLEMENTATION_PLAN.md)
**Plan implementacji funkcji planera posiłków**

#### [MVP.md](MVP.md)
**Zakres MVP (Minimum Viable Product)**

---

## 🚀 Quick Links

### Dla nowych użytkowników
1. Przeczytaj [../README.md](../README.md) - Główny README projektu
2. Zobacz [TECH_STACK.md](TECH_STACK.md) - Stack technologiczny
3. Przejdź do [deployment/](deployment/) - Instrukcje wdrożenia

### Dla deweloperów
1. [STYLE-GUIDE.md](STYLE-GUIDE.md) - Konwencje kodu
2. [DATABASE.md](DATABASE.md) - Struktura bazy danych
3. [MAKEFILE_COMMANDS.md](MAKEFILE_COMMANDS.md) - Przydatne komendy

### Dla administratorów
1. [deployment/VPS-DEPLOYMENT.md](deployment/VPS-DEPLOYMENT.md) - Wdrożenie
2. [deployment/DEPLOYMENT-CHECKLIST.md](deployment/DEPLOYMENT-CHECKLIST.md) - Checklist
3. [deployment/DEPLOYMENT.md](deployment/DEPLOYMENT.md) - Konfiguracja API

---

## 📖 Jak czytać dokumentację?

### Scenario 1: Pierwszy raz z projektem
```
1. ../README.md (główny README)
   ↓
2. TECH_STACK.md (poznaj technologie)
   ↓
3. deployment/DEPLOYMENT.md (skonfiguruj API keys)
   ↓
4. deployment/VPS-DEPLOYMENT.md (wdróż na VPS)
```

### Scenario 2: Dołączam do zespołu deweloperskiego
```
1. ../README.md (główny README)
   ↓
2. TECH_STACK.md (stack technologiczny)
   ↓
3. STYLE-GUIDE.md (konwencje kodu)
   ↓
4. DATABASE.md (struktura bazy)
   ↓
5. MAKEFILE_COMMANDS.md (komendy pomocnicze)
```

### Scenario 3: Wdrażam aplikację na serwer
```
1. deployment/README.md (indeks dokumentacji deployment)
   ↓
2. deployment/DEPLOYMENT.md (skonfiguruj API)
   ↓
3. deployment/QUICK-START-VPS.md (szybki start)
   LUB
   deployment/VPS-DEPLOYMENT.md (szczegółowy przewodnik)
   ↓
4. deployment/DEPLOYMENT-CHECKLIST.md (sprawdź czy wszystko OK)
```

---

## 🔧 Utrzymanie Dokumentacji

### Zasady aktualizacji

1. **Zawsze aktualizuj dokumentację przy zmianach kodu**
   - Nowa funkcja → zaktualizuj odpowiednią sekcję
   - Zmiana struktury DB → zaktualizuj DATABASE.md
   - Nowa komenda make → zaktualizuj MAKEFILE_COMMANDS.md

2. **Zachowaj spójność**
   - Używaj tych samych terminów w całej dokumentacji
   - Zachowaj jednolity format markdown
   - Dodawaj przykłady kodu

3. **Testuj instrukcje**
   - Upewnij się, że komendy działają
   - Sprawdź czy linki prowadzą do właściwych miejsc
   - Weryfikuj screenshoty (jeśli są)

---

## 📝 Szablon nowego dokumentu

Jeśli tworzysz nowy dokument, użyj tego szablonu:

```markdown
# Tytuł Dokumentu

Krótki opis (1-2 zdania) co zawiera ten dokument.

---

## Spis treści
1. [Sekcja 1](#sekcja-1)
2. [Sekcja 2](#sekcja-2)

---

## Sekcja 1

Treść sekcji...

### Podsekcja 1.1

Treść podsekcji...

---

## Sekcja 2

Treść sekcji...

---

**Wersja:** 1.0
**Data ostatniej aktualizacji:** YYYY-MM-DD
```

---

## 🆘 Potrzebujesz pomocy?

### Dokumentacja nie jest jasna?
Otwórz issue na GitHub z tagiem `documentation`

### Znalazłeś błąd?
Otwórz Pull Request z poprawką

### Brakuje jakiejś informacji?
Otwórz issue z tagiem `documentation-request`

---

**Ostatnia aktualizacja:** 2026-01-25
**Wersja dokumentacji:** 1.0
