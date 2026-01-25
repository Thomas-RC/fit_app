# FIT AI - Deployment Checklist

Kompleksowa lista kontrolna przed wdrożeniem aplikacji na VPS produkcyjny.

---

## 📋 Checklist Przedwdrożeniowy

### 🖥️ **VPS i Infrastruktura**

- [ ] VPS zamówiony i skonfigurowany
  - [ ] Minimum 2GB RAM (zalecane 4GB)
  - [ ] Minimum 20GB SSD
  - [ ] Ubuntu 22.04 LTS lub nowszy
- [ ] Dostęp SSH do VPS działa
- [ ] Domena zarejestrowana (np. `fit-ai.pl`)
- [ ] Rekordy DNS skonfigurowane w OVH:
  - [ ] `A Record: @ → YOUR_VPS_IP`
  - [ ] `A Record: www → YOUR_VPS_IP`
- [ ] DNS propagacja zakończona (sprawdź: `dig fit-ai.pl`)

---

### 🔐 **Bezpieczeństwo VPS**

- [ ] System zaktualizowany (`apt update && apt upgrade`)
- [ ] Firewall UFW skonfigurowany:
  - [ ] Port 22 (SSH) - otwarty
  - [ ] Port 80 (HTTP) - otwarty
  - [ ] Port 443 (HTTPS) - otwarty
  - [ ] UFW włączony (`ufw enable`)
- [ ] Fail2ban zainstalowany (`apt install fail2ban`)
- [ ] Użytkownik deploy utworzony (opcjonalnie)
- [ ] Klucz SSH skonfigurowany (zalecane zamiast hasła)
- [ ] Automatyczne aktualizacje bezpieczeństwa włączone (`unattended-upgrades`)

---

### 🐳 **Docker**

- [ ] Docker zainstalowany i działa
  - [ ] `docker --version` wyświetla wersję
  - [ ] `docker ps` działa bez sudo
- [ ] Docker Compose zainstalowany
  - [ ] `docker-compose --version` wyświetla wersję
- [ ] Użytkownik dodany do grupy docker (`usermod -aG docker $USER`)

---

### 📁 **Kod Aplikacji**

- [ ] Repozytorium sklonowane w `/var/www/fit-ai`
- [ ] Kod najnowszej wersji (`git pull origin main`)
- [ ] Katalog `backups/` utworzony
- [ ] Uprawnienia do katalogów poprawione:
  - [ ] `chown -R $USER:$USER /var/www/fit-ai`

---

### ⚙️ **Konfiguracja Plików**

- [ ] `laravel/.env` utworzony (skopiowany z `.env.example`)
- [ ] `laravel/.env` skonfigurowany z wartościami produkcyjnymi:
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] `APP_URL=https://twoja-domena.pl`
  - [ ] `APP_KEY` wygenerowany (`php artisan key:generate`)
- [ ] Hasła bazy danych zmienione na silne:
  - [ ] `DB_PASSWORD` (użytkownik aplikacji)
  - [ ] `MYSQL_ROOT_PASSWORD` (root MySQL)
- [ ] `docker/nginx/nginx.prod.conf` zaktualizowany:
  - [ ] Domena zmieniona z `fit-ai.pl` na twoją
  - [ ] Ścieżki do certyfikatu SSL poprawne

---

### 🔑 **API Credentials**

#### Google OAuth 2.0
- [ ] Projekt utworzony w Google Cloud Console
- [ ] OAuth 2.0 Client ID utworzony
- [ ] Authorized redirect URIs dodane:
  - [ ] `https://twoja-domena.pl/auth/google/callback`
- [ ] `GOOGLE_CLIENT_ID` ustawiony w `.env`
- [ ] `GOOGLE_CLIENT_SECRET` ustawiony w `.env`
- [ ] `GOOGLE_REDIRECT_URI` ustawiony w `.env`

