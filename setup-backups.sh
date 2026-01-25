#!/bin/bash

###############################################################################
# FIT AI - Automatic Backup Setup Script
# Konfiguruje automatyczne backupy bazy danych
###############################################################################

set -e

# Kolory
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
CYAN='\033[0;36m'
NC='\033[0m'

print_header() {
    echo -e "${CYAN}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  $1"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_info() {
    echo -e "${CYAN}ℹ $1${NC}"
}

print_header "FIT AI - Konfiguracja Automatycznych Backupów"

# Pobierz bieżący katalog projektu
PROJECT_DIR=$(pwd)
BACKUP_DIR="$PROJECT_DIR/backups"

# Utwórz katalog na backupy jeśli nie istnieje
mkdir -p "$BACKUP_DIR"
print_success "Katalog backupów: $BACKUP_DIR"

# Utwórz skrypt backupu
BACKUP_SCRIPT="/usr/local/bin/backup-fitai-db.sh"

print_info "Tworzenie skryptu backupu: $BACKUP_SCRIPT"

sudo tee $BACKUP_SCRIPT > /dev/null <<'EOF'
#!/bin/bash

###############################################################################
# FIT AI - Database Backup Script
# Automatyczny backup bazy danych
###############################################################################

# Konfiguracja
PROJECT_DIR="/var/www/fit-ai"
BACKUP_DIR="$PROJECT_DIR/backups"
COMPOSE_FILE="$PROJECT_DIR/docker-compose.prod.yml"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/backup_$DATE.sql"
RETENTION_DAYS=7

# Sprawdź czy katalog backupów istnieje
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
fi

# Przejdź do katalogu projektu
cd "$PROJECT_DIR"

# Sprawdź czy kontenery działają
if ! docker-compose -f "$COMPOSE_FILE" ps | grep -q "Up"; then
    echo "ERROR: Kontenery Docker nie działają!"
    exit 1
fi

# Wykonaj backup
echo "$(date): Rozpoczynam backup bazy danych..."
docker-compose -f "$COMPOSE_FILE" exec -T db mysqldump \
    -u root \
    -proot \
    --single-transaction \
    --routines \
    --triggers \
    fit_ai_prod > "$BACKUP_FILE"

# Sprawdź czy backup się powiódł
if [ $? -eq 0 ]; then
    # Kompresuj backup
    gzip "$BACKUP_FILE"
    echo "$(date): Backup utworzony: backup_$DATE.sql.gz"

    # Usuń stare backupy (starsze niż RETENTION_DAYS dni)
    find "$BACKUP_DIR" -name "backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete
    echo "$(date): Usunięto stare backupy (starsze niż $RETENTION_DAYS dni)"

    # Wyświetl rozmiar backupu
    SIZE=$(du -h "$BACKUP_FILE.gz" | cut -f1)
    echo "$(date): Rozmiar backupu: $SIZE"

    # Wyświetl liczbę backupów
    COUNT=$(ls -1 "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null | wc -l)
    echo "$(date): Liczba backupów: $COUNT"
else
    echo "$(date): ERROR: Backup nie powiódł się!"
    exit 1
fi

echo "$(date): Backup zakończony pomyślnie!"
EOF

# Nadaj uprawnienia wykonywania
sudo chmod +x $BACKUP_SCRIPT
print_success "Skrypt backupu utworzony i gotowy do użycia"

# Dodaj zadanie cron
print_info "Konfiguracja zadania cron (backup codziennie o 3:00 AM)..."

# Sprawdź czy zadanie już istnieje
if crontab -l 2>/dev/null | grep -q "backup-fitai-db.sh"; then
    print_info "Zadanie cron już istnieje - pomijam"
else
    # Dodaj nowe zadanie cron
    (crontab -l 2>/dev/null; echo "0 3 * * * $BACKUP_SCRIPT >> /var/log/fitai-backup.log 2>&1") | crontab -
    print_success "Zadanie cron dodane (3:00 AM codziennie)"
fi

# Utwórz plik logu jeśli nie istnieje
sudo touch /var/log/fitai-backup.log
sudo chmod 666 /var/log/fitai-backup.log

print_success "Konfiguracja zakończona!"
echo ""

# Podsumowanie
print_header "Podsumowanie"
echo ""
print_info "📂 Katalog backupów: $BACKUP_DIR"
print_info "📜 Skrypt backupu: $BACKUP_SCRIPT"
print_info "📝 Log backupów: /var/log/fitai-backup.log"
print_info "🕒 Harmonogram: Codziennie o 3:00 AM"
print_info "🗑️  Retencja: 7 dni (stare backupy są automatycznie usuwane)"
echo ""

# Test backupu
read -p "Czy chcesz wykonać testowy backup teraz? (y/N) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_info "Wykonywanie testowego backupu..."
    sudo $BACKUP_SCRIPT
    echo ""
    print_success "Testowy backup zakończony!"
    print_info "Sprawdź zawartość katalogu backupów:"
    ls -lh "$BACKUP_DIR"
fi

echo ""
print_header "Przydatne komendy"
echo ""
echo "  • Uruchom backup ręcznie:           sudo $BACKUP_SCRIPT"
echo "  • Zobacz harmonogram cron:          crontab -l"
echo "  • Zobacz logi backupów:             cat /var/log/fitai-backup.log"
echo "  • Lista backupów:                   ls -lh $BACKUP_DIR"
echo "  • Usuń stare backupy ręcznie:       find $BACKUP_DIR -name 'backup_*.sql.gz' -mtime +7 -delete"
echo ""
print_success "Gotowe! Automatyczne backupy zostały skonfigurowane. 🎉"
