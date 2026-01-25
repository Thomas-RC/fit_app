#!/bin/bash

###############################################################################
# FIT AI - Automatyczny Skrypt Wdrożenia
# Ten skrypt automatyzuje proces wdrożenia aplikacji na produkcji
###############################################################################

set -e  # Exit on error

# Kolory dla output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Funkcje pomocnicze
print_header() {
    echo -e "${CYAN}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  $1"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${CYAN}ℹ $1${NC}"
}

# Sprawdzenie czy jesteśmy w głównym katalogu projektu
if [ ! -f "docker-compose.prod.yml" ]; then
    print_error "Błąd: Nie znaleziono pliku docker-compose.prod.yml"
    print_info "Upewnij się, że jesteś w głównym katalogu projektu"
    exit 1
fi

print_header "FIT AI - Automatyczne Wdrożenie"

# Krok 1: Backup bazy danych
print_header "Krok 1: Backup bazy danych"
BACKUP_DIR="./backups"
mkdir -p $BACKUP_DIR

if [ "$(docker-compose -f docker-compose.prod.yml ps -q db)" ]; then
    DATE=$(date +%Y%m%d_%H%M%S)
    BACKUP_FILE="$BACKUP_DIR/pre-deploy-backup_$DATE.sql"

    print_info "Tworzenie backupu bazy danych..."
    docker-compose -f docker-compose.prod.yml exec -T db mysqldump -u root -proot fit_ai_prod > $BACKUP_FILE
    gzip $BACKUP_FILE
    print_success "Backup utworzony: ${BACKUP_FILE}.gz"
else
    print_warning "Kontener bazy danych nie jest uruchomiony - pomijam backup"
fi

# Krok 2: Pobieranie zmian z repozytorium
print_header "Krok 2: Pobieranie zmian z Git"
print_info "Sprawdzanie aktualnej gałęzi..."
CURRENT_BRANCH=$(git branch --show-current)
print_info "Aktualna gałąź: $CURRENT_BRANCH"

print_info "Pobieranie najnowszych zmian..."
git fetch origin

print_info "Aktualizacja kodu..."
git pull origin $CURRENT_BRANCH
print_success "Kod zaktualizowany"

# Krok 3: Budowanie kontenerów
print_header "Krok 3: Budowanie kontenerów Docker"
print_info "Budowanie obrazów Docker..."
docker-compose -f docker-compose.prod.yml build --no-cache
print_success "Obrazy Docker zbudowane"

# Krok 4: Zatrzymanie starych kontenerów
print_header "Krok 4: Zatrzymanie starych kontenerów"
print_info "Zatrzymywanie kontenerów..."
docker-compose -f docker-compose.prod.yml down
print_success "Kontenery zatrzymane"

# Krok 5: Uruchomienie nowych kontenerów
print_header "Krok 5: Uruchomienie nowych kontenerów"
print_info "Uruchamianie kontenerów w tle..."
docker-compose -f docker-compose.prod.yml up -d
print_success "Kontenery uruchomione"

# Oczekiwanie na uruchomienie kontenerów
print_info "Oczekiwanie na uruchomienie kontenerów (10s)..."
sleep 10

# Krok 6: Instalacja zależności Composer
print_header "Krok 6: Instalacja zależności Composer"
print_info "Instalowanie pakietów PHP..."
docker-compose -f docker-compose.prod.yml exec -T app composer install --optimize-autoloader --no-dev --no-interaction
print_success "Zależności Composer zainstalowane"

# Krok 7: Migracje bazy danych
print_header "Krok 7: Migracje bazy danych"
print_info "Uruchamianie migracji..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
print_success "Migracje wykonane"

# Krok 8: Czyszczenie i generowanie cache
print_header "Krok 8: Optymalizacja Laravel"
print_info "Czyszczenie starego cache..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:clear
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:clear

print_info "Generowanie nowego cache..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache
print_success "Cache wygenerowany"

# Krok 9: Budowanie assetów frontend (opcjonalnie)
print_header "Krok 9: Budowanie assetów frontend"
if [ -d "laravel/node_modules" ]; then
    print_info "Sprawdzanie czy są nowe zależności npm..."
    cd laravel
    npm install --production

    print_info "Budowanie assetów produkcyjnych..."
    npm run build
    cd ..
    print_success "Assety zbudowane"
else
    print_warning "Katalog node_modules nie istnieje - pomijam budowanie assetów"
    print_info "Aby zbudować assety, uruchom ręcznie:"
    print_info "  cd laravel && npm install && npm run build"
fi

# Krok 10: Naprawianie uprawnień
print_header "Krok 10: Naprawianie uprawnień"
print_info "Ustawianie poprawnych uprawnień..."
docker-compose -f docker-compose.prod.yml exec -T -u root app chown -R www-data:www-data storage bootstrap/cache
docker-compose -f docker-compose.prod.yml exec -T -u root app chmod -R 775 storage bootstrap/cache
print_success "Uprawnienia naprawione"

# Krok 11: Restart serwisów
print_header "Krok 11: Restart serwisów"
print_info "Restartowanie kontenerów..."
docker-compose -f docker-compose.prod.yml restart
print_success "Kontenery zrestartowane"

# Oczekiwanie na restart
print_info "Oczekiwanie na restart (5s)..."
sleep 5

# Krok 12: Weryfikacja
print_header "Krok 12: Weryfikacja wdrożenia"
print_info "Sprawdzanie statusu kontenerów..."

# Sprawdź czy wszystkie kontenery działają
CONTAINERS_RUNNING=$(docker-compose -f docker-compose.prod.yml ps --services --filter "status=running" | wc -l)
CONTAINERS_TOTAL=$(docker-compose -f docker-compose.prod.yml ps --services | wc -l)

if [ "$CONTAINERS_RUNNING" -eq "$CONTAINERS_TOTAL" ]; then
    print_success "Wszystkie kontenery działają ($CONTAINERS_RUNNING/$CONTAINERS_TOTAL)"
else
    print_warning "Niektóre kontenery nie działają ($CONTAINERS_RUNNING/$CONTAINERS_TOTAL)"
    docker-compose -f docker-compose.prod.yml ps
fi

# Podsumowanie
print_header "✓ Wdrożenie zakończone!"
echo ""
print_success "Aplikacja została pomyślnie wdrożona"
echo ""
print_info "Status kontenerów:"
docker-compose -f docker-compose.prod.yml ps
echo ""
print_info "Przydatne komendy:"
echo "  • Logi wszystkich kontenerów:  docker-compose -f docker-compose.prod.yml logs -f"
echo "  • Logi aplikacji:              docker-compose -f docker-compose.prod.yml logs -f app"
echo "  • Logi Nginx:                  docker-compose -f docker-compose.prod.yml logs -f nginx"
echo "  • Status kontenerów:           docker-compose -f docker-compose.prod.yml ps"
echo "  • Restart:                     docker-compose -f docker-compose.prod.yml restart"
echo ""
print_info "Sprawdź czy aplikacja działa:"
echo "  • Strona główna:               https://fit-ai.pl"
echo "  • Panel logowania:             https://fit-ai.pl/login"
echo ""
print_success "Gotowe! 🚀"
