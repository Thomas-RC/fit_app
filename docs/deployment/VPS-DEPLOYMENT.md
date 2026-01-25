# FIT AI - Wdrożenie na VPS OVH

## Spis treści
1. [Wymagania](#wymagania)
2. [Przygotowanie VPS](#przygotowanie-vps)
3. [Instalacja środowiska](#instalacja-środowiska)
4. [Konfiguracja domeny](#konfiguracja-domeny)
5. [Wdrożenie aplikacji](#wdrożenie-aplikacji)
6. [Konfiguracja SSL](#konfiguracja-ssl)
7. [Uruchomienie produkcyjne](#uruchomienie-produkcyjne)
8. [Monitoring i backup](#monitoring-i-backup)
9. [Rozwiązywanie problemów](#rozwiązywanie-problemów)

---

## Wymagania

### VPS
- **System:** Ubuntu 22.04 LTS lub nowszy
- **RAM:** Minimum 2GB (zalecane 4GB)
- **Dysk:** Minimum 20GB SSD
- **CPU:** 2 rdzenie lub więcej

### Domena
- Zarejestrowana domena (np. `fit-ai.pl`)
- Dostęp do ustawień DNS

### Lokalne
- SSH client (Windows: PuTTY, Linux/Mac: terminal)
- Git zainstalowany lokalnie

---

## Przygotowanie VPS

### Krok 1: Połączenie z VPS przez SSH

```bash
# Połącz się z VPS (zastąp YOUR_VPS_IP swoim adresem IP)
ssh root@YOUR_VPS_IP

# Przy pierwszym połączeniu potwierdź fingerprint: yes
```

### Krok 2: Aktualizacja systemu

```bash
# Aktualizuj listę pakietów
apt update && apt upgrade -y

# Zainstaluj podstawowe narzędzia
apt install -y curl wget git unzip vim ufw fail2ban
```

### Krok 3: Konfiguracja firewalla

```bash
# Zezwól na SSH, HTTP i HTTPS
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp

# Włącz firewall
ufw --force enable

# Sprawdź status
ufw status
```

### Krok 4: Utworzenie użytkownika (opcjonalne, ale zalecane)

```bash
# Utwórz nowego użytkownika
adduser deploy

# Dodaj do grupy sudo
usermod -aG sudo deploy

# Dodaj do grupy docker (będzie utworzona później)
usermod -aG docker deploy

# Przełącz się na nowego użytkownika
su - deploy
```

**Podsumowanie:** VPS zaktualizowany, firewall skonfigurowany, użytkownik deploy utworzony.

---

## Instalacja środowiska

### Krok 1: Instalacja Docker

```bash
# Usuń stare wersje Docker (jeśli są)
sudo apt remove docker docker-engine docker.io containerd runc

# Zainstaluj wymagane pakiety
sudo apt install -y ca-certificates curl gnupg lsb-release

# Dodaj oficjalny klucz GPG Docker
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Dodaj repozytorium Docker
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Zainstaluj Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Sprawdź instalację
docker --version
docker compose version
```

### Krok 2: Instalacja Docker Compose (standalone)

```bash
# Pobierz Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.24.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose

# Nadaj uprawnienia
sudo chmod +x /usr/local/bin/docker-compose

# Sprawdź instalację
docker-compose --version
```

### Krok 3: Konfiguracja Docker dla użytkownika deploy

```bash
# Dodaj użytkownika do grupy docker
sudo usermod -aG docker $USER

# Zaloguj się ponownie lub uruchom
newgrp docker

# Sprawdź czy działa bez sudo
docker ps
```

**Podsumowanie:** Docker i Docker Compose zainstalowane, użytkownik może używać Docker bez sudo.

---

## Konfiguracja domeny

### Krok 1: Konfiguracja DNS w panelu OVH

Zaloguj się do panelu OVH i dodaj następujące rekordy DNS:

| Typ   | Subdomena | Wartość (Target)  | TTL  |
|-------|-----------|-------------------|------|
| A     | @         | YOUR_VPS_IP       | 300  |
| A     | www       | YOUR_VPS_IP       | 300  |

**Przykład:**
- `fit-ai.pl` → `51.83.123.45`
- `www.fit-ai.pl` → `51.83.123.45`

### Krok 2: Weryfikacja propagacji DNS

```bash
# Sprawdź czy domena wskazuje na VPS (może zająć do 24h)
dig fit-ai.pl +short
nslookup fit-ai.pl

# Lub użyj online: https://dnschecker.org
```

**Podsumowanie:** Domena skonfigurowana i wskazuje na VPS.

---

## Wdrożenie aplikacji

### Krok 1: Utworzenie struktury katalogów

```bash
# Utwórz katalog dla aplikacji
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www
cd /var/www
```

### Krok 2: Klonowanie repozytorium

```bash
# Jeśli repozytorium jest publiczne:
git clone https://github.com/YOUR_USERNAME/fit-ai.git

# Jeśli repozytorium jest prywatne, wygeneruj SSH key:
ssh-keygen -t ed25519 -C "deploy@fit-ai"
cat ~/.ssh/id_ed25519.pub
# Skopiuj klucz i dodaj jako Deploy Key w GitHub/GitLab

# Następnie sklonuj przez SSH:
git clone git@github.com:YOUR_USERNAME/fit-ai.git

# Przejdź do katalogu
cd fit-ai
```

### Krok 3: Konfiguracja pliku .env

```bash
# Skopiuj przykładowy plik produkcyjny .env
cd laravel
cp ../docs/deployment/.env.production.example .env

# Edytuj plik .env
nano .env
```

**Ustaw następujące zmienne:**

```env
# === APLIKACJA ===
APP_NAME="FIT AI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://fit-ai.pl

# === BAZA DANYCH ===
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=fit_ai_prod
DB_USERNAME=fit_ai_user
DB_PASSWORD=WYGENERUJ_SILNE_HASLO_123

# === GOOGLE OAUTH ===
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://fit-ai.pl/auth/google/callback

# === SPOONACULAR API ===
SPOONACULAR_API_KEY=your-spoonacular-api-key

# === MAIL (opcjonalne) ===
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@fit-ai.pl
MAIL_FROM_NAME="${APP_NAME}"

# === SESSION & CACHE ===
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# === LOGS ===
LOG_CHANNEL=daily
LOG_LEVEL=error
```

**Zapisz plik:** `Ctrl+O`, `Enter`, `Ctrl+X`

### Krok 4: Utworzenie docker-compose.prod.yml

Wróć do głównego katalogu projektu:

```bash
cd /var/www/fit-ai
```

Plik `docker-compose.prod.yml` został już utworzony (patrz niżej w sekcji plików).

### Krok 5: Budowanie i uruchomienie kontenerów

```bash
# Zbuduj kontenery produkcyjne
docker-compose -f docker-compose.prod.yml build

# Uruchom w tle
docker-compose -f docker-compose.prod.yml up -d

# Sprawdź status
docker-compose -f docker-compose.prod.yml ps
```

### Krok 6: Instalacja zależności i konfiguracja Laravel

```bash
# Zainstaluj zależności Composer
docker-compose -f docker-compose.prod.yml exec app composer install --optimize-autoloader --no-dev

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

### Krok 7: Instalacja i budowanie frontendu (na VPS)

```bash
# Zainstaluj Node.js i npm (jeśli nie masz)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Przejdź do katalogu Laravel
cd /var/www/fit-ai/laravel

# Zainstaluj zależności
npm install

# Zbuduj assety produkcyjne
npm run build

# Sprawdź czy pliki zostały zbudowane
ls -la public/build/
```

**Podsumowanie:** Aplikacja wdrożona, kontenery działają, Laravel skonfigurowany.

---

## Konfiguracja SSL

### Krok 1: Instalacja Certbot (Let's Encrypt)

```bash
# Zainstaluj Certbot
sudo apt install -y certbot

# Zatrzymaj tymczasowo Nginx (jeśli działa)
docker-compose -f docker-compose.prod.yml stop nginx
```

### Krok 2: Generowanie certyfikatu SSL

```bash
# Wygeneruj certyfikat (zastąp domeną)
sudo certbot certonly --standalone -d fit-ai.pl -d www.fit-ai.pl

# Certbot zapyta o email - podaj swój email
# Akceptuj Terms of Service: Yes
# Czy chcesz otrzymywać newsletter: No (opcjonalnie)

# Certyfikaty zostaną zapisane w:
# /etc/letsencrypt/live/fit-ai.pl/fullchain.pem
# /etc/letsencrypt/live/fit-ai.pl/privkey.pem
```

### Krok 3: Konfiguracja automatycznego odnowienia

```bash
# Test automatycznego odnowienia
sudo certbot renew --dry-run

# Certbot automatycznie doda zadanie cron do odnowienia
# Sprawdź: sudo systemctl status certbot.timer
```

### Krok 4: Aktualizacja konfiguracji Nginx

Produkcyjna konfiguracja Nginx z SSL została już przygotowana w pliku `docker/nginx/nginx.prod.conf` (patrz niżej).

### Krok 5: Aktualizacja docker-compose.prod.yml

Upewnij się, że `docker-compose.prod.yml` montuje certyfikaty SSL:

```yaml
nginx:
  volumes:
    - /etc/letsencrypt:/etc/letsencrypt:ro
```

### Krok 6: Restart Nginx

```bash
# Uruchom ponownie Nginx z nową konfiguracją
docker-compose -f docker-compose.prod.yml up -d nginx

# Sprawdź logi
docker-compose -f docker-compose.prod.yml logs nginx
```

**Podsumowanie:** SSL skonfigurowany, certyfikat Let's Encrypt zainstalowany, HTTPS działa.

---

## Uruchomienie produkcyjne

### Krok 1: Restart wszystkich kontenerów

```bash
cd /var/www/fit-ai
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml up -d
```

### Krok 2: Weryfikacja działania

```bash
# Sprawdź czy wszystkie kontenery działają
docker-compose -f docker-compose.prod.yml ps

# Sprawdź logi
docker-compose -f docker-compose.prod.yml logs -f

# Test HTTP (przekierowanie do HTTPS)
curl -I http://fit-ai.pl

# Test HTTPS
curl -I https://fit-ai.pl
```

### Krok 3: Weryfikacja w przeglądarce

Otwórz przeglądarkę i przejdź do:
- `https://fit-ai.pl` - strona główna
- `https://fit-ai.pl/login` - logowanie przez Google

**Sprawdź:**
- ✅ Certyfikat SSL jest ważny (ikona kłódki w pasku adresu)
- ✅ Strona się ładuje
- ✅ Logowanie przez Google działa
- ✅ Brak błędów w konsoli przeglądarki (F12)

**Podsumowanie:** Aplikacja działa na produkcji z HTTPS!

---

## Monitoring i backup

### Krok 1: Konfiguracja automatycznego backupu bazy danych

```bash
# Utwórz katalog na backupy
mkdir -p /var/www/fit-ai/backups

# Utwórz skrypt backup
sudo nano /usr/local/bin/backup-fitai-db.sh
```

**Wklej zawartość:**

```bash
#!/bin/bash
# Backup bazy danych FIT AI

BACKUP_DIR="/var/www/fit-ai/backups"
DATE=$(date +%Y%m%d_%H%M%S)
COMPOSE_FILE="/var/www/fit-ai/docker-compose.prod.yml"

# Utwórz backup
docker-compose -f $COMPOSE_FILE exec -T db mysqldump -u root -proot fit_ai_prod > $BACKUP_DIR/backup_$DATE.sql

# Kompresuj
gzip $BACKUP_DIR/backup_$DATE.sql

# Usuń backupy starsze niż 7 dni
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup created: backup_$DATE.sql.gz"
```

**Zapisz i nadaj uprawnienia:**

```bash
sudo chmod +x /usr/local/bin/backup-fitai-db.sh
```

**Dodaj zadanie cron (codziennie o 3:00 rano):**

```bash
crontab -e

# Dodaj linię:
0 3 * * * /usr/local/bin/backup-fitai-db.sh >> /var/log/fitai-backup.log 2>&1
```

### Krok 2: Monitoring logów

```bash
# Obejrzyj logi aplikacji
docker-compose -f docker-compose.prod.yml logs -f app

# Obejrzyj logi Nginx
docker-compose -f docker-compose.prod.yml logs -f nginx

# Logi Laravel (w kontenerze)
docker-compose -f docker-compose.prod.yml exec app tail -f storage/logs/laravel.log
```

### Krok 3: Monitoring zasobów

```bash
# Użycie zasobów przez kontenery
docker stats

# Użycie dysku
df -h

# Użycie pamięci
free -h

# Procesy
htop
```

**Podsumowanie:** Automatyczne backupy skonfigurowane, monitoring logów i zasobów dostępny.

---

## Rozwiązywanie problemów

### Problem 1: Kontenery nie startują

```bash
# Sprawdź logi
docker-compose -f docker-compose.prod.yml logs

# Sprawdź status
docker-compose -f docker-compose.prod.yml ps

# Restart
docker-compose -f docker-compose.prod.yml restart
```

### Problem 2: Błąd 502 Bad Gateway

**Przyczyna:** PHP-FPM nie odpowiada

```bash
# Sprawdź logi aplikacji
docker-compose -f docker-compose.prod.yml logs app

# Restart aplikacji
docker-compose -f docker-compose.prod.yml restart app
```

### Problem 3: Błąd połączenia z bazą danych

```bash
# Sprawdź czy MySQL działa
docker-compose -f docker-compose.prod.yml ps db

# Sprawdź logi bazy
docker-compose -f docker-compose.prod.yml logs db

# Testuj połączenie
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
# W Tinker: DB::connection()->getPdo();
```

### Problem 4: SSL nie działa

```bash
# Sprawdź certyfikaty
sudo certbot certificates

# Odnów certyfikat ręcznie
sudo certbot renew

# Sprawdź konfigurację Nginx
docker-compose -f docker-compose.prod.yml exec nginx nginx -t
```

### Problem 5: Brak miejsca na dysku

```bash
# Sprawdź użycie
df -h

# Wyczyść stare obrazy Docker
docker system prune -a

# Wyczyść logi Laravel
docker-compose -f docker-compose.prod.yml exec app truncate -s 0 storage/logs/laravel.log
```

### Problem 6: Wysokie użycie CPU/RAM

```bash
# Sprawdź które kontenery zużywają zasoby
docker stats

# Restart kontenerów
docker-compose -f docker-compose.prod.yml restart

# Optymalizuj Laravel cache
docker-compose -f docker-compose.prod.yml exec app php artisan optimize:clear
docker-compose -f docker-compose.prod.yml exec app php artisan optimize
```

---

## Aktualizacja aplikacji

### Metoda 1: Ręczna aktualizacja

```bash
cd /var/www/fit-ai

# Pobierz nowe zmiany
git pull origin main

# Buduj nowe kontenery
docker-compose -f docker-compose.prod.yml build

# Zatrzymaj stare kontenery
docker-compose -f docker-compose.prod.yml down

# Uruchom nowe
docker-compose -f docker-compose.prod.yml up -d

# Zainstaluj nowe zależności
docker-compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader

# Uruchom migracje
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Odśwież cache
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache

# Zbuduj nowe assety (jeśli były zmiany w frontend)
cd laravel
npm install
npm run build
```

### Metoda 2: Automatyczna aktualizacja (deploy script)

Użyj skryptu `deploy.sh` (opisany w następnej sekcji).

---

## Przydatne komendy

```bash
# Status wszystkich kontenerów
docker-compose -f docker-compose.prod.yml ps

# Restart wszystkich kontenerów
docker-compose -f docker-compose.prod.yml restart

# Restart pojedynczego kontenera
docker-compose -f docker-compose.prod.yml restart nginx

# Logi wszystkich kontenerów
docker-compose -f docker-compose.prod.yml logs -f

# Logi pojedynczego kontenera
docker-compose -f docker-compose.prod.yml logs -f app

# Wejście do kontenera aplikacji
docker-compose -f docker-compose.prod.yml exec app bash

# Wejście do kontenera MySQL
docker-compose -f docker-compose.prod.yml exec db mysql -u root -proot fit_ai_prod

# Artisan commands
docker-compose -f docker-compose.prod.yml exec app php artisan [command]

# Backup bazy danych
docker-compose -f docker-compose.prod.yml exec -T db mysqldump -u root -proot fit_ai_prod > backup.sql

# Restore bazy danych
docker-compose -f docker-compose.prod.yml exec -T db mysql -u root -proot fit_ai_prod < backup.sql

# Czyszczenie cache Laravel
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec app php artisan route:clear
docker-compose -f docker-compose.prod.yml exec app php artisan view:clear
```

---

## Bezpieczeństwo

### 1. Zmień domyślne hasła
- Hasło root do MySQL (w `.env` i `docker-compose.prod.yml`)
- Hasła użytkowników w panelu admina

### 2. Konfiguracja fail2ban (ochrona przed brute-force)

```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 3. Automatyczne aktualizacje bezpieczeństwa

```bash
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

### 4. Backup poza VPS
- Skopiuj backupy na lokalny komputer lub cloud storage
- Użyj `rsync` lub `scp` do automatyzacji

```bash
# Przykład: backup na lokalny komputer
rsync -avz deploy@YOUR_VPS_IP:/var/www/fit-ai/backups/ ./local-backups/
```

---

## Checklist przed uruchomieniem produkcyjnym

- [ ] VPS zaktualizowany i zabezpieczony
- [ ] Firewall skonfigurowany (UFW)
- [ ] Docker i Docker Compose zainstalowane
- [ ] Domena skonfigurowana i wskazuje na VPS
- [ ] Certyfikat SSL zainstalowany i odnawia się automatycznie
- [ ] `.env` skonfigurowany z danymi produkcyjnymi
- [ ] `APP_DEBUG=false` w `.env`
- [ ] Google OAuth skonfigurowany z prawidłowym redirect URI
- [ ] Vertex AI credentials skonfigurowane
- [ ] Spoonacular API key ustawiony
- [ ] Baza danych utworzona i zmigrowana
- [ ] Assety frontend zbudowane (`npm run build`)
- [ ] Cache Laravel wygenerowany
- [ ] Automatyczne backupy bazy danych skonfigurowane
- [ ] Wszystkie kontenery działają poprawnie
- [ ] Aplikacja dostępna przez HTTPS
- [ ] Logowanie przez Google działa
- [ ] Testowe zdjęcie lodówki przesłane i przeanalizowane przez Vertex AI

---

## Kontakt i wsparcie

- **Dokumentacja Docker:** https://docs.docker.com/
- **Dokumentacja Let's Encrypt:** https://letsencrypt.org/docs/
- **Dokumentacja Laravel Deployment:** https://laravel.com/docs/deployment
- **OVH Support:** https://help.ovhcloud.com/

---

**Powodzenia z wdrożeniem! 🚀**

**Wersja dokumentacji:** 1.0
**Data:** 2026-01-25
