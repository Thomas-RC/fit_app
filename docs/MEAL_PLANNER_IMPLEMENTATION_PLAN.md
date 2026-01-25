# Plan Wdrożenia Zaawansowanego Planera Posiłków

**Data utworzenia**: 2026-01-25
**Wersja**: 1.0
**Status**: Do implementacji

---

## 📋 Spis Treści

1. [Analiza Obecnego Stanu](#1-analiza-obecnego-stanu)
2. [Dostępne Endpointy API Spoonacular](#2-dostępne-endpointy-api-spoonacular)
3. [Strategia dla Typów Posiłków](#3-strategia-dla-typów-posiłków)
4. [Logika Wyboru Endpointa](#4-logika-wyboru-endpointa)
5. [Struktura Danych i Ujednolicenie](#5-struktura-danych-i-ujednolicenie)
6. [Obsługa Preferencji Użytkownika](#6-obsługa-preferencji-użytkownika)
7. [Plan Implementacji Krok Po Kroku](#7-plan-implementacji-krok-po-kroku)
8. [Testowanie i Walidacja](#8-testowanie-i-walidacja)

---

## 1. Analiza Obecnego Stanu

### 🔴 Zidentyfikowane Problemy

#### Problem 1: Nieprawidłowe użycie `fillIngredients`
- **Lokalizacja**: `MealPlannerService.php:73`
- **Opis**: Parametr `fillIngredients: true` w `complexSearch` nie zwraca `usedIngredients` i `missedIngredients` bez parametru `includeIngredients`
- **Skutek**: Brak informacji o dopasowaniu składników z lodówki użytkownika

#### Problem 2: Nieoptymalne użycie endpointów
- **Opis**: Używanie tylko `complexSearch` do wszystkich scenariuszy
- **Skutek**:
  - Wyższy koszt API (więcej punktów)
  - Brak dedykowanej analizy składników z lodówki
  - Mniej precyzyjne dopasowanie do dostępnych produktów

#### Problem 3: Brak różnicowania typów posiłków
- **Opis**: Wszystkie posiłki (śniadanie, obiad, kolacja) wyszukiwane tymi samymi parametrami
- **Skutek**: Nieodpowiednie proporcje kaloryczne i typy dań

### ✅ Dostępne Zasoby

- **Model `UserPreference`**:
  - `diet_type`: omnivore, vegetarian, vegan, keto
  - `daily_calories`: cel dzienny (domyślnie 2000 kcal)
  - `allergies`: tablica alergenów
  - `exclude_ingredients`: tablica wykluczonych składników

- **Istniejące Metody w `SpoonacularService`**:
  - `searchRecipesByIngredients()` - gotowa implementacja `/recipes/findByIngredients`
  - `complexSearch()` - ogólne wyszukiwanie `/recipes/complexSearch`
  - `getRecipeInformation()` - szczegóły przepisu

---

## 2. Dostępne Endpointy API Spoonacular

### 2.1 `/recipes/findByIngredients`

**Przeznaczenie**: Wyszukiwanie przepisów na podstawie dostępnych składników ("co mam w lodówce")

**Zalety**:
- ✅ Automatycznie zwraca `usedIngredients` (składniki z lodówki użytkownika)
- ✅ Automatycznie zwraca `missedIngredients` (brakujące składniki)
- ✅ Ranking według wykorzystania składników
- ✅ Niższy koszt API
- ✅ Specjalnie zaprojektowany do tego scenariusza

**Parametry**:
```php
[
    'ingredients' => 'jabłka,mleko,jajka',  // Lista składników
    'number' => 100,                         // Liczba wyników
    'ranking' => 2,                          // 1=min missing, 2=max used
    'ignorePantry' => true,                  // Ignoruj podstawowe składniki
]
```

**Ograniczenia**:
- ❌ Brak parametru `type` (nie można filtrować po typie dania)
- ❌ Brak parametru `diet` (nie można filtrować po diecie)
- ❌ Brak filtrów żywieniowych (kalorie, białko, etc.)
- ❌ **Wymaga dodatkowego wywołania `getRecipeInformation()` dla szczegółów**

**Koszt API**: 1 punkt + 0.01 za każdy wynik

---

### 2.2 `/recipes/complexSearch`

**Przeznaczenie**: Zaawansowane wyszukiwanie z wieloma filtrami

**Zalety**:
- ✅ Parametr `type` - typ dania (main course, breakfast, dessert, etc.)
- ✅ Parametr `diet` - typ diety (vegetarian, vegan, ketogenic, etc.)
- ✅ Filtry żywieniowe: `minCalories`, `maxCalories`, `minProtein`, etc.
- ✅ Parametr `intolerances` - alergeny
- ✅ Parametr `excludeIngredients` - wykluczenie składników
- ✅ Parametr `includeIngredients` - preferowane składniki
- ✅ Opcje `addRecipeInformation`, `addRecipeNutrition`, `addRecipeInstructions`
- ✅ Sortowanie (popularity, healthiness, random, etc.)

**Parametry rozszerzające odpowiedź**:
```php
[
    'addRecipeInformation' => true,   // +0.025 per recipe - dodaje szczegóły
    'addRecipeNutrition' => true,     // +0.025 per recipe - dodaje wartości odżywcze
    'addRecipeInstructions' => true,  // +0.025 per recipe - dodaje instrukcje
    'fillIngredients' => true,        // +0.025 per recipe - tylko z includeIngredients!
]
```

**Ważne**: `fillIngredients` działa **tylko** gdy użyty jest `includeIngredients`!

**Koszt API**: 1 punkt + 0.01 za wynik + dodatkowe opłaty za rozszerzenia

---

### 2.3 `/recipes/{id}/information`

**Przeznaczenie**: Szczegółowe informacje o konkretnym przepisie

**Zwraca**:
- Pełne informacje o przepisie
- Listę składników (`extendedIngredients`)
- Instrukcje (`analyzedInstructions`)
- Wartości odżywcze (jeśli `includeNutrition=true`)

**Koszt API**: 1 punkt

---

## 3. Strategia dla Typów Posiłków

### 3.1 Podział Kaloryczny

Standardowy podział dzienny według zalecań dietetycznych:

| Posiłek | % Kalorii Dziennych | Przykład dla 2000 kcal | Zakres |
|---------|---------------------|------------------------|---------|
| **Śniadanie** | 25-30% | 500-600 kcal | 400-800 kcal |
| **Obiad** | 35-40% | 700-800 kcal | 600-1000 kcal |
| **Kolacja** | 25-30% | 500-600 kcal | 400-800 kcal |
| **Przekąska** | 5-10% | 100-200 kcal | 50-300 kcal |

### 3.2 Typy Dań dla Każdego Posiłku

#### Śniadanie (breakfast)
**Typy dań API**: `breakfast`, `brunch`, `appetizer` (lekkie)

**Charakterystyka**:
- Szybkie przygotowanie (preferowane < 30 min)
- Bogate w węglowodany i białko
- Przykłady: owsianka, jajecznica, smoothie bowl, tosty, naleśniki

**Parametry `complexSearch`**:
```php
[
    'type' => 'breakfast',
    'minCalories' => round($dailyCalories * 0.20),  // 20%
    'maxCalories' => round($dailyCalories * 0.35),  // 35%
    'maxReadyTime' => 30,  // max 30 minut
]
```

#### Obiad (lunch/dinner - główny posiłek)
**Typy dań API**: `main course`, `soup`, `salad`

**Charakterystyka**:
- Główny posiłek dnia - najwyższe kalorie
- Zbilansowany makroskładowo
- Czas przygotowania elastyczny
- Przykłady: kurczak z ryżem, makaron, zupa z mięsem, curry

**Parametry `complexSearch`**:
```php
[
    'type' => 'main course,soup',
    'minCalories' => round($dailyCalories * 0.30),  // 30%
    'maxCalories' => round($dailyCalories * 0.45),  // 45%
]
```

#### Kolacja (dinner/supper)
**Typy dań API**: `main course`, `salad`, `side dish`, `soup`

**Charakterystyka**:
- Średnio kaloryczny
- Lżejszy niż obiad
- Preferowane dania łatwo strawne
- Przykłady: ryba z warzywami, sałatka z kurczakiem, omlet

**Parametry `complexSearch`**:
```php
[
    'type' => 'main course,salad,side dish,soup',
    'minCalories' => round($dailyCalories * 0.20),  // 20%
    'maxCalories' => round($dailyCalories * 0.35),  // 35%
]
```

---

## 4. Logika Wyboru Endpointa

### 4.1 Drzewo Decyzyjne

```
START
│
├─ Czy użytkownik ma składniki w lodówce?
│  │
│  ├─ TAK (count($fridgeItems) > 0)
│  │  │
│  │  └─ Użyj HYBRYDOWEGO podejścia:
│  │     │
│  │     ├─ Krok 1: findByIngredients
│  │     │  └─ Znajdź przepisy dopasowane do lodówki
│  │     │     └─ Zwraca: usedIngredients, missedIngredients
│  │     │
│  │     ├─ Krok 2: getRecipeInformation (dla każdego)
│  │     │  └─ Pobierz pełne szczegóły + nutrition
│  │     │
│  │     └─ Krok 3: FILTRUJ lokalnie po:
│  │        ├─ Typ posiłku (breakfast/lunch/dinner)
│  │        ├─ Zakres kaloryczny
│  │        ├─ Dieta (diet_type)
│  │        └─ Alergeny (allergies)
│  │
│  └─ NIE (count($fridgeItems) == 0)
│     │
│     └─ Użyj BEZPOŚREDNIO complexSearch:
│        │
│        └─ Dla każdego typu posiłku oddzielnie:
│           ├─ Śniadanie: type=breakfast, minCal/maxCal
│           ├─ Obiad: type=main course, minCal/maxCal
│           └─ Kolacja: type=main course,salad, minCal/maxCal
│
END
```

### 4.2 Szczegółowa Implementacja

#### Scenariusz A: Użytkownik MA składniki w lodówce

```php
// 1. Użyj findByIngredients dla WSZYSTKICH przepisów
$params = [
    'ingredients' => implode(',', $fridgeItems),
    'number' => 300,  // Duża pula do wyboru
    'ranking' => 2,   // Maksymalizuj wykorzystanie składników
    'ignorePantry' => true,
];

$recipes = $spoonacularService->searchRecipesByIngredients(
    $fridgeItems,
    ['diet_type' => $dietType, 'allergies' => $allergies]
);

// 2. Pobierz szczegóły dla każdego przepisu
$detailedRecipes = [];
foreach ($recipes as $recipe) {
    $details = $spoonacularService->getRecipeInformation($recipe['id']);

    // 3. Połącz dane: podstawowe (usedIngredients, missedIngredients) + szczegóły
    $detailedRecipes[] = array_merge($recipe, $details);
}

// 4. FILTRUJ lokalnie
$breakfastRecipes = array_filter($detailedRecipes, function($recipe) {
    return $this->matchesMealType($recipe, 'breakfast')
        && $this->matchesCalorieRange($recipe, $minCal, $maxCal)
        && $this->matchesDiet($recipe, $dietType)
        && !$this->hasAllergens($recipe, $allergies);
});

// Powtórz dla lunch i dinner...
```

**Zalety**:
- ✅ Maksymalne wykorzystanie składników z lodówki
- ✅ Pełna analiza `usedIngredients` / `missedIngredients`
- ✅ Kontrola nad wszystkimi filtrami

**Wady**:
- ❌ Wymaga wielu wywołań `getRecipeInformation()` (koszt API)
- ❌ Filtrowanie lokalne po typie dania (mniej precyzyjne)

**Optymalizacja kosztów**:
- Buforuj wyniki `getRecipeInformation()` w bazie danych
- Ogranicz liczbę pobieranych szczegółów (np. tylko top 50 przepisów)

---

#### Scenariusz B: Użytkownik NIE MA składników w lodówce

```php
// Wykonaj 3 oddzielne zapytania - po jednym dla każdego typu posiłku

// 1. ŚNIADANIE
$breakfastParams = [
    'type' => 'breakfast',
    'minCalories' => round($dailyCalories * 0.20),
    'maxCalories' => round($dailyCalories * 0.35),
    'maxReadyTime' => 30,
    'diet' => $dietType !== 'omnivore' ? $dietType : null,
    'intolerances' => implode(',', $allergies),
    'number' => 50,
    'addRecipeNutrition' => true,
    'addRecipeInformation' => true,
    'addRecipeInstructions' => true,
    'sort' => 'random',
];

$breakfastRecipes = $spoonacularService->complexSearch($breakfastParams);

// 2. OBIAD (główny posiłek)
$lunchParams = [
    'type' => 'main course,soup',
    'minCalories' => round($dailyCalories * 0.30),
    'maxCalories' => round($dailyCalories * 0.45),
    'diet' => $dietType !== 'omnivore' ? $dietType : null,
    'intolerances' => implode(',', $allergies),
    'number' => 50,
    'addRecipeNutrition' => true,
    'addRecipeInformation' => true,
    'addRecipeInstructions' => true,
    'sort' => 'random',
];

$lunchRecipes = $spoonacularService->complexSearch($lunchParams);

// 3. KOLACJA
$dinnerParams = [
    'type' => 'main course,salad,side dish,soup',
    'minCalories' => round($dailyCalories * 0.20),
    'maxCalories' => round($dailyCalories * 0.35),
    'diet' => $dietType !== 'omnivore' ? $dietType : null,
    'intolerances' => implode(',', $allergies),
    'number' => 50,
    'addRecipeNutrition' => true,
    'addRecipeInformation' => true,
    'addRecipeInstructions' => true,
    'sort' => 'random',
];

$dinnerRecipes = $spoonacularService->complexSearch($dinnerParams);
```

**Zalety**:
- ✅ Precyzyjne dopasowanie typu dania przez API
- ✅ Filtrowanie kaloryczne po stronie API
- ✅ Wszystkie szczegóły w jednym zapytaniu
- ✅ Mniej wywołań API

**Wady**:
- ❌ Brak analizy składników z lodówki
- ❌ Wyższy koszt pojedynczego zapytania (rozszerzenia)

---

## 5. Struktura Danych i Ujednolicenie

### 5.1 Problem: Różne Struktury z Różnych Endpointów

#### Endpoint: `/recipes/findByIngredients`
```json
{
  "id": 716429,
  "title": "Pasta with Garlic, Scallions...",
  "image": "https://...",
  "usedIngredients": [
    {
      "id": 11215,
      "name": "garlic",
      "amount": 1,
      "unit": "clove",
      "image": "garlic.png"
    }
  ],
  "missedIngredients": [
    {
      "id": 11282,
      "name": "onion",
      "amount": 1,
      "unit": "",
      "image": "onion.png"
    }
  ],
  "unusedIngredients": [],
  "likes": 584
}
```
**❌ BRAK**: nutrition, instructions, extendedIngredients, readyInMinutes

---

#### Endpoint: `/recipes/complexSearch` (z rozszerzeniami)
```json
{
  "results": [
    {
      "id": 716429,
      "title": "Pasta with Garlic...",
      "image": "https://...",
      "imageType": "jpg",
      "readyInMinutes": 45,
      "servings": 4,
      "sourceUrl": "https://...",
      "nutrition": {
        "nutrients": [
          {"name": "Calories", "amount": 584.88, "unit": "kcal"}
        ]
      },
      "analyzedInstructions": [...],
      "extendedIngredients": [...]
    }
  ]
}
```
**❌ BRAK**: usedIngredients, missedIngredients (chyba że fillIngredients + includeIngredients)

---

#### Endpoint: `/recipes/{id}/information`
```json
{
  "id": 716429,
  "title": "Pasta with Garlic...",
  "image": "https://...",
  "readyInMinutes": 45,
  "servings": 4,
  "extendedIngredients": [...],
  "analyzedInstructions": [...],
  "nutrition": {  // tylko jeśli includeNutrition=true
    "nutrients": [...]
  }
}
```
**❌ BRAK**: usedIngredients, missedIngredients

---

### 5.2 Ujednolicona Struktura Danych (Target)

```php
[
    // Podstawowe info
    'id' => 716429,
    'title' => 'Pasta with Garlic...',
    'image' => 'https://...',
    'readyInMinutes' => 45,
    'servings' => 4,
    'sourceUrl' => 'https://...',

    // Typ posiłku (określony lokalnie)
    'meal_type' => 'breakfast|lunch|dinner|snack',

    // Składniki - analiza dopasowania do lodówki
    'usedIngredients' => [
        ['id' => 11215, 'name' => 'garlic', 'amount' => 1, 'unit' => 'clove']
    ],
    'missedIngredients' => [
        ['id' => 11282, 'name' => 'onion', 'amount' => 1, 'unit' => '']
    ],
    'usedIngredientCount' => 3,
    'missedIngredientCount' => 1,

    // Szczegółowe składniki
    'extendedIngredients' => [
        ['id' => 11215, 'name' => 'garlic', 'original' => '1 clove garlic', ...]
    ],

    // Wartości odżywcze
    'calories' => 584.88,
    'nutrition' => [
        'nutrients' => [
            ['name' => 'Calories', 'amount' => 584.88, 'unit' => 'kcal'],
            ['name' => 'Protein', 'amount' => 25.5, 'unit' => 'g'],
            // ...
        ]
    ],

    // Instrukcje
    'analyzedInstructions' => [
        [
            'name' => '',
            'steps' => [
                ['number' => 1, 'step' => 'Preheat oven...', 'ingredients' => [...]]
            ]
        ]
    ],
    'hasInstructions' => true,

    // Metadane
    'likes' => 584,
    'source' => 'findByIngredients|complexSearch',

    // Pełne dane (do zapisu w DB)
    'full_recipe_data' => [...] // oryginalny JSON
]
```

---

## 6. Obsługa Preferencji Użytkownika

### 6.1 Model UserPreference

```php
class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'diet_type',        // 'omnivore', 'vegetarian', 'vegan', 'keto'
        'daily_calories',   // int: 1500-4000
        'allergies',        // array: ['Dairy', 'Egg', 'Gluten', 'Peanut', ...]
        'exclude_ingredients', // array: ['onion', 'mushroom', ...]
    ];
}
```

### 6.2 Mapowanie Preferencji na Parametry API

#### Diet Type
```php
$dietMapping = [
    'omnivore' => null,           // Nie przekazuj parametru 'diet'
    'vegetarian' => 'vegetarian',
    'vegan' => 'vegan',
    'keto' => 'ketogenic',        // API używa 'ketogenic', nie 'keto'
];
```

**Dodatkowe parametry dla diety keto**:
```php
if ($dietType === 'keto') {
    $params['maxCarbs'] = 50;      // Max 50g węglowodanów
    $params['minFat'] = 20;        // Min 20g tłuszczu
}
```

#### Allergies (Intolerances)
```php
// API Spoonacular obsługuje te alergeny:
$supportedIntolerances = [
    'Dairy', 'Egg', 'Gluten', 'Grain', 'Peanut', 'Seafood',
    'Sesame', 'Shellfish', 'Soy', 'Sulfite', 'Tree Nut', 'Wheat'
];

// Przykład użycia:
$params['intolerances'] = implode(',', $user->preferences->allergies);
```

#### Exclude Ingredients
```php
// Wyklucz niechciane składniki
$params['excludeIngredients'] = implode(',', $user->preferences->exclude_ingredients);
```

#### Daily Calories
```php
$dailyCalories = $user->preferences->daily_calories ?? 2000;

// Dla śniadania:
$params['minCalories'] = round($dailyCalories * 0.20);  // 20%
$params['maxCalories'] = round($dailyCalories * 0.35);  // 35%
```

---

## 7. Plan Implementacji Krok Po Kroku

### FAZA 1: Przygotowanie SpoonacularService (1-2 godziny)

#### ✅ Task 1.1: Zaktualizuj `searchRecipesByIngredients()`
**Plik**: `app/Services/SpoonacularService.php`

**Zmiany**:
- Usuń parametr `type` (nie jest obsługiwany przez endpoint)
- Usuń parametr `diet` (nie jest obsługiwany)
- Skoncentruj się tylko na składnikach i `ranking`

```php
public function searchRecipesByIngredients(array $ingredients, array $preferences = []): array
{
    $params = [
        'apiKey' => $this->apiKey,
        'ingredients' => implode(',', $ingredients),
        'number' => 300,
        'ranking' => 2,  // Maximize used ingredients
        'ignorePantry' => true,
    ];

    // NIE DODAWAJ 'type', 'diet' - endpoint tego nie obsługuje!

    $response = Http::get("{$this->baseUrl}/recipes/findByIngredients", $params);
    // ... reszta bez zmian
}
```

---

#### ✅ Task 1.2: Zaktualizuj `getRecipeInformation()`
**Plik**: `app/Services/SpoonacularService.php`

**Upewnij się, że zawsze pobiera nutrition**:
```php
public function getRecipeInformation(int $recipeId): array
{
    $response = Http::get("{$this->baseUrl}/recipes/{$recipeId}/information", [
        'apiKey' => $this->apiKey,
        'includeNutrition' => 'true',  // ZAWSZE true
    ]);
    // ... reszta bez zmian
}
```

---

#### ✅ Task 1.3: Dodaj metodę `complexSearchByMealType()`
**Plik**: `app/Services/SpoonacularService.php`

**Dodaj nową metodę**:
```php
/**
 * Complex search specifically for a meal type with calorie constraints.
 *
 * @param string $mealType 'breakfast', 'lunch', 'dinner'
 * @param int $dailyCalories
 * @param array $preferences
 * @return array
 */
public function complexSearchByMealType(
    string $mealType,
    int $dailyCalories,
    array $preferences = []
): array {
    // Określ typ dania i zakresy kaloryczne
    $typeMapping = [
        'breakfast' => [
            'type' => 'breakfast',
            'minCalPercent' => 0.20,
            'maxCalPercent' => 0.35,
            'maxReadyTime' => 30,
        ],
        'lunch' => [
            'type' => 'main course,soup',
            'minCalPercent' => 0.30,
            'maxCalPercent' => 0.45,
        ],
        'dinner' => [
            'type' => 'main course,salad,side dish,soup',
            'minCalPercent' => 0.20,
            'maxCalPercent' => 0.35,
        ],
    ];

    $config = $typeMapping[$mealType] ?? $typeMapping['lunch'];

    $params = [
        'type' => $config['type'],
        'minCalories' => round($dailyCalories * $config['minCalPercent']),
        'maxCalories' => round($dailyCalories * $config['maxCalPercent']),
        'number' => 50,
        'addRecipeNutrition' => true,
        'addRecipeInformation' => true,
        'addRecipeInstructions' => true,
        'sort' => 'random',
        'offset' => rand(0, 100), // Randomizacja
    ];

    // Dodaj maxReadyTime dla śniadania
    if (isset($config['maxReadyTime'])) {
        $params['maxReadyTime'] = $config['maxReadyTime'];
    }

    // Dieta
    if (isset($preferences['diet_type']) && $preferences['diet_type'] !== 'omnivore') {
        $dietMapping = [
            'vegetarian' => 'vegetarian',
            'vegan' => 'vegan',
            'keto' => 'ketogenic',
        ];
        $params['diet'] = $dietMapping[$preferences['diet_type']] ?? null;

        // Dodatkowe parametry dla keto
        if ($preferences['diet_type'] === 'keto') {
            $params['maxCarbs'] = 50;
            $params['minFat'] = 20;
        }
    }

    // Alergeny
    if (!empty($preferences['allergies'])) {
        $params['intolerances'] = implode(',', $preferences['allergies']);
    }

    // Wykluczone składniki
    if (!empty($preferences['exclude_ingredients'])) {
        $params['excludeIngredients'] = implode(',', $preferences['exclude_ingredients']);
    }

    return $this->complexSearch($params);
}
```

---

### FAZA 2: Refaktoryzacja MealPlannerService (3-4 godziny)

#### ✅ Task 2.1: Dodaj metodę pomocniczą `normalizeRecipeData()`
**Plik**: `app/Services/MealPlannerService.php`

**Cel**: Ujednolicenie struktury danych z różnych źródeł

```php
/**
 * Normalize recipe data from different API sources into unified format.
 *
 * @param array $recipe Base recipe data
 * @param array $detailedInfo Optional detailed information from getRecipeInformation()
 * @param string $source 'findByIngredients' or 'complexSearch'
 * @return array Normalized recipe structure
 */
protected function normalizeRecipeData(
    array $recipe,
    ?array $detailedInfo = null,
    string $source = 'complexSearch'
): array {
    // Merge base + detailed
    $merged = $detailedInfo ? array_merge($recipe, $detailedInfo) : $recipe;

    // Extract calories
    $calories = 0;
    if (isset($merged['nutrition']['nutrients'])) {
        foreach ($merged['nutrition']['nutrients'] as $nutrient) {
            if ($nutrient['name'] === 'Calories') {
                $calories = $nutrient['amount'];
                break;
            }
        }
    }

    // Determine meal type from recipe metadata
    $mealType = $this->determineMealTypeFromRecipe($merged);

    // Check if has instructions
    $hasInstructions = !empty($merged['analyzedInstructions'])
        || !empty($merged['instructions']);

    return [
        // Basic
        'id' => $merged['id'],
        'title' => $merged['title'],
        'image' => $merged['image'] ?? null,
        'readyInMinutes' => $merged['readyInMinutes'] ?? null,
        'servings' => $merged['servings'] ?? 2,
        'sourceUrl' => $merged['sourceUrl'] ?? null,

        // Meal type
        'meal_type' => $mealType,

        // Ingredients analysis (from findByIngredients)
        'usedIngredients' => $merged['usedIngredients'] ?? [],
        'missedIngredients' => $merged['missedIngredients'] ?? [],
        'usedIngredientCount' => $merged['usedIngredientCount'] ?? count($merged['usedIngredients'] ?? []),
        'missedIngredientCount' => $merged['missedIngredientCount'] ?? count($merged['missedIngredients'] ?? []),

        // Extended ingredients
        'extendedIngredients' => $merged['extendedIngredients'] ?? [],

        // Nutrition
        'calories' => $calories,
        'nutrition' => $merged['nutrition'] ?? [],

        // Instructions
        'analyzedInstructions' => $merged['analyzedInstructions'] ?? [],
        'hasInstructions' => $hasInstructions,

        // Metadata
        'likes' => $merged['likes'] ?? $merged['aggregateLikes'] ?? 0,
        'source' => $source,

        // Full data for DB storage
        'full_recipe_data' => $merged,
    ];
}
```

---

#### ✅ Task 2.2: Dodaj metodę `determineMealTypeFromRecipe()`
**Plik**: `app/Services/MealPlannerService.php`

```php
/**
 * Determine meal type from recipe metadata (dishTypes, title, etc.)
 *
 * @param array $recipe
 * @return string 'breakfast', 'lunch', 'dinner', or 'snack'
 */
protected function determineMealTypeFromRecipe(array $recipe): string
{
    // Check dishTypes first (most reliable)
    if (isset($recipe['dishTypes']) && is_array($recipe['dishTypes'])) {
        foreach ($recipe['dishTypes'] as $dishType) {
            $dishType = strtolower($dishType);

            if (in_array($dishType, ['breakfast', 'brunch', 'morning meal'])) {
                return 'breakfast';
            }

            if (in_array($dishType, ['lunch', 'main course', 'main dish'])) {
                // Could be lunch or dinner - check time context or default to lunch
                return 'lunch';
            }

            if (in_array($dishType, ['dinner'])) {
                return 'dinner';
            }

            if (in_array($dishType, ['snack', 'appetizer', 'fingerfood'])) {
                return 'snack';
            }
        }
    }

    // Fallback to title analysis
    $title = strtolower($recipe['title'] ?? '');

    if (str_contains($title, 'breakfast') || str_contains($title, 'pancake')
        || str_contains($title, 'oatmeal') || str_contains($title, 'smoothie')) {
        return 'breakfast';
    }

    if (str_contains($title, 'dinner') || str_contains($title, 'supper')) {
        return 'dinner';
    }

    // Default to lunch for main courses
    return 'lunch';
}
```

---

#### ✅ Task 2.3: Dodaj metodę `fetchRecipesWithFridgeItems()`
**Plik**: `app/Services/MealPlannerService.php`

**Scenariusz A**: Gdy użytkownik ma składniki

```php
/**
 * Fetch recipes using user's fridge items with full details.
 *
 * @param array $fridgeItems
 * @param array $preferences
 * @param int $dailyCalories
 * @return array Normalized recipes
 */
protected function fetchRecipesWithFridgeItems(
    array $fridgeItems,
    array $preferences,
    int $dailyCalories
): array {
    // Step 1: Use findByIngredients to get recipes with ingredient analysis
    $baseRecipes = $this->spoonacularService->searchRecipesByIngredients(
        $fridgeItems,
        $preferences
    );

    if (isset($baseRecipes['error']) || empty($baseRecipes)) {
        Log::warning('findByIngredients returned no results', [
            'fridge_items' => $fridgeItems
        ]);
        return [];
    }

    Log::info('findByIngredients returned recipes', [
        'count' => count($baseRecipes)
    ]);

    // Step 2: Fetch detailed information for top recipes (limit to save API calls)
    $topRecipes = array_slice($baseRecipes, 0, 50);  // Limit to top 50
    $detailedRecipes = [];

    foreach ($topRecipes as $recipe) {
        $details = $this->spoonacularService->getRecipeInformation($recipe['id']);

        if (isset($details['error'])) {
            continue;
        }

        // Step 3: Normalize and merge data
        $normalized = $this->normalizeRecipeData($recipe, $details, 'findByIngredients');

        // Skip if no calories
        if ($normalized['calories'] <= 0) {
            continue;
        }

        $detailedRecipes[] = $normalized;
    }

    Log::info('Fetched detailed recipes with fridge items', [
        'total' => count($detailedRecipes),
        'with_nutrition' => count(array_filter($detailedRecipes, fn($r) => $r['calories'] > 0))
    ]);

    return $detailedRecipes;
}
```

---

#### ✅ Task 2.4: Dodaj metodę `fetchRecipesByMealTypes()`
**Plik**: `app/Services/MealPlannerService.php`

**Scenariusz B**: Gdy użytkownik NIE ma składników

```php
/**
 * Fetch recipes using complexSearch for each meal type.
 *
 * @param array $preferences
 * @param int $dailyCalories
 * @return array ['breakfast' => [...], 'lunch' => [...], 'dinner' => [...]]
 */
protected function fetchRecipesByMealTypes(
    array $preferences,
    int $dailyCalories
): array {
    $recipesByMealType = [
        'breakfast' => [],
        'lunch' => [],
        'dinner' => [],
    ];

    foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
        $result = $this->spoonacularService->complexSearchByMealType(
            $mealType,
            $dailyCalories,
            $preferences
        );

        if (isset($result['error']) || !isset($result['results'])) {
            Log::error("complexSearch failed for {$mealType}", [
                'error' => $result['error'] ?? 'Unknown error'
            ]);
            continue;
        }

        // Normalize recipes
        foreach ($result['results'] as $recipe) {
            $normalized = $this->normalizeRecipeData($recipe, null, 'complexSearch');

            // Skip if no calories
            if ($normalized['calories'] <= 0) {
                continue;
            }

            // Override meal type with our classification
            $normalized['meal_type'] = $mealType;

            $recipesByMealType[$mealType][] = $normalized;
        }

        Log::info("complexSearch returned recipes for {$mealType}", [
            'count' => count($recipesByMealType[$mealType])
        ]);
    }

    return $recipesByMealType;
}
```

---

#### ✅ Task 2.5: Dodaj metodę `filterRecipesByMealType()`
**Plik**: `app/Services/MealPlannerService.php`

```php
/**
 * Filter recipes for a specific meal type and calorie range.
 *
 * @param array $recipes All recipes
 * @param string $mealType 'breakfast', 'lunch', 'dinner'
 * @param int $dailyCalories
 * @return array Filtered recipes
 */
protected function filterRecipesByMealType(
    array $recipes,
    string $mealType,
    int $dailyCalories
): array {
    // Define calorie ranges per meal type
    $ranges = [
        'breakfast' => ['min' => 0.20, 'max' => 0.35],
        'lunch' => ['min' => 0.30, 'max' => 0.45],
        'dinner' => ['min' => 0.20, 'max' => 0.35],
    ];

    $range = $ranges[$mealType] ?? $ranges['lunch'];
    $minCal = round($dailyCalories * $range['min']);
    $maxCal = round($dailyCalories * $range['max']);

    // Define dish types per meal
    $dishTypes = [
        'breakfast' => ['breakfast', 'brunch', 'morning meal'],
        'lunch' => ['main course', 'soup', 'salad', 'main dish'],
        'dinner' => ['main course', 'salad', 'side dish', 'soup', 'main dish'],
    ];

    $allowedDishTypes = $dishTypes[$mealType] ?? [];

    return array_filter($recipes, function($recipe) use ($minCal, $maxCal, $allowedDishTypes, $mealType) {
        // Check calories
        if ($recipe['calories'] < $minCal || $recipe['calories'] > $maxCal) {
            return false;
        }

        // Check dish types if available
        if (isset($recipe['full_recipe_data']['dishTypes'])) {
            $recipeDishTypes = array_map('strtolower', $recipe['full_recipe_data']['dishTypes']);

            $matches = array_intersect($recipeDishTypes, $allowedDishTypes);

            if (empty($matches)) {
                return false;
            }
        }

        return true;
    });
}
```

---

#### ✅ Task 2.6: PRZEPISZ główną metodę `generateMealPlanForUser()`
**Plik**: `app/Services/MealPlannerService.php`

```php
/**
 * Generate a meal plan for a user based on their fridge items and preferences.
 *
 * @param User $user
 * @param string $date
 * @return MealPlan|null
 */
public function generateMealPlanForUser(User $user, string $date): ?MealPlan
{
    try {
        // Get user preferences
        $preferences = $user->preferences;
        $dailyCalories = $preferences->daily_calories ?? 2000;

        // Get user's fridge items
        $fridgeItems = $user->fridgeItems()->pluck('product_name')->toArray();

        // Prepare preferences array
        $preferencesArray = [
            'diet_type' => $preferences->diet_type ?? 'omnivore',
            'allergies' => $preferences->allergies ?? [],
            'exclude_ingredients' => $preferences->exclude_ingredients ?? [],
            'daily_calories' => $dailyCalories,
        ];

        // Get recently used recipe IDs to avoid repetition
        $recentRecipeIds = $user->mealPlans()
            ->where('date', '>=', now()->subDays(30))
            ->with('recipes')
            ->get()
            ->pluck('recipes')
            ->flatten()
            ->pluck('spoonacular_recipe_id')
            ->unique()
            ->toArray();

        Log::info('Starting meal plan generation', [
            'user_id' => $user->id,
            'date' => $date,
            'daily_calories' => $dailyCalories,
            'has_fridge_items' => !empty($fridgeItems),
            'fridge_item_count' => count($fridgeItems),
        ]);

        // === DECISION TREE: Choose strategy based on fridge items ===

        if (!empty($fridgeItems)) {
            // === SCENARIO A: User HAS fridge items ===
            Log::info('Using HYBRID strategy with fridge items');

            $allRecipes = $this->fetchRecipesWithFridgeItems(
                $fridgeItems,
                $preferencesArray,
                $dailyCalories
            );

            if (empty($allRecipes)) {
                Log::error('No recipes found with fridge items');
                return null;
            }

            // Filter recipes by meal type and calorie ranges
            $recipesByMealType = [
                'breakfast' => $this->filterRecipesByMealType($allRecipes, 'breakfast', $dailyCalories),
                'lunch' => $this->filterRecipesByMealType($allRecipes, 'lunch', $dailyCalories),
                'dinner' => $this->filterRecipesByMealType($allRecipes, 'dinner', $dailyCalories),
            ];

        } else {
            // === SCENARIO B: User has NO fridge items ===
            Log::info('Using DIRECT complexSearch strategy (no fridge items)');

            $recipesByMealType = $this->fetchRecipesByMealTypes(
                $preferencesArray,
                $dailyCalories
            );
        }

        // Check if we have recipes for each meal type
        foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
            if (empty($recipesByMealType[$mealType])) {
                Log::error("No recipes found for {$mealType}");
                return null;
            }
        }

        Log::info('Recipes filtered by meal type', [
            'breakfast_count' => count($recipesByMealType['breakfast']),
            'lunch_count' => count($recipesByMealType['lunch']),
            'dinner_count' => count($recipesByMealType['dinner']),
        ]);

        // === Use VertexAI to select best recipe for each meal ===

        $selectedRecipes = [];

        foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
            $candidates = $recipesByMealType[$mealType];

            // Use AI to select best recipe
            $selectedIds = $this->vertexAIService->selectBestRecipes(
                $candidates,
                $preferencesArray,
                $recentRecipeIds,
                $fridgeItems,
                1  // Select only 1 recipe per meal type
            );

            if (empty($selectedIds)) {
                // Fallback: random selection
                shuffle($candidates);
                $selectedRecipe = $candidates[0];
            } else {
                $selectedRecipe = array_filter($candidates, fn($r) => $r['id'] === $selectedIds[0])[0] ?? $candidates[0];
            }

            $selectedRecipes[] = $selectedRecipe;
        }

        Log::info('Selected final recipes', [
            'recipe_ids' => array_column($selectedRecipes, 'id'),
            'recipe_titles' => array_column($selectedRecipes, 'title'),
            'calories' => array_column($selectedRecipes, 'calories'),
            'total_calories' => array_sum(array_column($selectedRecipes, 'calories')),
        ]);

        // Create meal plan from selected recipes
        return $this->createMealPlanFromRecipes($user, $date, $selectedRecipes, $dailyCalories);

    } catch (\Exception $e) {
        Log::error('Meal Planner Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return null;
    }
}
```

---

### FAZA 3: Aktualizacja VertexAIService (1 godzina)

#### ✅ Task 3.1: Zaktualizuj sygnaturę `selectBestRecipes()`
**Plik**: `app/Services/VertexAIService.php`

**Dodaj parametr `$limit` aby kontrolować liczbę wybranych przepisów**:

```php
public function selectBestRecipes(
    array $recipes,
    array $preferences,
    array $recentRecipeIds = [],
    array $fridgeItems = [],
    int $limit = 3  // NOWY PARAMETR: liczba przepisów do wyboru
): array
```

**W prompt dla AI dodaj**:
- Informację o typie posiłku (jeśli dostępna w `$recipe['meal_type']`)
- Informację o wykorzystanych składnikach (`usedIngredients`)
- Limit wyników do zwrócenia

---

### FAZA 4: Testowanie (2-3 godziny)

#### ✅ Test 1: Użytkownik z pełną lodówką (10+ składników)
**Oczekiwany wynik**:
- Użycie `findByIngredients`
- 3 posiłki z maksymalnym wykorzystaniem składników
- Różnorodne typy posiłków (breakfast, main course, salad/soup)

#### ✅ Test 2: Użytkownik z pustą lodówką
**Oczekiwany wynik**:
- 3 oddzielne zapytania `complexSearch`
- Każdy posiłek z odpowiedniego typu
- Prawidłowe zakresy kaloryczne

#### ✅ Test 3: Użytkownik z dietą vegan + alergie (Dairy, Egg)
**Oczekiwany wynik**:
- Wszystkie przepisy vegan
- Brak produktów mlecznych i jaj

#### ✅ Test 4: Użytkownik z dietą keto + 2500 kcal
**Oczekiwany wynik**:
- Przepisy z niskimi węglowodanami (< 50g)
- Wysokie tłuszcze (> 20g)
- Suma kalorii ≈ 2500 kcal (± 100 kcal)

---

## 8. Testowanie i Walidacja

### 8.1 Checklist Jakości

- [ ] **Różnorodność typów posiłków**: Śniadanie ≠ Obiad ≠ Kolacja
- [ ] **Dokładność kaloryczna**: Suma kalorii w planie ± 10% od celu
- [ ] **Zgodność z dietą**: Wszystkie przepisy zgodne z `diet_type`
- [ ] **Brak alergenów**: Weryfikacja parametru `intolerances`
- [ ] **Wykorzystanie lodówki**: Jeśli są składniki, przynajmniej 1 przepis z > 50% wykorzystaniem
- [ ] **Brak powtórek**: Przepisy spoza ostatnich 30 dni
- [ ] **Instrukcje gotowania**: Wszystkie przepisy mają `analyzedInstructions`
- [ ] **Tłumaczenia**: Tytuły, składniki, kroki w języku polskim

---

### 8.2 Monitoring i Logi

**Kluczowe punkty logowania**:

```php
Log::info('Meal plan generation started', [
    'strategy' => 'hybrid|direct',
    'fridge_items_count' => count($fridgeItems),
]);

Log::info('API call', [
    'endpoint' => 'findByIngredients|complexSearch',
    'params' => $params,
    'results_count' => count($results),
]);

Log::info('Recipes filtered', [
    'before' => $beforeCount,
    'after' => $afterCount,
    'meal_type' => $mealType,
]);

Log::info('Final meal plan', [
    'breakfast' => ['id' => ..., 'calories' => ...],
    'lunch' => ['id' => ..., 'calories' => ...],
    'dinner' => ['id' => ..., 'calories' => ...],
    'total_calories' => $totalCalories,
    'target_calories' => $targetCalories,
    'deviation' => abs($totalCalories - $targetCalories),
]);
```

---

### 8.3 Optymalizacja Kosztów API

**Obecny koszt dla 1 użytkownika (scenariusz A - z lodówką)**:
1. `findByIngredients`: 1 punkt + 300 * 0.01 = **4 punkty**
2. `getRecipeInformation` (50x): 50 * 1 = **50 punktów**
3. **TOTAL: ~54 punkty**

**Optymalizacje**:
- [ ] **Cache `getRecipeInformation()` w Redis**: TTL 24h, oszczędność 90%
- [ ] **Limit do 30 szczegółowych przepisów** zamiast 50: oszczędność 40%
- [ ] **Batch processing**: Grupuj wiele użytkowników w jednym oknie czasowym

**Obecny koszt (scenariusz B - bez lodówki)**:
1. `complexSearch` breakfast: 1 + 50*0.01 + 50*0.075 = **5.25 punktów**
2. `complexSearch` lunch: **5.25 punktów**
3. `complexSearch` dinner: **5.25 punktów**
4. **TOTAL: ~16 punktów**

**Wniosek**: Scenariusz B jest **3x tańszy** niż scenariusz A!

---

## 📊 Podsumowanie

### Kluczowe Zmiany

1. **Hybrydowe podejście do API**:
   - Z lodówką: `findByIngredients` + `getRecipeInformation`
   - Bez lodówki: `complexSearch` z parametrami specyficznymi dla posiłku

2. **Różnicowanie typów posiłków**:
   - Śniadanie: type=breakfast, 20-35% kalorii, < 30 min
   - Obiad: type=main course/soup, 30-45% kalorii
   - Kolacja: type=main course/salad, 20-35% kalorii

3. **Ujednolicona struktura danych**:
   - Metoda `normalizeRecipeData()` łączy dane z różnych źródeł
   - Spójna struktura niezależnie od endpointa

4. **Lepsza obsługa preferencji**:
   - Parametry `diet`, `intolerances`, `excludeIngredients` w każdym zapytaniu
   - Specjalne zasady dla diety keto (maxCarbs, minFat)

---

### Następne Kroki

1. ✅ **Zaakceptuj plan**
2. 🔨 **Implementacja**: Postępuj zgodnie z fazami 1-4
3. 🧪 **Testowanie**: Wykonaj wszystkie 4 testy
4. 📈 **Monitoring**: Obserwuj logi i koszty API przez 1 tydzień
5. 🚀 **Optymalizacja**: Wprowadź cache i inne usprawnienia

---

**Autor**: Claude Code
**Data**: 2026-01-25
**Status**: ✅ Gotowy do implementacji
