# FIT AI - Struktura Bazy Danych

## Spis treści
1. [Diagram ERD](#diagram-erd)
2. [Szczegółowa struktura tabel](#szczegółowa-struktura-tabel)
3. [Relacje między tabelami](#relacje-między-tabelami)
4. [Indeksy i optymalizacja](#indeksy-i-optymalizacja)
5. [Migracje Laravel](#migracje-laravel)
6. [Seeders (dane testowe)](#seeders-dane-testowe)
7. [Przykładowe zapytania SQL](#przykładowe-zapytania-sql)

---

## Diagram ERD

### Entity Relationship Diagram

```
┌─────────────────────────┐
│        users            │
│─────────────────────────│
│ id (PK)                 │
│ google_id (UNIQUE)      │
│ name                    │
│ email (UNIQUE)          │
│ avatar                  │
│ created_at              │
│ updated_at              │
└───────────┬─────────────┘
            │
            │ 1:1
            │
            ▼
┌─────────────────────────┐
│   user_preferences      │
│─────────────────────────│
│ id (PK)                 │
│ user_id (FK)            │
│ diet_type               │
│ daily_calories          │
│ allergies (JSON)        │
│ exclude_ingredients     │
│ created_at              │
│ updated_at              │
└─────────────────────────┘

┌─────────────────────────┐
│        users            │
└───────────┬─────────────┘
            │
            │ 1:N
            │
            ▼
┌─────────────────────────┐
│     fridge_items        │
│─────────────────────────│
│ id (PK)                 │
│ user_id (FK)            │
│ product_name            │
│ quantity                │
│ unit                    │
│ added_at                │
│ expires_at              │
│ created_at              │
│ updated_at              │
└─────────────────────────┘

┌─────────────────────────┐
│        users            │
└───────────┬─────────────┘
            │
            │ 1:N
            │
            ▼
┌─────────────────────────┐
│      meal_plans         │
│─────────────────────────│
│ id (PK)                 │
│ user_id (FK)            │
│ date                    │
│ total_calories          │
│ created_at              │
│ updated_at              │
└───────────┬─────────────┘
            │
            │ 1:N
            │
            ▼
┌─────────────────────────┐
│   meal_plan_recipes     │
│─────────────────────────│
│ id (PK)                 │
│ meal_plan_id (FK)       │
│ spoonacular_recipe_id   │
│ meal_type               │
│ recipe_title            │
│ calories                │
│ recipe_data (JSON)      │
│ created_at              │
│ updated_at              │
└─────────────────────────┘

┌─────────────────────────┐
│        users            │
└───────────┬─────────────┘
            │
            │ 1:N
            │
            ▼
┌─────────────────────────┐
│    custom_dishes        │
│─────────────────────────│
│ id (PK)                 │
│ user_id (FK)            │
│ title                   │
│ ingredients (JSON)      │
│ instructions (TEXT)     │
│ calories                │
│ image_url               │
│ created_at              │
│ updated_at              │
└─────────────────────────┘

┌─────────────────────────┐
│     app_settings        │
│─────────────────────────│
│ id (PK)                 │
│ key (UNIQUE)            │
│ value (ENCRYPTED)       │
│ description             │
│ created_at              │
│ updated_at              │
└─────────────────────────┘
```

---

## Szczegółowa struktura tabel

### 1. users

Tabela przechowująca dane użytkowników zalogowanych przez Google OAuth.

| Kolumna | Typ | Długość | Null | Default | Opis |
|---------|-----|---------|------|---------|------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Klucz główny |
| google_id | VARCHAR | 255 | NO | - | ID użytkownika z Google (unikalny) |
| name | VARCHAR | 255 | NO | - | Imię i nazwisko użytkownika |
| email | VARCHAR | 255 | NO | - | Email użytkownika (unikalny) |
| avatar | VARCHAR | 500 | YES | NULL | URL do zdjęcia profilowego |
| created_at | TIMESTAMP | - | YES | NULL | Data utworzenia konta |
| updated_at | TIMESTAMP | - | YES | NULL | Data ostatniej aktualizacji |

**Indeksy:**
- PRIMARY KEY (id)
- UNIQUE KEY (google_id)
- UNIQUE KEY (email)

**Constraints:**
- Brak foreign keys (tabela główna)

---

### 2. user_preferences

Tabela preferencji żywieniowych użytkownika.

| Kolumna | Typ | Długość | Null | Default | Opis |
|---------|-----|---------|------|---------|------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Klucz główny |
| user_id | BIGINT UNSIGNED | - | NO | - | FK do users |
| diet_type | ENUM | - | NO | 'omnivore' | Typ diety |
| daily_calories | INT UNSIGNED | - | NO | 2000 | Dzienny limit kalorii |
| allergies | JSON | - | YES | NULL | Lista alergenów (JSON array) |
| exclude_ingredients | JSON | - | YES | NULL | Składniki do wykluczenia |
| created_at | TIMESTAMP | - | YES | NULL | Data utworzenia |
| updated_at | TIMESTAMP | - | YES | NULL | Data aktualizacji |

**Wartości ENUM diet_type:**
- 'omnivore' (z mięsem)
- 'vegetarian' (wegetariańska)
- 'vegan' (wegańska)
- 'keto' (ketogeniczna)

**Indeksy:**
- PRIMARY KEY (id)
- UNIQUE KEY (user_id)
- INDEX (diet_type)

**Constraints:**
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

---

### 3. fridge_items

Tabela produktów znajdujących się w lodówce użytkownika.

| Kolumna | Typ | Długość | Null | Default | Opis |
|---------|-----|---------|------|---------|------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Klucz główny |
| user_id | BIGINT UNSIGNED | - | NO | - | FK do users |
| product_name | VARCHAR | 255 | NO | - | Nazwa produktu |
| quantity | DECIMAL | 8,2 | YES | NULL | Ilość produktu |
| unit | VARCHAR | 50 | YES | NULL | Jednostka (kg, g, szt, ml) |
| added_at | TIMESTAMP | - | NO | CURRENT_TIMESTAMP | Data dodania |
| expires_at | DATE | - | YES | NULL | Data ważności |
| created_at | TIMESTAMP | - | YES | NULL | Data utworzenia rekordu |
| updated_at | TIMESTAMP | - | YES | NULL | Data aktualizacji |

**Indeksy:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (added_at)

**Constraints:**
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

---

### 4. meal_plans

Tabela planów posiłków wygenerowanych dla użytkownika.

| Kolumna | Typ | Długość | Null | Default | Opis |
|---------|-----|---------|------|---------|------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Klucz główny |
| user_id | BIGINT UNSIGNED | - | NO | - | FK do users |
| date | DATE | - | NO | - | Data planu |
| total_calories | INT UNSIGNED | - | NO | - | Suma kalorii w planie |
| created_at | TIMESTAMP | - | YES | NULL | Data utworzenia |
| updated_at | TIMESTAMP | - | YES | NULL | Data aktualizacji |

**Indeksy:**
- PRIMARY KEY (id)
- INDEX (user_id, date)
- INDEX (created_at)

**Constraints:**
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

---

### 5. meal_plan_recipes

Tabela przepisów wchodzących w skład planu posiłków.

| Kolumna | Typ | Długość | Null | Default | Opis |
|---------|-----|---------|------|---------|------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Klucz główny |
| meal_plan_id | BIGINT UNSIGNED | - | NO | - | FK do meal_plans |
| spoonacular_recipe_id | INT UNSIGNED | - | NO | - | ID przepisu z Spoonacular |
| meal_type | ENUM | - | NO | - | Typ posiłku |
| recipe_title | VARCHAR | 255 | NO | - | Tytuł przepisu |
| calories | INT UNSIGNED | - | NO | - | Kalorie w posiłku |
| recipe_data | JSON | - | YES | NULL | Pełne dane przepisu (cache) |
| created_at | TIMESTAMP | - | YES | NULL | Data utworzenia |
| updated_at | TIMESTAMP | - | YES | NULL | Data aktualizacji |

**Wartości ENUM meal_type:**
- 'breakfast' (śniadanie)
- 'lunch' (obiad)
- 'dinner' (kolacja)
- 'snack' (przekąska)

**Indeksy:**
- PRIMARY KEY (id)
- INDEX (meal_plan_id)
- INDEX (spoonacular_recipe_id)
- INDEX (meal_type)

**Constraints:**
- FOREIGN KEY (meal_plan_id) REFERENCES meal_plans(id) ON DELETE CASCADE

---

### 6. custom_dishes

Tabela własnych dań utworzonych przez użytkownika.

| Kolumna | Typ | Długość | Null | Default | Opis |
|---------|-----|---------|------|---------|------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Klucz główny |
| user_id | BIGINT UNSIGNED | - | NO | - | FK do users |
| title | VARCHAR | 255 | NO | - | Nazwa dania |
| ingredients | JSON | - | NO | - | Lista składników (JSON) |
| instructions | TEXT | - | YES | NULL | Instrukcja przygotowania |
| calories | INT UNSIGNED | - | NO | - | Kalorie w daniu |
| protein | DECIMAL | 8,2 | YES | NULL | Białko (g) |
| carbs | DECIMAL | 8,2 | YES | NULL | Węglowodany (g) |
| fat | DECIMAL | 8,2 | YES | NULL | Tłuszcze (g) |
| image_url | VARCHAR | 500 | YES | NULL | URL do zdjęcia dania |
| created_at | TIMESTAMP | - | YES | NULL | Data utworzenia |
| updated_at | TIMESTAMP | - | YES | NULL | Data aktualizacji |

**Indeksy:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (created_at)

**Constraints:**
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

---

### 7. app_settings

Tabela globalnych ustawień aplikacji (Vertex AI credentials, etc.).

| Kolumna | Typ | Długość | Null | Default | Opis |
|---------|-----|---------|------|---------|------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | Klucz główny |
| key | VARCHAR | 255 | NO | - | Klucz ustawienia (unikalny) |
| value | TEXT | - | YES | NULL | Wartość (zaszyfrowana) |
| description | VARCHAR | 500 | YES | NULL | Opis ustawienia |
| created_at | TIMESTAMP | - | YES | NULL | Data utworzenia |
| updated_at | TIMESTAMP | - | YES | NULL | Data aktualizacji |

**Przykładowe klucze:**
- `vertex_ai_credentials` - JSON credentials dla Vertex AI
- `vertex_ai_project_id` - Project ID Google Cloud
- `spoonacular_api_key` - Klucz API Spoonacular

**Indeksy:**
- PRIMARY KEY (id)
- UNIQUE KEY (key)

**Constraints:**
- Brak foreign keys

---

## Relacje między tabelami

### Relacje One-to-One (1:1)

```sql
users (1) ←→ (1) user_preferences
```

Każdy użytkownik ma dokładnie jeden rekord preferencji.

### Relacje One-to-Many (1:N)

```sql
users (1) ←→ (N) fridge_items
users (1) ←→ (N) meal_plans
users (1) ←→ (N) custom_dishes
meal_plans (1) ←→ (N) meal_plan_recipes
```

### Zasady kaskadowego usuwania

**ON DELETE CASCADE:**
- Usunięcie użytkownika → usuwa wszystkie powiązane dane (preferencje, produkty, plany, dania)
- Usunięcie planu posiłków → usuwa wszystkie przepisy w tym planie

---

## Indeksy i optymalizacja

### Indeksy podstawowe (PRIMARY KEY)

Wszystkie tabele mają auto-inkrementowany klucz główny `id`.

### Indeksy unikalne (UNIQUE KEY)

| Tabela | Kolumna | Cel |
|--------|---------|-----|
| users | google_id | Zapobieganie duplikatom kont Google |
| users | email | Zapobieganie duplikatom emaili |
| user_preferences | user_id | Jeden użytkownik = jedna preferencja |
| app_settings | key | Unikalne nazwy ustawień |

### Indeksy wyszukiwania (INDEX)

| Tabela | Kolumna(y) | Cel optymalizacji |
|--------|-----------|-------------------|
| user_preferences | diet_type | Filtrowanie po typie diety |
| fridge_items | user_id | Pobieranie produktów użytkownika |
| fridge_items | added_at | Sortowanie po dacie dodania |
| meal_plans | user_id, date | Wyszukiwanie planów użytkownika po dacie |
| meal_plans | created_at | Ostatnio utworzone plany |
| meal_plan_recipes | meal_plan_id | Pobieranie przepisów z planu |
| meal_plan_recipes | spoonacular_recipe_id | Wyszukiwanie po ID Spoonacular |
| meal_plan_recipes | meal_type | Filtrowanie po typie posiłku |
| custom_dishes | user_id | Pobieranie dań użytkownika |
| custom_dishes | created_at | Sortowanie po dacie utworzenia |

### Optymalizacja zapytań

```sql
-- Dobrze zoptymalizowane zapytanie
SELECT mp.*, mpr.*
FROM meal_plans mp
INNER JOIN meal_plan_recipes mpr ON mp.id = mpr.meal_plan_id
WHERE mp.user_id = ? AND mp.date = ?
ORDER BY mpr.meal_type;
-- Wykorzystuje indeks (user_id, date)

-- Zapytanie do unikania (bez indeksu)
SELECT * FROM fridge_items WHERE LOWER(product_name) LIKE '%milk%';
-- Full table scan - brak indeksu na product_name
```

---

## Migracje Laravel

### Kolejność wykonywania migracji

```
1. create_users_table
2. create_user_preferences_table
3. create_fridge_items_table
4. create_meal_plans_table
5. create_meal_plan_recipes_table
6. create_custom_dishes_table
7. create_app_settings_table
```

### 1. Migracja: users

**Plik:** `database/migrations/2024_01_01_000001_create_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('google_id')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar', 500)->nullable();
            $table->timestamps();

            // Indeksy
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### 2. Migracja: user_preferences

**Plik:** `database/migrations/2024_01_01_000002_create_user_preferences_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->enum('diet_type', ['omnivore', 'vegetarian', 'vegan', 'keto'])
                  ->default('omnivore');
            $table->unsignedInteger('daily_calories')->default(2000);
            $table->json('allergies')->nullable();
            $table->json('exclude_ingredients')->nullable();
            $table->timestamps();

            // Indeksy
            $table->unique('user_id');
            $table->index('diet_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
```

### 3. Migracja: fridge_items

**Plik:** `database/migrations/2024_01_01_000003_create_fridge_items_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fridge_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('product_name');
            $table->decimal('quantity', 8, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->date('expires_at')->nullable();
            $table->timestamps();

            // Indeksy
            $table->index('user_id');
            $table->index('added_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fridge_items');
    }
};
```

### 4. Migracja: meal_plans

**Plik:** `database/migrations/2024_01_01_000004_create_meal_plans_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('total_calories');
            $table->timestamps();

            // Indeksy
            $table->index(['user_id', 'date']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
```

### 5. Migracja: meal_plan_recipes

**Plik:** `database/migrations/2024_01_01_000005_create_meal_plan_recipes_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_plan_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_plan_id')
                  ->constrained('meal_plans')
                  ->onDelete('cascade');
            $table->unsignedInteger('spoonacular_recipe_id');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']);
            $table->string('recipe_title');
            $table->unsignedInteger('calories');
            $table->json('recipe_data')->nullable();
            $table->timestamps();

            // Indeksy
            $table->index('meal_plan_id');
            $table->index('spoonacular_recipe_id');
            $table->index('meal_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plan_recipes');
    }
};
```

### 6. Migracja: custom_dishes

**Plik:** `database/migrations/2024_01_01_000006_create_custom_dishes_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('title');
            $table->json('ingredients');
            $table->text('instructions')->nullable();
            $table->unsignedInteger('calories');
            $table->decimal('protein', 8, 2)->nullable();
            $table->decimal('carbs', 8, 2)->nullable();
            $table->decimal('fat', 8, 2)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->timestamps();

            // Indeksy
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_dishes');
    }
};
```

### 7. Migracja: app_settings

**Plik:** `database/migrations/2024_01_01_000007_create_app_settings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description', 500)->nullable();
            $table->timestamps();

            // Indeks
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
```

---

## Seeders (dane testowe)

### UserSeeder

**Plik:** `database/seeders/UserSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserPreference;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Testowy użytkownik
        $user = User::create([
            'google_id' => '123456789',
            'name' => 'Jan Kowalski',
            'email' => 'jan.kowalski@example.com',
            'avatar' => 'https://via.placeholder.com/150',
        ]);

        // Preferencje użytkownika
        UserPreference::create([
            'user_id' => $user->id,
            'diet_type' => 'vegetarian',
            'daily_calories' => 2000,
            'allergies' => json_encode(['gluten', 'nuts']),
            'exclude_ingredients' => json_encode(['onion', 'garlic']),
        ]);
    }
}
```

### AppSettingsSeeder

**Plik:** `database/seeders/AppSettingsSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::create([
            'key' => 'vertex_ai_project_id',
            'value' => encrypt('your-project-id'),
            'description' => 'Google Cloud Project ID for Vertex AI',
        ]);

        AppSetting::create([
            'key' => 'vertex_ai_credentials',
            'value' => encrypt('{}'),
            'description' => 'Vertex AI Service Account credentials (JSON)',
        ]);
    }
}
```

---

## Przykładowe zapytania SQL

### 1. Pobranie wszystkich produktów użytkownika

```sql
SELECT * FROM fridge_items
WHERE user_id = 1
ORDER BY added_at DESC;
```

**Laravel Eloquent:**
```php
$items = FridgeItem::where('user_id', $userId)
    ->orderBy('added_at', 'desc')
    ->get();
