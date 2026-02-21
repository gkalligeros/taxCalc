# AGENTS.md — Project Guide for AI Agents

## Project Overview

A salary/tax calculator web application built with **Laravel 11** (PHP backend) and **Vue 3 + Inertia.js** (SPA frontend). It computes net salary from gross (and vice-versa) using configurable tax scales, brackets, deductions, and overrides per country/state.

## Tech Stack

- **Backend:** PHP 8.x, Laravel 11, Inertia.js server-side adapter
- **Frontend:** Vue 3 (Composition API, `<script setup>`), Inertia.js client adapter, Tailwind CSS 4, Chart.js + vue-chartjs
- **Build:** Vite 7, Laravel Vite Plugin
- **Database:** MySQL/SQLite (via Eloquent ORM)

## Directory Structure

```
taxCalc/
├── app/
│   ├── Http/Controllers/     # Laravel controllers
│   │   ├── CalculatorController.php  # Main calculator (index + API)
│   │   ├── ScaleController.php       # Admin CRUD for tax scales
│   │   ├── BracketController.php     # Tax bracket management
│   │   ├── BracketOverrideController.php
│   │   └── DeductionController.php
│   ├── Http/Middleware/
│   │   ├── HandleInertiaRequests.php # Shares locale/translations with frontend
│   │   └── SetLocale.php
│   ├── Models/               # Eloquent models
│   │   ├── TaxScale.php      # Has many brackets & deductions
│   │   ├── TaxBracket.php    # Progressive tax bracket
│   │   ├── TaxBracketOverride.php  # Age/children-based rate overrides
│   │   └── Deduction.php     # Pre-tax deductions (e.g. social security)
│   └── Services/
│       └── SalaryCalculator.php  # Core calculation logic
├── resources/js/
│   ├── app.js                # Vue/Inertia bootstrap
│   ├── composables/
│   │   └── useI18n.js        # i18n composable (locale, translations)
│   └── Pages/
│       ├── Calculator.vue    # Main public calculator page
│       └── Admin/Scales/     # Admin pages for managing tax scales
├── lang/
│   ├── en/ui.php             # English translations
│   └── el/ui.php             # Greek translations
├── routes/
│   ├── web.php               # Inertia page routes
│   └── api.php               # POST /api/calculate endpoint
└── database/                 # Migrations and seeders
```

## Key Conventions

### Backend
- **Service layer:** Business logic lives in `app/Services/`, not in controllers.
- **Named parameters:** PHP method calls use named arguments for clarity.
- **Validation:** Done in controllers via `$request->validate()`.
- **API responses:** The calculator API returns flat JSON with keys like `gross`, `net`, `tax`, `total_deductions`, `tax_breakdown`, `deductions_breakdown`, `tax_exemption_amount`.

### Frontend
- **Composition API only:** All components use `<script setup>` syntax.
- **Inertia.js:** Page navigation uses `router.get()`. API calls to `/api/calculate` use `fetch()` directly.
- **i18n:** Translations come from Laravel PHP files, shared via Inertia middleware. Use `t('key')` from `useI18n` composable.
- **Styling:** Tailwind CSS utility classes. No separate CSS files.
- **Auto-calculate:** The calculator debounces input changes (250ms) and auto-triggers calculation.

### Translations
- All user-facing strings must have entries in both `lang/en/ui.php` and `lang/el/ui.php`.
- Translation keys use `snake_case`.

### Tax Calculation Flow
1. Gross salary input
2. Subtract deductions (social security, etc.) → taxable income
3. Apply tax exemption rate (e.g. Article 5C: 50%) → reduced taxable income
4. Apply progressive tax brackets on the reduced taxable income
5. Net = taxable income (before exemption) - tax

### Testing
- Run backend tests: `php artisan test`
- Dev server: `php artisan serve` + `npm run dev`
