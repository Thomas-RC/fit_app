# FIT AI - Makefile
# Proste komendy do zarządzania projektem

.PHONY: help
.DEFAULT_GOAL := help

# Kolory dla output
CYAN := \033[0;36m
GREEN := \033[0;32m
YELLOW := \033[0;33m
RED := \033[0;31m
RESET := \033[0m

help: ## Wyświetla pomoc
	@echo "$(CYAN)FIT AI - Dostępne komendy:$(RESET)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-20s$(RESET) %s\n", $$1, $$2}'
	@echo ""

# ============================================
# DOCKER - Zarządzanie kontenerami
# ============================================

build: ## Buduje kontenery Docker
	@echo "$(CYAN)Budowanie kontenerów...$(RESET)"
	docker-compose build

up: ## Uruchamia kontenery w tle
	@echo "$(CYAN)Uruchamianie kontenerów...$(RESET)"
	docker-compose up -d
	@echo "$(GREEN)Aplikacja dostępna na: http://localhost:8000$(RESET)"

down: ## Zatrzymuje kontenery
	@echo "$(CYAN)Zatrzymywanie kontenerów...$(RESET)"
	docker-compose down

restart: ## Restartuje kontenery
	@echo "$(CYAN)Restartowanie kontenerów...$(RESET)"
	docker-compose restart

stop: ## Zatrzymuje kontenery (bez usuwania)
	@echo "$(CYAN)Zatrzymywanie kontenerów...$(RESET)"
	docker-compose stop

logs: ## Wyświetla logi wszystkich kontenerów
	docker-compose logs -f

logs-app: ## Wyświetla logi aplikacji Laravel
	docker-compose logs -f app

logs-nginx: ## Wyświetla logi Nginx
	docker-compose logs -f nginx

logs-db: ## Wyświetla logi MySQL
	docker-compose logs -f db

ps: ## Wyświetla status kontenerów
	docker-compose ps

# ============================================
# INSTALACJA - Pierwszy setup projektu
# ============================================

install: ## Pełna instalacja projektu (pierwszy raz)
	@echo "$(CYAN)Instalacja projektu FIT AI...$(RESET)"
	@make build
	@make up
	@make composer-install
	@make key-generate
	@make npm-install
	@make migrate
	@make npm-build
	@echo "$(GREEN)Instalacja zakończona!$(RESET)"
	@echo "$(GREEN)Aplikacja dostępna na: http://localhost:8000$(RESET)"

setup: install ## Alias dla install

# ============================================
# LARAVEL - Komendy PHP/Artisan
# ============================================

composer-install: ## Instaluje zależności Composer
	@echo "$(CYAN)Instalacja zależności Composer...$(RESET)"
	docker-compose exec app composer install

composer-update: ## Aktualizuje zależności Composer
	@echo "$(CYAN)Aktualizacja Composer...$(RESET)"
	docker-compose exec app composer update

key-generate: ## Generuje klucz aplikacji Laravel
	@echo "$(CYAN)Generowanie klucza aplikacji...$(RESET)"
	docker-compose exec app php artisan key:generate

migrate: ## Uruchamia migracje bazy danych
	@echo "$(CYAN)Uruchamianie migracji...$(RESET)"
	docker-compose exec app php artisan migrate

migrate-fresh: ## Resetuje i uruchamia migracje od nowa (USUWA DANE!)
	@echo "$(RED)UWAGA: To usunie wszystkie dane!$(RESET)"
	docker-compose exec app php artisan migrate:fresh

migrate-rollback: ## Cofa ostatnią migrację
	@echo "$(CYAN)Cofanie migracji...$(RESET)"
	docker-compose exec app php artisan migrate:rollback

seed: ## Wypełnia bazę danymi testowymi
	@echo "$(CYAN)Seedowanie bazy danych...$(RESET)"
	docker-compose exec app php artisan db:seed

migrate-seed: ## Migruje i seeduje bazę danych
	@make migrate
	@make seed

fresh: ## Resetuje bazę i wypełnia danymi testowymi
	@echo "$(RED)UWAGA: To usunie wszystkie dane!$(RESET)"
	@make migrate-fresh
	@make seed

tinker: ## Otwiera Laravel Tinker (REPL)
	docker-compose exec app php artisan tinker

cache-clear: ## Czyści cache aplikacji
	@echo "$(CYAN)Czyszczenie cache...$(RESET)"
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

optimize: ## Optymalizuje aplikację (cache config, routes, views)
	@echo "$(CYAN)Optymalizacja aplikacji...$(RESET)"
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache
	docker-compose exec app composer install --optimize-autoloader --no-dev

# ============================================
# FRONTEND - NPM i Assets
# ============================================

npm-install: ## Instaluje zależności NPM
	@echo "$(CYAN)Instalacja zależności NPM...$(RESET)"
	cd laravel && npm install

npm-update: ## Aktualizuje zależności NPM
	@echo "$(CYAN)Aktualizacja NPM...$(RESET)"
	cd laravel && npm update

npm-build: ## Buduje assety produkcyjne (Vite)
	@echo "$(CYAN)Budowanie assetów...$(RESET)"
	cd laravel && npm run build

npm-dev: ## Buduje assety developerskie
	@echo "$(CYAN)Budowanie assetów (dev)...$(RESET)"
	cd laravel && npm run dev

npm-watch: ## Włącza watch mode dla assetów
	@echo "$(CYAN)Watch mode (Hot Module Replacement)...$(RESET)"
	cd laravel && npm run dev

# ============================================
# BAZA DANYCH - Backup i Restore
# ============================================

db-backup: ## Tworzy backup bazy danych
	@echo "$(CYAN)Tworzenie backupu...$(RESET)"
	@mkdir -p backups
	@$(eval DB_DATABASE=$(shell grep '^DB_DATABASE=' laravel/.env | cut -d '=' -f2))
	docker-compose exec -T db mysqldump -u root -proot $(DB_DATABASE) > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)Backup utworzony w folderze backups/$(RESET)"

db-restore: ## Przywraca bazę z backupu (użyj: make db-restore FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "$(RED)Błąd: Podaj plik backupu: make db-restore FILE=backup.sql$(RESET)"; \
		exit 1; \
	fi
	@echo "$(CYAN)Przywracanie z backupu: $(FILE)...$(RESET)"
	@$(eval DB_DATABASE=$(shell grep '^DB_DATABASE=' laravel/.env | cut -d '=' -f2))
	docker-compose exec -T db mysql -u root -proot $(DB_DATABASE) < $(FILE)
	@echo "$(GREEN)Backup przywrócony!$(RESET)"

db-console: ## Otwiera konsolę MySQL
	@$(eval DB_DATABASE=$(shell grep '^DB_DATABASE=' laravel/.env | cut -d '=' -f2))
	docker-compose exec db mysql -u root -proot $(DB_DATABASE)

# ============================================
# TESTING - Testy aplikacji
# ============================================

test: ## Uruchamia wszystkie testy
	@echo "$(CYAN)Uruchamianie testów...$(RESET)"
	docker-compose exec app php artisan test

test-unit: ## Uruchamia testy jednostkowe
	@echo "$(CYAN)Uruchamianie testów jednostkowych...$(RESET)"
	docker-compose exec app php artisan test --testsuite=Unit

test-feature: ## Uruchamia testy funkcjonalne
	@echo "$(CYAN)Uruchamianie testów funkcjonalnych...$(RESET)"
	docker-compose exec app php artisan test --testsuite=Feature

test-coverage: ## Generuje raport pokrycia testów
	@echo "$(CYAN)Generowanie raportu pokrycia...$(RESET)"
	docker-compose exec app php artisan test --coverage

# ============================================
# QUALITY - Jakość kodu
# ============================================

lint: ## Sprawdza jakość kodu (Pint)
	@echo "$(CYAN)Sprawdzanie jakości kodu...$(RESET)"
	docker-compose exec app ./vendor/bin/pint --test

lint-fix: ## Naprawia problemy z jakością kodu
	@echo "$(CYAN)Naprawa kodu...$(RESET)"
	docker-compose exec app ./vendor/bin/pint

phpstan: ## Uruchamia PHPStan (static analysis)
	@echo "$(CYAN)Analiza statyczna kodu...$(RESET)"
	docker-compose exec app ./vendor/bin/phpstan analyse

# ============================================
# DEVELOPMENT - Narzędzia deweloperskie
# ============================================

shell: ## Otwiera shell w kontenerze aplikacji
	docker-compose exec app bash

shell-root: ## Otwiera shell jako root
	docker-compose exec -u root app bash

artisan: ## Uruchamia komendę artisan (użyj: make artisan CMD="route:list")
	@if [ -z "$(CMD)" ]; then \
		echo "$(RED)Błąd: Podaj komendę: make artisan CMD=\"route:list\"$(RESET)"; \
		exit 1; \
	fi
	docker-compose exec app php artisan $(CMD)

routes: ## Wyświetla listę routingu
	docker-compose exec app php artisan route:list

composer: ## Uruchamia komendę composer (użyj: make composer CMD="require package")
	@if [ -z "$(CMD)" ]; then \
		echo "$(RED)Błąd: Podaj komendę: make composer CMD=\"require package\"$(RESET)"; \
		exit 1; \
	fi
	docker-compose exec app composer $(CMD)

# ============================================
# MONITORING - Logi i debugowanie
# ============================================

watch-logs: ## Obserwuje logi aplikacji Laravel
	tail -f laravel/storage/logs/laravel.log

clear-logs: ## Czyści logi aplikacji
	@echo "$(CYAN)Czyszczenie logów...$(RESET)"
	> laravel/storage/logs/laravel.log
	@echo "$(GREEN)Logi wyczyszczone!$(RESET)"

permissions: ## Naprawia uprawnienia do katalogów
	@echo "$(CYAN)Naprawianie uprawnień...$(RESET)"
	docker-compose exec -u root app chmod -R 775 storage bootstrap/cache
	docker-compose exec -u root app chown -R 1000:1000 storage bootstrap/cache
	@echo "$(GREEN)Uprawnienia naprawione!$(RESET)"

# ============================================
# DEPLOYMENT - Wdrożenie produkcyjne
# ============================================

deploy-prod: ## Wdrożenie produkcyjne (OSTROŻNIE!)
	@echo "$(RED)UWAGA: Wdrożenie produkcyjne!$(RESET)"
	@read -p "Czy na pewno chcesz wdrożyć na produkcję? [y/N] " confirm; \
	if [ "$$confirm" = "y" ] || [ "$$confirm" = "Y" ]; then \
		make deploy-prepare; \
		make migrate; \
		make optimize; \
		make npm-build; \
		make restart; \
		echo "$(GREEN)Wdrożenie zakończone!$(RESET)"; \
	else \
		echo "$(YELLOW)Wdrożenie anulowane.$(RESET)"; \
	fi

deploy-prepare: ## Przygotowuje aplikację do wdrożenia
	@echo "$(CYAN)Przygotowanie do wdrożenia...$(RESET)"
	docker-compose exec app composer install --no-dev --optimize-autoloader
	@make cache-clear
	@make optimize

# ============================================
# CLEANUP - Czyszczenie
# ============================================

clean: ## Czyści wszystkie pliki tymczasowe
	@echo "$(CYAN)Czyszczenie plików tymczasowych...$(RESET)"
	@make cache-clear
	rm -rf laravel/node_modules
	rm -rf laravel/vendor
	rm -rf laravel/storage/logs/*.log
	rm -rf laravel/storage/framework/cache/*
	rm -rf laravel/storage/framework/sessions/*
	rm -rf laravel/storage/framework/views/*
	@echo "$(GREEN)Czyszczenie zakończone!$(RESET)"

clean-docker: ## Usuwa kontenery, wolumeny i obrazy
	@echo "$(RED)UWAGA: To usunie wszystkie kontenery i dane!$(RESET)"
	@read -p "Czy na pewno chcesz kontynuować? [y/N] " confirm; \
	if [ "$$confirm" = "y" ] || [ "$$confirm" = "Y" ]; then \
		docker-compose down -v; \
		docker system prune -af; \
		echo "$(GREEN)Docker wyczyszczony!$(RESET)"; \
	else \
		echo "$(YELLOW)Czyszczenie anulowane.$(RESET)"; \
	fi

# ============================================
# STATUS - Sprawdzanie statusu
# ============================================

status: ## Wyświetla status aplikacji
	@echo "$(CYAN)Status aplikacji FIT AI:$(RESET)"
	@echo ""
	@echo "$(GREEN)Kontenery:$(RESET)"
	@docker-compose ps
	@echo ""
	@echo "$(GREEN)Aplikacja:$(RESET)"
	@echo "  URL: http://localhost:8000"
	@echo ""
	@echo "$(GREEN)Baza danych:$(RESET)"
	@docker-compose exec db mysql -u root -proot -e "SELECT VERSION();" 2>/dev/null || echo "  Brak połączenia"

health: ## Sprawdza czy wszystko działa
	@echo "$(CYAN)Sprawdzanie stanu aplikacji...$(RESET)"
	@curl -s http://localhost:8000 > /dev/null && echo "$(GREEN)Aplikacja działa!$(RESET)" || echo "$(RED)Aplikacja nie odpowiada!$(RESET)"
	@docker-compose exec db mysql -u root -proot -e "SELECT 1;" > /dev/null 2>&1 && echo "$(GREEN)Baza danych działa!$(RESET)" || echo "$(RED)Baza danych nie odpowiada!$(RESET)"

# ============================================
# QUICK COMMANDS - Szybkie komendy
# ============================================

quick-start: ## Szybkie uruchomienie (up + migrate)
	@make up
	@sleep 3
	@make migrate

quick-restart: ## Szybki restart z czyszczeniem cache
	@make cache-clear
	@make restart

dev: ## Tryb deweloperski (up + logs + watch)
	@make up
	@echo "$(GREEN)Uruchamianie trybu deweloperskiego...$(RESET)"
	@make npm-watch &
	@make logs

# ============================================
# GIT - Operacje na repozytorium
# ============================================

git-status: ## Status repozytorium Git
	git status

git-pull: ## Pobiera zmiany z repozytorium
	@echo "$(CYAN)Pobieranie zmian z repozytorium...$(RESET)"
	git pull origin main

git-push: ## Wysyła zmiany do repozytorium
	@echo "$(CYAN)Wysyłanie zmian do repozytorium...$(RESET)"
	git push origin main

# ============================================
# INFO - Informacje o projekcie
# ============================================

version: ## Wyświetla wersje użytych technologii
	@echo "$(CYAN)Wersje technologii:$(RESET)"
	@echo ""
	@echo "$(GREEN)Docker:$(RESET)"
	@docker --version
	@echo ""
	@echo "$(GREEN)Docker Compose:$(RESET)"
	@docker-compose --version
	@echo ""
	@echo "$(GREEN)PHP (w kontenerze):$(RESET)"
	@docker-compose exec app php --version | head -n 1
	@echo ""
	@echo "$(GREEN)Laravel (w kontenerze):$(RESET)"
	@docker-compose exec app php artisan --version
	@echo ""
	@echo "$(GREEN)MySQL (w kontenerze):$(RESET)"
	@docker-compose exec db mysql --version
	@echo ""
	@echo "$(GREEN)Node.js (host):$(RESET)"
	@node --version 2>/dev/null || echo "  Not installed"
	@echo ""
	@echo "$(GREEN)NPM (host):$(RESET)"
	@npm --version 2>/dev/null || echo "  Not installed"

info: ## Wyświetla informacje o projekcie
	@echo "$(CYAN)╔════════════════════════════════════════╗$(RESET)"
	@echo "$(CYAN)║          FIT AI - Projekt              ║$(RESET)"
	@echo "$(CYAN)╚════════════════════════════════════════╝$(RESET)"
	@echo ""
	@echo "$(GREEN)Aplikacja do planowania posiłków$(RESET)"
	@echo "Stack: Laravel 11 + Blade + Alpine.js + Tailwind CSS"
	@echo "AI: Vertex AI (Gemini Vision)"
	@echo "API: Spoonacular"
	@echo ""
	@echo "Dokumentacja:"
	@echo "  - README.md           - Podstawowe informacje"
	@echo "  - DEPLOYMENT.md       - Instrukcja wdrożenia"
	@echo "  - TECH_STACK.md       - Stack technologiczny"
	@echo "  - DATABASE.md         - Struktura bazy danych"
	@echo "  - MVP.md              - Zakres MVP"
	@echo ""
	@echo "Uruchom: $(CYAN)make install$(RESET) aby rozpocząć"
	@echo ""