```

### 2. Pobranie ostatniego planu posiłków użytkownika

```sql
SELECT
    mp.*,
    GROUP_CONCAT(
        CONCAT(mpr.meal_type, ':', mpr.recipe_title, ' (', mpr.calories, ' kcal)')
        SEPARATOR ', '
    ) AS meals
FROM meal_plans mp
LEFT JOIN meal_plan_recipes mpr ON mp.id = mpr.meal_plan_id
WHERE mp.user_id = 1
GROUP BY mp.id
ORDER BY mp.created_at DESC
LIMIT 1;
```

**Laravel Eloquent:**
```php
$mealPlan = MealPlan::with('recipes')
    ->where('user_id', $userId)
    ->latest()
    ->first();
```

### 3. Statystyki użytkownika (liczba planów, produktów)

```sql
SELECT
    u.id,
    u.name,
    COUNT(DISTINCT mp.id) AS total_meal_plans,
    COUNT(DISTINCT fi.id) AS total_fridge_items,
    COUNT(DISTINCT cd.id) AS total_custom_dishes
FROM users u
LEFT JOIN meal_plans mp ON u.id = mp.user_id
LEFT JOIN fridge_items fi ON u.id = fi.user_id
LEFT JOIN custom_dishes cd ON u.id = cd.user_id
WHERE u.id = 1
GROUP BY u.id;
```

**Laravel Eloquent:**
```php
$user = User::withCount(['mealPlans', 'fridgeItems', 'customDishes'])
    ->find($userId);
