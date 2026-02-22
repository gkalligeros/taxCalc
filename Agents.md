# taxCalc — Agent Code Structure Guide

## Stack
- **Backend**: Laravel 11 (PHP), Inertia.js server-side adapter
- **Frontend**: Vue 3 (Composition API), Inertia.js client-side adapter, Tailwind CSS, Chart.js (via vue-chartjs)
- **Database**: MySQL
- **i18n**: Laravel `lang/` files (PHP arrays), shared to Vue via Inertia shared props

---

## Directory Layout

```
taxCalc/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CalculatorController.php   # Main calculator page + /api/calculate endpoint
│   │   │   ├── ScaleController.php        # Admin CRUD for tax scales, brackets, deductions
│   │   │   ├── StatsController.php        # Admin statistics dashboard
│   │   │   └── AdminAuthController.php    # Admin login/logout
│   │   └── Middleware/
│   │       ├── HandleInertiaRequests.php  # Shares locale, translations (ui + countries) via Inertia
│   │       ├── SetLocale.php              # Reads ?lang= param or session to set app locale
│   │       └── AdminAuth.php             # Guards admin routes
│   ├── Models/
│   │   ├── TaxScale.php        # Tax scale (country, currency, salaries_per_year, is_active)
│   │   ├── TaxBracket.php      # Bracket (min_amount, max_amount, rate) → belongs to TaxScale
│   │   ├── TaxBracketOverride.php # Rate override by age/children range → belongs to TaxBracket
│   │   ├── Deduction.php       # Social security deduction (name, rate) → belongs to TaxScale
│   │   ├── TaxExemption.php    # Tax exemption (name, description, rate) → belongs to TaxScale
│   │   └── Calculation.php     # Saved anonymous calculation result (country, currency, gross, net, tax…)
│   ├── Services/
│   │   ├── SalaryCalculator.php   # Core calculation logic: gross→net and net→gross
│   │   └── PercentileService.php  # World & country income percentile computation
│   └── Support/
│       └── CurrencyHelper.php     # Static COUNTRY_CURRENCIES map + forCountry(code) helper
├── database/migrations/
│   ├── 2025_01_01_000003_create_tax_scales_table.php
│   ├── 2025_01_01_000004_create_tax_brackets_table.php
│   ├── 2025_01_01_000005_create_deductions_table.php
│   ├── 2025_01_02_000001_add_country_and_overrides_to_tax_scales.php
│   ├── 2026_02_15_160000_add_salaries_per_year_to_tax_scales.php
│   ├── 2026_02_21_000001_create_tax_exemptions_table.php
│   └── 2026_02_22_000002_add_currency_to_tax_scales_and_calculations.php
├── routes/web.php                   # All routes (calculator, admin CRUD, locale switch)
├── lang/
│   ├── en/
│   │   ├── ui.php        # UI string translations (English)
│   │   └── countries.php # Country code → country name map (English)
│   └── el/
│       ├── ui.php        # UI string translations (Greek)
│       └── countries.php # Country code → country name map (Greek)
└── resources/js/
    ├── Pages/
    │   ├── Calculator.vue         # Main public calculator page
    │   └── Admin/
    │       ├── Login.vue          # Admin login form
    │       ├── Stats.vue          # Admin stats dashboard
    │       └── Scales/
    │           ├── Index.vue      # List + create tax scales
    │           └── Edit.vue       # Edit scale details, brackets, overrides, deductions
    ├── composables/
    │   └── useI18n.js             # Reads locale/translations from Inertia shared props; t(), switchLocale()
    ├── utils/
    │   └── currencies.js          # COUNTRY_CURRENCIES map, currencyForCountry(), getCurrencySymbol(), formatCurrencyValue()
    └── Components/
        └── CookieBanner.vue       # Cookie consent banner
```

---

## Key Data Flow

### Calculator page load
1. `GET /` → `CalculatorController::index`
2. Loads active `TaxScale` (with brackets, deductions, exemptions) for the selected country/state
3. Passes `activeScale`, `selectedCountry`, `availableCountries`, `availableStates` to `Calculator.vue`

