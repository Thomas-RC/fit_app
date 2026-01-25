# Przewodnik stylów FIT AI

## 1. Główne pliki stylów

- **[resources/css/app.css](laravel/resources/css/app.css)** - Wszystkie komponenty CSS (przyciski, karty, odznaki)
- **[tailwind.config.js](laravel/tailwind.config.js)** - Kolory `fit-green` i konfiguracja Tailwind

## 2. Dodawanie nowych stylów

```css
/* W resources/css/app.css dodaj w sekcji @layer components */
.twoja-klasa {
    @apply bg-fit-green-500 text-white rounded-lg p-4;
}
```

## 3. Używanie w plikach Blade

```php
<!-- Używaj gotowych klas z app.css -->
<div class="fit-card green-bg-stripes p-8">
    <button class="btn-fit-primary">Zapisz</button>
</div>
```

**Po zmianach uruchom:** `npm run dev` lub `npm run build`