```

### 4. Wyszukiwanie przepisów po typie posiłku

```sql
SELECT DISTINCT
    mpr.spoonacular_recipe_id,
    mpr.recipe_title,
    AVG(mpr.calories) AS avg_calories
FROM meal_plan_recipes mpr
WHERE mpr.meal_type = 'breakfast'
GROUP BY mpr.spoonacular_recipe_id, mpr.recipe_title
ORDER BY avg_calories ASC
LIMIT 10;
```

**Laravel Eloquent:**
```php
$recipes = MealPlanRecipe::select('spoonacular_recipe_id', 'recipe_title')
    ->selectRaw('AVG(calories) as avg_calories')
    ->where('meal_type', 'breakfast')
    ->groupBy('spoonacular_recipe_id', 'recipe_title')
    ->orderBy('avg_calories')
    ->limit(10)
    ->get();
```

### 5. Produkty wygasające w ciągu 3 dni

```sql
SELECT * FROM fridge_items
WHERE user_id = 1
  AND expires_at IS NOT NULL
  AND expires_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
ORDER BY expires_at ASC;
```

**Laravel Eloquent:**
```php
$expiringItems = FridgeItem::where('user_id', $userId)
    ->whereNotNull('expires_at')
    ->whereBetween('expires_at', [now(), now()->addDays(3)])
    ->orderBy('expires_at')
    ->get();