#### Google Vertex AI (Gemini Vision)
- [ ] Google Cloud Project utworzony
- [ ] Vertex AI API włączone
- [ ] Service Account utworzony z rolą "Vertex AI User"
- [ ] Klucz JSON Service Account pobrany (do uploadu w panelu admina)
- [ ] `GOOGLE_CLOUD_PROJECT_ID` ustawiony w `.env`

#### Spoonacular API
- [ ] Konto utworzone na Spoonacular.com
- [ ] API Key wygenerowany
- [ ] `SPOONACULAR_API_KEY` ustawiony w `.env`
- [ ] Limity API sprawdzone (Free: 150 req/day)

#### Email (opcjonalnie)
- [ ] SMTP skonfigurowany (Gmail, Mailgun, SendGrid)
- [ ] `MAIL_*` zmienne ustawione w `.env`
- [ ] Email testowy wysłany i otrzymany

---

### 🔒 **SSL Certificate**

- [ ] Certbot zainstalowany (`apt install certbot`)
- [ ] Certyfikat SSL wygenerowany:
  - [ ] `certbot certonly --standalone -d twoja-domena.pl -d www.twoja-domena.pl`
- [ ] Certyfikaty znajdują się w `/etc/letsencrypt/live/twoja-domena.pl/`
  - [ ] `fullchain.pem` istnieje
  - [ ] `privkey.pem` istnieje
- [ ] Automatyczne odnowienie skonfigurowane
  - [ ] Test odnowienia: `certbot renew --dry-run`
- [ ] `docker-compose.prod.yml` montuje certyfikaty (`/etc/letsencrypt`)

---

### 🚀 **Uruchomienie Aplikacji**

- [ ] Kontenery zbudowane (`docker-compose -f docker-compose.prod.yml build`)
- [ ] Kontenery uruchomione (`docker-compose -f docker-compose.prod.yml up -d`)
- [ ] Wszystkie kontenery działają:
  - [ ] `fit-ai-nginx-prod` (Status: Up)
  - [ ] `fit-ai-app-prod` (Status: Up)
  - [ ] `fit-ai-db-prod` (Status: Up)
- [ ] Zależności Composer zainstalowane
  - [ ] `composer install --no-dev --optimize-autoloader`
- [ ] Klucz aplikacji wygenerowany (`php artisan key:generate`)
- [ ] Migracje bazy danych wykonane (`php artisan migrate --force`)
- [ ] Cache wygenerowany:
  - [ ] `php artisan config:cache`
  - [ ] `php artisan route:cache`
  - [ ] `php artisan view:cache`
- [ ] Uprawnienia naprawione:
  - [ ] `chown -R www-data:www-data storage bootstrap/cache`
  - [ ] `chmod -R 775 storage bootstrap/cache`

---

### 🎨 **Frontend Assets**

- [ ] Node.js zainstalowany na VPS (v20+)
- [ ] Zależności npm zainstalowane (`npm install`)
- [ ] Assety produkcyjne zbudowane (`npm run build`)
- [ ] Pliki w `public/build/` istnieją

---

### ✅ **Testy Funkcjonalne**

#### Dostępność
- [ ] Strona główna ładuje się: `https://twoja-domena.pl`
- [ ] HTTP przekierowuje na HTTPS
- [ ] Certyfikat SSL ważny (zielona kłódka w przeglądarce)
- [ ] Brak błędów w konsoli przeglądarki (F12)

#### Logowanie
- [ ] Przycisk "Zaloguj przez Google" działa
- [ ] Proces OAuth nie rzuca błędów
- [ ] Po zalogowaniu użytkownik trafia na dashboard
- [ ] Wylogowanie działa poprawnie

#### Funkcjonalności
- [ ] Upload zdjęcia lodówki działa
- [ ] Vertex AI rozpoznaje produkty na zdjęciu
- [ ] Generowanie planu posiłków działa
- [ ] Przepisy z Spoonacular się ładują
- [ ] Panel użytkownika działa
- [ ] Panel admina działa (jeśli istnieje)

