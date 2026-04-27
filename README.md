# 🌱 VESTA — REST API (Backend)

[![My skills](https://skillicons.dev/icons?i=git,php,symfony,mysql)](https://skillicons.dev)

Symfony-based REST API powering the VESTA carbon tracker.

---

## Tech Stack

- **Symfony 7** — PHP framework
- **Doctrine ORM** — database & migrations
- **LexikJWTAuthenticationBundle** — JWT authentication
- **NelmioCorsBundle** — CORS handling
- **API Platform** — (installed, partially used)
- **MySQL** — database

---

## Features

- User registration & login with JWT
- Activity logging (journey, food, shopping)
- Automatic CO₂ calculation via `CarbonCalculatorService`
- Per-user stats: total, by category, by week
- Global averages across all users
- Profile update (name, locale, profile picture)

---

## Getting Started

```bash
composer install
```

Configure your `.env`:

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/vesta"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase
```

Then:

```bash
php bin/console doctrine:migrations:migrate
symfony server:start
```

---

## API Endpoints

All protected routes require `Authorization: Bearer <token>`.

| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| `POST` | `/register` | ✗ | Create account, returns JWT |
| `POST` | `/login` | ✗ | Sign in, returns JWT |
| `GET` | `/me` | ✓ | Get current user info |
| `PATCH` | `/me` | ✓ | Update name / locale / picture |
| `GET` | `/activities` | ✓ | List user activities |
| `POST` | `/activities` | ✓ | Log a new activity |
| `DELETE` | `/activities/{id}` | ✓ | Delete an activity |
| `GET` | `/activities/stats` | ✓ | CO₂ stats (total, by category, by week) |

More detailed API documentation : https://api.sae401.mmi24c16.mmi-troyes.fr/

---

## CO2 Calculation

Handled by `CarbonCalculatorService`. Coefficients used:

**Journey** — vehicle (kg/km) + energy (kg/km) × distance  
**Food** — kg CO₂ per meal × count  
**Shopping** — kg CO₂ per € × amount

---

## Project Structure

```
src/
├── Controller/   # Route handlers (Activity, Auth, Me, Stats)
├── Entity/       # Doctrine entities (User, Activity)
├── Repository/   # Custom queries & stats aggregations
├── Security/     # JWT login success handler
└── Service/      # CarbonCalculatorService
```