```

---

## Backup i restore bazy danych

### Backup MySQL

```bash
# Backup całej bazy danych
docker-compose exec db mysqldump -u root -p fit_ai > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup tylko struktury (bez danych)
docker-compose exec db mysqldump -u root -p --no-data fit_ai > structure.sql

# Backup tylko danych (bez struktury)
docker-compose exec db mysqldump -u root -p --no-create-info fit_ai > data.sql
```

### Restore MySQL

```bash
# Restore z pliku backup
docker-compose exec -T db mysql -u root -p fit_ai < backup_20240124_120000.sql
```

### Laravel backup commands

```bash
# Eksport struktury do migracji
php artisan schema:dump

# Eksport do SQL
php artisan db:dump
```

---

## Monitorowanie i maintenance

### Rozmiar tabel

```sql
SELECT
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'fit_ai'
ORDER BY (data_length + index_length) DESC;
```

### Optymalizacja tabel

```sql
OPTIMIZE TABLE users, user_preferences, fridge_items, meal_plans, meal_plan_recipes, custom_dishes, app_settings;
```

**Laravel command:**
```bash
php artisan db:optimize
```

### Czyszczenie starych danych

```sql
-- Usunięcie produktów starszych niż 30 dni
DELETE FROM fridge_items
WHERE added_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Usunięcie planów posiłków starszych niż 90 dni
DELETE FROM meal_plans
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

---

## Bezpieczeństwo bazy danych

### Szyfrowanie wrażliwych danych

**Laravel encryption helpers:**

```php
// Zapisywanie zaszyfrowanej wartości
AppSetting::create([
    'key' => 'vertex_ai_credentials',
    'value' => encrypt($jsonCredentials)
]);

// Odczytywanie zaszyfrowanej wartości
$credentials = decrypt($setting->value);
```

### Best practices

1. **Hasła:** Nigdy nie przechowuj haseł w plain text (użyj bcrypt/Hash)
2. **API Keys:** Zawsze szyfruj klucze API (użyj encrypt())
3. **JSON credentials:** Szyfruj przed zapisem w bazie
4. **Backup:** Regularnie twórz backupy (zalecane: codziennie)
5. **Permissions:** Ogranicz uprawnienia użytkownika MySQL

---

**Wersja dokumentu:** 1.0
**Data ostatniej aktualizacji:** 2026-01-24
**RDBMS:** MySQL 8.0
**ORM:** Laravel Eloquent
