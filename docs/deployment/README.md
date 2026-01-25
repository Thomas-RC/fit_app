# Dokumentacja Wdrożenia FIT AI

Ten katalog zawiera kompletną dokumentację wdrożenia aplikacji FIT AI na serwer VPS.

---

## 📚 Przewodniki Wdrożenia

### 🚀 [QUICK-START-VPS.md](QUICK-START-VPS.md)
**Szybki start - Wdrożenie w 10 krokach**

Idealny dla osób, które chcą szybko wdrożyć aplikację na VPS OVH.

**Zawiera:**
- Instalację Docker na VPS
- Konfigurację domeny i SSL
- Uruchomienie aplikacji
- Podstawowe komendy

**Dla kogo:** Osoby z doświadczeniem w administracji serwerami

---

### 📖 [VPS-DEPLOYMENT.md](VPS-DEPLOYMENT.md)
**Szczegółowy przewodnik wdrożenia**

Kompleksowa, krok po kroku instrukcja wdrożenia aplikacji.

**Zawiera:**
- Przygotowanie VPS (bezpieczeństwo, firewall)
- Instalacja środowiska (Docker, Node.js)
- Konfiguracja domeny DNS
- Instalacja certyfikatu SSL (Let's Encrypt)
- Konfiguracja Nginx z HTTPS
- Uruchomienie produkcyjne
- Monitoring i automatyczne backupy
- Rozwiązywanie problemów

**Dla kogo:** Wszyscy - zarówno początkujący jak i zaawansowani

---

### ✅ [DEPLOYMENT-CHECKLIST.md](DEPLOYMENT-CHECKLIST.md)
**Kompleksowa checklist przed wdrożeniem**

Lista kontrolna wszystkich kroków wdrożenia.

**Zawiera:**
- Checklist infrastruktury (VPS, domena, SSL)
- Checklist konfiguracji (pliki, zmienne środowiskowe)
- Checklist API credentials (Google OAuth, Vertex AI, Spoonacular)
- Checklist bezpieczeństwa
- Checklist testów funkcjonalnych
- Checklist monitoringu i backupów

**Dla kogo:** Wszyscy - używaj jako lista kontrolna podczas wdrożenia

---

### ⚙️ [DEPLOYMENT.md](DEPLOYMENT.md)
**Konfiguracja API i usług zewnętrznych**

Szczegółowa instrukcja konfiguracji wszystkich API i usług.

**Zawiera:**
- Google OAuth 2.0 (logowanie przez Google)
- Google Vertex AI (rozpoznawanie produktów na zdjęciach)
- Spoonacular API (przepisy kulinarne)
- Konfiguracja email (SMTP)
- Konfiguracja PWA

**Dla kogo:** Wszyscy - potrzebne przed pierwszym uruchomieniem

---

### 📋 [STRUKTURA.md](STRUKTURA.md)
**Struktura plików deployment**

Szczegółowy opis struktury katalogów i plików związanych z wdrożeniem.

**Zawiera:**
- Mapę katalogów projektu
- Opis wszystkich plików deployment
- Workflow wdrożenia
- FAQ i konwencje nazewnictwa

**Dla kogo:** Wszyscy - pomocne w nawigacji po projekcie

---

## 📁 Pliki Konfiguracyjne

### [.env.production.example](.env.production.example)
**Przykładowy plik środowiskowy dla produkcji**

Szablon pliku `.env` z wszystkimi wymaganymi zmiennymi dla środowiska produkcyjnego.

**Użycie:**
```bash
cd laravel
cp ../docs/deployment/.env.production.example .env
nano .env  # Edytuj i wypełnij wartości
```

---

## 🔄 Kolejność czytania (dla początkujących)

Jeśli wdrażasz aplikację po raz pierwszy, przeczytaj dokumentację w tej kolejności:

1. **[DEPLOYMENT.md](DEPLOYMENT.md)** - Skonfiguruj API keys (Google OAuth, Vertex AI, Spoonacular)
2. **[VPS-DEPLOYMENT.md](VPS-DEPLOYMENT.md)** - Wykonaj wdrożenie krok po kroku
3. **[DEPLOYMENT-CHECKLIST.md](DEPLOYMENT-CHECKLIST.md)** - Sprawdź czy wszystko zostało wykonane
4. **[QUICK-START-VPS.md](QUICK-START-VPS.md)** - Zachowaj jako quick reference

---

## 🚀 Szybki start (dla zaawansowanych)

Jeśli masz doświadczenie z wdrożeniami:

1. Przeczytaj **[QUICK-START-VPS.md](QUICK-START-VPS.md)**
2. Użyj **[DEPLOYMENT-CHECKLIST.md](DEPLOYMENT-CHECKLIST.md)** jako checklist
3. W razie problemów - **[VPS-DEPLOYMENT.md](VPS-DEPLOYMENT.md)** (sekcja "Rozwiązywanie problemów")

---

## 📦 Skrypty pomocnicze (główny katalog projektu)

Te skrypty znajdują się w głównym katalogu projektu (`/var/www/fit-ai/`):

### `deploy.sh`
**Automatyczne wdrożenie aplikacji**

```bash
cd /var/www/fit-ai
./deploy.sh
```

Automatycznie wykonuje:
- Backup bazy danych
- Git pull
- Build kontenerów Docker
- Instalacja zależności Composer
- Migracje bazy danych
- Cache Laravel
- Restart serwisów

### `setup-backups.sh`
**Konfiguracja automatycznych backupów**

```bash
cd /var/www/fit-ai
sudo ./setup-backups.sh
```

Konfiguruje:
- Skrypt backupu bazy danych
- Zadanie cron (backup codziennie o 3:00 AM)
- Automatyczne usuwanie starych backupów (retencja 7 dni)

---

## 🛠️ Pliki Docker (główny katalog projektu)

### `docker-compose.prod.yml`
Produkcyjna konfiguracja Docker Compose (Nginx, PHP-FPM, MySQL)

### `docker/nginx/nginx.prod.conf`
Konfiguracja Nginx z SSL, HTTPS, security headers

---

## 🆘 Pomoc

### Najczęstsze problemy

**Problem:** Certyfikat SSL nie działa
- **Rozwiązanie:** Zobacz [VPS-DEPLOYMENT.md](VPS-DEPLOYMENT.md) - Sekcja "Konfiguracja SSL"

**Problem:** Google OAuth zwraca błąd
- **Rozwiązanie:** Zobacz [DEPLOYMENT.md](DEPLOYMENT.md) - Sekcja "Google OAuth"

**Problem:** Vertex AI nie rozpoznaje produktów
- **Rozwiązanie:** Zobacz [DEPLOYMENT.md](DEPLOYMENT.md) - Sekcja "Vertex AI"

**Problem:** Kontenery nie startują
- **Rozwiązanie:** Zobacz [VPS-DEPLOYMENT.md](VPS-DEPLOYMENT.md) - Sekcja "Rozwiązywanie problemów"

### Kontakt

Jeśli napotkasz problemy:
1. Sprawdź logi: `docker-compose -f docker-compose.prod.yml logs -f`
2. Zobacz sekcję "Rozwiązywanie problemów" w [VPS-DEPLOYMENT.md](VPS-DEPLOYMENT.md)
3. Otwórz issue na GitHub

---

**Powodzenia z wdrożeniem! 🚀**