### Calculation request
1. `POST /api/calculate` → `CalculatorController::calculate`
2. Validates input, calls `SalaryCalculator::calculate` or `calculateFromNet`
3. Saves anonymous `Calculation` record (includes `currency` derived from country via `CurrencyHelper`)
4. Calls `PercentileService::compute` for world/country percentile
5. Computes live stats (percentile among calculator users)
6. Returns JSON with `gross`, `net`, `tax`, `total_deductions`, `currency`, percentile data, live stats

### Currency handling
- **PHP side**: `CurrencyHelper::COUNTRY_CURRENCIES` maps country codes → ISO 4217 codes
- **JS side**: `resources/js/utils/currencies.js` exports the same map + formatting helpers
- `tax_scales.currency` and `calculations.currency` are stored in DB (auto-derived from country on save)
- `Calculator.vue`: `currentCurrency` computed from `activeScale.currency ?? COUNTRY_CURRENCIES[selectedCountry]`
- All monetary amounts formatted via `formatCurrencyValue(n, currency, locale)` using `Intl.NumberFormat`

### i18n
- `HandleInertiaRequests` shares `locale`, `supportedLocales`, `translations.ui`, `translations.countries`
- `useI18n` composable exposes `t(key, replacements)`, `locale`, `countries`, `switchLocale(lang)`
- Locale switch: sets `?lang=` query param + session via `SetLocale` middleware

---

## Database Schema (key tables)

### `tax_scales`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | varchar | e.g. "2026 Tax Scale" |
| country_code | char(2) | ISO 3166-1 alpha-2 |
| currency | char(3) | ISO 4217, default EUR |
| state | varchar(100) null | for sub-national scales |
| salaries_per_year | tinyint | 12 or 14 |
| is_active | boolean | one active per country+state |

### `tax_brackets` → belongs to `tax_scales`
| Column | Type |
|--------|------|
| min_amount | decimal(12,2) |
| max_amount | decimal(12,2) null |
| rate | decimal(5,4) |

### `tax_bracket_overrides` → belongs to `tax_brackets`
Overrides rate for specific age/children ranges.

### `deductions` → belongs to `tax_scales`
Social security / insurance deductions (rate-based).

### `tax_exemptions` → belongs to `tax_scales`
Optional exemptions the user can toggle (e.g. disability).

### `calculations`
Anonymous calculation records for live stats / percentile.
| Column | Type |
|--------|------|
| country_code | char(2) |
| currency | char(3) |
| state | varchar null |
| mode | varchar | gross_to_net or net_to_gross |
| gross / net / tax / total_deductions | decimal(12,2) |
| age / children | integer |
| tax_exemption_rate | decimal(5,4) |

---

## Country → Currency Map

| Countries | Currency |
|-----------|----------|
| GR DE FR IT ES PT CY NL BE AT IE FI HR | EUR |
| US | USD |
| GB | GBP |
| SE | SEK |
| DK | DKK |
| NO | NOK |
| CH | CHF |
| PL | PLN |
| CZ | CZK |
| RO | RON |
| BG | BGN |
| HU | HUF |

---

## Admin Routes (protected by `admin.auth` middleware)
| Route | Handler |
|-------|---------|
| GET /admin | StatsController::index |
| GET /admin/scales | ScaleController::index |
| POST /admin/scales | ScaleController::store |
| GET /admin/scales/{id} | ScaleController::show (Edit page) |
| PUT /admin/scales/{id} | ScaleController::update |
| DELETE /admin/scales/{id} | ScaleController::destroy |
| POST /admin/scales/{id}/activate | ScaleController::activate |
| POST/PUT/DELETE /admin/scales/{id}/brackets/* | Bracket CRUD (in ScaleController) |
| POST/PUT/DELETE /admin/scales/{id}/deductions/* | Deduction CRUD |

---

## Things to Know

- **No auth for calculator**: public, no session required
- **Locale**: driven by `?lang=en|el` query param; stored in session; falls back to `en`
- **TaxScale activation**: only one active scale per (country_code, state) pair
- **Net→Gross**: solved via binary search in `SalaryCalculator::calculateFromNet`
- **Percentile data**: `PercentileService` uses hardcoded World Bank distribution data
- **Live stats**: computed fresh on each `/api/calculate` call (before saving current record)
- **Currency** is always auto-derived from `country_code` on the PHP side; never manually set by admin