#### Wydajność
- [ ] Strona ładuje się szybko (< 3s)
- [ ] Brak błędów 500 w logach
- [ ] Brak wycieków pamięci

---

### 🗄️ **Backup i Monitoring**

- [ ] Automatyczne backupy skonfigurowane
  - [ ] Skrypt `/usr/local/bin/backup-fitai-db.sh` istnieje
  - [ ] Zadanie cron dodane (3:00 AM)
  - [ ] Testowy backup wykonany i działa
- [ ] Katalog `backups/` zawiera backupy
- [ ] Stare backupy są usuwane automatycznie (7 dni)
- [ ] Logi backupów zapisywane w `/var/log/fitai-backup.log`
- [ ] Backup poza VPS skonfigurowany (rsync, cloud storage)

---

### 📊 **Monitoring**

- [ ] Logi aplikacji sprawdzone:
  - [ ] `docker-compose -f docker-compose.prod.yml logs app`
  - [ ] Brak błędów CRITICAL/ERROR
- [ ] Logi Nginx sprawdzone:
  - [ ] `docker-compose -f docker-compose.prod.yml logs nginx`
  - [ ] Brak błędów 5xx
- [ ] Logi MySQL sprawdzone:
  - [ ] `docker-compose -f docker-compose.prod.yml logs db`
  - [ ] Brak błędów połączenia
- [ ] Monitoring zasobów:
  - [ ] `docker stats` - użycie CPU/RAM w normie
  - [ ] `df -h` - wystarczająco miejsca na dysku (min. 5GB wolne)
  - [ ] `free -h` - wystarczająco RAM

---

### 📚 **Dokumentacja**

- [ ] Instrukcja wdrożenia udostępniona zespołowi
- [ ] Hasła i credentials zapisane w bezpiecznym miejscu (np. 1Password, LastPass)
- [ ] Kontakt do supportu zapisany
- [ ] Plan disaster recovery przygotowany

---

### 🔄 **Aktualizacje**

- [ ] Proces aktualizacji przetestowany
- [ ] Skrypt `deploy.sh` działa poprawnie
- [ ] Rollback plan przygotowany (na wypadek błędów)

---

### 🧪 **Post-Deployment Tests**

#### Test 1: Dostępność
```bash
curl -I https://twoja-domena.pl
# Oczekiwany status: 200 OK
```

#### Test 2: SSL
```bash
openssl s_client -connect twoja-domena.pl:443 -servername twoja-domena.pl
# Sprawdź czy certyfikat jest ważny
```

#### Test 3: Baza danych
```bash
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
# W Tinker: DB::connection()->getPdo();
# Powinno zwrócić obiekt PDO bez błędów
```

#### Test 4: Cache
```bash
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
# Brak błędów
```

#### Test 5: API Connections
- [ ] Test Vertex AI (upload testowego zdjęcia lodówki)
- [ ] Test Spoonacular API (wyszukiwanie przepisów)
- [ ] Test Google OAuth (logowanie)

---

## 🎉 Wdrożenie Zakończone!

Jeśli wszystkie checkboxy są zaznaczone ✅, Twoja aplikacja jest gotowa do użycia w produkcji!

---

## 📞 W razie problemów

1. **Sprawdź logi:** `docker-compose -f docker-compose.prod.yml logs -f`
2. **Status kontenerów:** `docker-compose -f docker-compose.prod.yml ps`
3. **Restart:** `docker-compose -f docker-compose.prod.yml restart`
4. **Dokumentacja:** Sprawdź `docs/VPS-DEPLOYMENT.md`

---

**Data wdrożenia:** ___________________

**Wdrożył:** ___________________

**Uwagi:**
```
_____________________________________________________________

_____________________________________________________________

_____________________________________________________________
```
