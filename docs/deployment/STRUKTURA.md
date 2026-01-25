# Struktura Plików Deployment

Ten dokument opisuje strukturę plików związanych z wdrożeniem aplikacji FIT AI.

---

## 📁 Struktura Katalogów

```
fit-ai/
│
├── docs/
│   └── deployment/           # Dokumentacja wdrożenia
│       ├── README.md        # Indeks dokumentacji deployment
│       ├── QUICK-START-VPS.md
│       ├── VPS-DEPLOYMENT.md
│       ├── DEPLOYMENT-CHECKLIST.md
│       ├── DEPLOYMENT.md
│       ├── .env.production.example
│       ├── .gitignore
│       └── STRUKTURA.md     # Ten plik
│
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf       # Konfiguracja Nginx (development)
│   │   └── nginx.prod.conf  # Konfiguracja Nginx (production z SSL)
│   ├── php/
│   │   └── Dockerfile       # Obraz PHP-FPM
│   └── mysql/
│       └── my.cnf           # Konfiguracja MySQL
│
├── docker-compose.yml       # Docker Compose (development)
├── docker-compose.prod.yml  # Docker Compose (production)
│
├── deploy.sh                # Skrypt automatycznego wdrożenia
├── setup-backups.sh         # Skrypt konfiguracji backupów
│
└── Makefile                 # Komendy pomocnicze
```

---

## 📄 Opis Plików

### Dokumentacja (`docs/deployment/`)

| Plik | Opis |
|------|------|
| `README.md` | Indeks całej dokumentacji deployment |
| `QUICK-START-VPS.md` | Szybki przewodnik wdrożenia (10 kroków) |
| `VPS-DEPLOYMENT.md` | Szczegółowa instrukcja wdrożenia |
| `DEPLOYMENT-CHECKLIST.md` | Lista kontrolna wdrożenia |
| `DEPLOYMENT.md` | Konfiguracja API (OAuth, Vertex AI, Spoonacular) |
| `.env.production.example` | Przykładowy plik .env dla produkcji |
| `.gitignore` | Ignorowanie plików wrażliwych |

### Konfiguracja Docker

| Plik | Opis |
|------|------|
| `docker-compose.yml` | Środowisko deweloperskie (localhost:8000) |
| `docker-compose.prod.yml` | Środowisko produkcyjne (z SSL) |
| `docker/nginx/nginx.conf` | Nginx bez SSL (development) |
| `docker/nginx/nginx.prod.conf` | Nginx z SSL (production) |
| `docker/php/Dockerfile` | Obraz PHP 8.2 z rozszerzeniami |

### Skrypty

| Plik | Opis | Użycie |
|------|------|--------|
| `deploy.sh` | Automatyczne wdrożenie | `./deploy.sh` |
| `setup-backups.sh` | Konfiguracja backupów | `sudo ./setup-backups.sh` |
| `Makefile` | Komendy pomocnicze | `make help` |

---

## 🔄 Workflow Wdrożenia

### 1. Przygotowanie (lokalne)
```bash
# 1. Sprawdź dokumentację
cat docs/deployment/README.md

# 2. Przygotuj credentials (Google OAuth, Vertex AI, Spoonacular)
# - Skonfiguruj w Google Cloud Console
# - Zapisz klucze w bezpiecznym miejscu
```

### 2. Konfiguracja VPS (remote)
```bash
# Na VPS: Zainstaluj Docker, skonfiguruj firewall
# Instrukcje: docs/deployment/VPS-DEPLOYMENT.md
```

### 3. Deployment (remote)
```bash
# Na VPS: Sklonuj repo i wdróż
cd /var/www/fit-ai

# Użyj skryptu deploy
./deploy.sh
```

### 4. Weryfikacja
```bash
# Sprawdź checklistę
cat docs/deployment/DEPLOYMENT-CHECKLIST.md
```

---

## 🎯 Gdzie znajdę...?

### "Jak wdrożyć aplikację na VPS?"
→ [docs/deployment/VPS-DEPLOYMENT.md](VPS-DEPLOYMENT.md)

### "Szybki start wdrożenia?"
→ [docs/deployment/QUICK-START-VPS.md](QUICK-START-VPS.md)

### "Checklist wdrożenia?"
→ [docs/deployment/DEPLOYMENT-CHECKLIST.md](DEPLOYMENT-CHECKLIST.md)

### "Jak skonfigurować Google OAuth?"
→ [docs/deployment/DEPLOYMENT.md](DEPLOYMENT.md) - Sekcja "Google OAuth"

### "Jak skonfigurować Vertex AI?"
→ [docs/deployment/DEPLOYMENT.md](DEPLOYMENT.md) - Sekcja "Vertex AI"

### "Przykładowy plik .env dla produkcji?"
→ [docs/deployment/.env.production.example](.env.production.example)

### "Konfiguracja Nginx z SSL?"
→ `docker/nginx/nginx.prod.conf`

### "Automatyczne wdrożenie?"
→ `./deploy.sh` (główny katalog)

### "Automatyczne backupy?"
→ `./setup-backups.sh` (główny katalog)

---

## 🔐 Bezpieczeństwo

### Pliki wrażliwe (NIE commituj!)

❌ **Nigdy nie commituj:**
- `laravel/.env` - zawiera hasła i API keys
- Prawdziwe pliki credentials (JSON od Google)
- Pliki backupów bazy danych

✅ **Commituj tylko:**
- `.env.example` (bez wartości)
- `.env.production.example` (bez wartości)
- Dokumentację
- Skrypty (bez credentials)

### `.gitignore` lokalizacje

1. **Główny `.gitignore`** - ignoruje:
   - `laravel/.env`
   - `backups/*.sql`
   - `laravel/vendor/`
   - `laravel/node_modules/`

2. **`docs/deployment/.gitignore`** - ignoruje:
   - `.env` (w tym katalogu)
   - Pliki backup (`.bak`)

---

## 📋 Konwencje nazewnictwa

### Pliki dokumentacji
- `NAZWA-PLIKU.md` - wielkie litery, myślniki
- `README.md` - zawsze wielkie litery
- `.env.production.example` - małe litery

### Pliki konfiguracyjne
- `nginx.conf` - development
- `nginx.prod.conf` - production
- `docker-compose.yml` - development
- `docker-compose.prod.yml` - production

### Skrypty
- `deploy.sh` - małe litery, myślniki
- `setup-backups.sh` - małe litery, myślniki

---

## 🆘 FAQ

**Q: Gdzie są pliki deployment?**
A: W katalogu `docs/deployment/`

**Q: Czy mogę wdrożyć bez czytania dokumentacji?**
A: Nie zalecane. Przeczytaj przynajmniej `QUICK-START-VPS.md`

**Q: Czy plik .env jest commitowany?**
A: NIE! Tylko `.env.example` i `.env.production.example`

**Q: Gdzie są skrypty wdrożenia?**
A: `deploy.sh` i `setup-backups.sh` w głównym katalogu

**Q: Jaka jest różnica między nginx.conf a nginx.prod.conf?**
A: `nginx.conf` - bez SSL (dev), `nginx.prod.conf` - z SSL (prod)

---

**Ostatnia aktualizacja:** 2026-01-25
