# FIT AI - Quick Start VPS Deployment

Szybki przewodnik wdrożenia aplikacji FIT AI na VPS OVH.

## 📋 Przed rozpoczęciem

Upewnij się, że masz:
- ✅ VPS z Ubuntu 22.04+ (minimum 2GB RAM)
- ✅ Zarejestrowaną domenę (np. `fit-ai.pl`)
- ✅ Dostęp SSH do VPS
- ✅ Google OAuth credentials
- ✅ Spoonacular API key
- ✅ Google Cloud Project z Vertex AI

---

## 🚀 Wdrożenie w 10 krokach

### 1️⃣ Połącz się z VPS

```bash
ssh root@YOUR_VPS_IP
```

### 2️⃣ Zainstaluj Docker

```bash
# Aktualizuj system
apt update && apt upgrade -y

# Zainstaluj Docker
curl -fsSL https://get.docker.com | sh

# Zainstaluj Docker Compose
curl -L "https://github.com/docker/compose/releases/download/v2.24.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Sprawdź instalację
docker --version
docker-compose --version
```

### 3️⃣ Skonfiguruj firewall

```bash
apt install -y ufw
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
```

### 4️⃣ Sklonuj repozytorium

```bash
mkdir -p /var/www
cd /var/www
git clone YOUR_REPOSITORY_URL fit-ai
cd fit-ai
```

### 5️⃣ Skonfiguruj domenę w OVH

Dodaj rekordy DNS (w panelu OVH):
- **A Record:** `fit-ai.pl` → `YOUR_VPS_IP`
- **A Record:** `www.fit-ai.pl` → `YOUR_VPS_IP`

Poczekaj 5-30 minut na propagację DNS.

### 6️⃣ Skonfiguruj .env

```bash
cd laravel
cp .env.example .env
nano .env
```

**Edytuj następujące wartości:**

```env
APP_URL=https://fit-ai.pl  # Twoja domena
APP_ENV=production
APP_DEBUG=false

DB_PASSWORD=SILNE_HASLO_123  # Wygeneruj silne hasło

GOOGLE_CLIENT_ID=twoj-client-id
GOOGLE_CLIENT_SECRET=twoj-client-secret
GOOGLE_REDIRECT_URI=https://fit-ai.pl/auth/google/callback

SPOONACULAR_API_KEY=twoj-api-key

GOOGLE_CLOUD_PROJECT_ID=twoj-projekt-id
```

Zapisz: `Ctrl+O`, `Enter`, `Ctrl+X`

### 7️⃣ Zainstaluj SSL (Let's Encrypt)

```bash
# Zainstaluj Certbot
apt install -y certbot

# Wygeneruj certyfikat (zamień fit-ai.pl na swoją domenę)
certbot certonly --standalone -d fit-ai.pl -d www.fit-ai.pl
```

**UWAGA:** Przed uruchomieniem certbot upewnij się, że:
- ✅ Domena wskazuje na VPS (sprawdź: `dig fit-ai.pl`)
- ✅ Port 80 jest wolny (żaden kontener nie działa)

### 8️⃣ Zaktualizuj konfigurację Nginx

Edytuj plik `docker/nginx/nginx.prod.conf` i zamień `fit-ai.pl` na swoją domenę:

```bash
cd /var/www/fit-ai
nano docker/nginx/nginx.prod.conf
```

Zamień wszystkie wystąpienia `fit-ai.pl` na swoją domenę.

### 9️⃣ Uruchom aplikację

```bash
cd /var/www/fit-ai

# Zbuduj i uruchom kontenery
docker-compose -f docker-compose.prod.yml build
docker-compose -f docker-compose.prod.yml up -d

# Zainstaluj zależności
docker-compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader

# Wygeneruj klucz aplikacji
docker-compose -f docker-compose.prod.yml exec app php artisan key:generate

# Uruchom migracje
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Cache konfiguracji
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache

# Napraw uprawnienia
docker-compose -f docker-compose.prod.yml exec -u root app chown -R www-data:www-data storage bootstrap/cache
docker-compose -f docker-compose.prod.yml exec -u root app chmod -R 775 storage bootstrap/cache
```

### 🔟 Zbuduj frontend

```bash
# Zainstaluj Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Zbuduj assety
cd /var/www/fit-ai/laravel
npm install
npm run build
```

---

## ✅ Weryfikacja

Otwórz przeglądarkę i przejdź do:
- `https://fit-ai.pl` - strona główna
- `https://fit-ai.pl/login` - logowanie przez Google

**Sprawdź:**
- ✅ Certyfikat SSL jest ważny (zielona kłódka)
- ✅ Strona się ładuje
- ✅ Logowanie przez Google działa
- ✅ Brak błędów w konsoli (F12)

---

## 🔄 Aktualizacja aplikacji

Po każdej zmianie w kodzie:

```bash
cd /var/www/fit-ai
./deploy.sh
```

Skrypt automatycznie:
- ✅ Tworzy backup bazy danych
- ✅ Pobiera nowe zmiany z Git
- ✅ Buduje nowe kontenery
- ✅ Uruchamia migracje
- ✅ Czyści i regeneruje cache
- ✅ Restartuje serwisy

---

## 📊 Przydatne komendy

```bash
# Status kontenerów
docker-compose -f docker-compose.prod.yml ps

# Logi aplikacji
docker-compose -f docker-compose.prod.yml logs -f app

# Restart kontenerów
docker-compose -f docker-compose.prod.yml restart

# Backup bazy danych
docker-compose -f docker-compose.prod.yml exec -T db mysqldump -u root -proot fit_ai_prod > backup.sql

# Wejście do kontenera
docker-compose -f docker-compose.prod.yml exec app bash
```

---

## 🛟 Pomoc

**Pełna dokumentacja:**
- [VPS-DEPLOYMENT.md](VPS-DEPLOYMENT.md) - Szczegółowy przewodnik
- [DEPLOYMENT.md](DEPLOYMENT.md) - Konfiguracja API i OAuth

**Problemy?**
- Sprawdź logi: `docker-compose -f docker-compose.prod.yml logs`
- Status kontenerów: `docker-compose -f docker-compose.prod.yml ps`
- Weryfikacja DNS: `dig fit-ai.pl`

---

**Powodzenia! 🚀**
