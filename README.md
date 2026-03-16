# Kanban API

<p>
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="MIT">
</p>

A **RESTful API** for a Kanban-style task board: boards, columns, and tasks with token-based auth, versioned endpoints, and optional nested responses. Built with **Laravel 12** and **Sanctum**.

---

## Highlights

- **RESTful & versioned** — `POST /api/register`, `GET /api/v1/boards`, nested routes for columns and tasks
- **Token auth** — Laravel Sanctum; register, login, logout with API tokens
- **Nested responses** — `?include=columns` or `?include=columns.tasks` for one-shot board + columns + tasks
- **Consistent API design** — Laravel API Resources, Form Request validation, unified JSON error format (401, 404, 422)
- **Clean architecture** — Repository pattern, single-action (invokable) controllers, dependency injection
- **Tested** — Feature tests for auth and CRUD, unit tests for repositories

---

## Tech Stack

| Layer | Choice |
|-------|--------|
| Framework | Laravel 12 |
| Auth | Laravel Sanctum (API tokens) |
| PHP | 8.2+ |
| Database | MySQL / MariaDB (configurable) |

---

## API at a Glance

### Auth (unversioned)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/register` | Register; returns user + token |
| `POST` | `/api/login` | Login; returns user + token |
| `POST` | `/api/logout` | Revoke current token *(requires auth)* |

### Versioned API (v1) — all require `Authorization: Bearer {token}`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/user` | Current user |
| `GET` | `/api/v1/boards` | List boards *(supports `?include=columns,columns.tasks`)* |
| `POST` | `/api/v1/boards` | Create board |
| `GET` | `/api/v1/boards/{id}` | Get board *(supports `?include=columns,columns.tasks`)* |
| `PUT` | `/api/v1/boards/{id}` | Update board |
| `DELETE` | `/api/v1/boards/{id}` | Delete board |
| `GET` | `/api/v1/boards/{board}/columns` | List columns *(supports `?include=tasks`)* |
| `POST` | `/api/v1/boards/{board}/columns` | Create column |
| `PUT` | `/api/v1/boards/{board}/columns/{column}` | Update column |
| `DELETE` | `/api/v1/boards/{board}/columns/{column}` | Delete column |
| `GET` | `/api/v1/boards/{board}/columns/{column}/tasks` | List tasks |
| `POST` | `/api/v1/boards/{board}/columns/{column}/tasks` | Create task |
| `PUT` | `/api/v1/boards/{board}/columns/{column}/tasks/{task}` | Update task |
| `DELETE` | `/api/v1/boards/{board}/columns/{column}/tasks/{task}` | Delete task |
| `PATCH` | `/api/v1/tasks/{task}/move` | Move task to another column |

### Example: full board in one request

```http
GET /api/v1/boards/1?include=columns.tasks
Authorization: Bearer {your-token}
```

Response includes the board with nested `columns`, each with nested `tasks`.

---

## Getting Started

### Requirements

- PHP 8.2+
- Composer
- MySQL / MariaDB (or SQLite for local dev)

### Install & run

```bash
git clone <repo-url> kanban-api && cd kanban-api
composer install
cp .env.example .env
php artisan key:generate
```

Configure `.env` (e.g. `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), then:

```bash
php artisan migrate
php artisan serve
```

API base: `http://localhost:8000/api`

### Quick smoke test

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Demo","email":"demo@example.com","password":"password","password_confirmation":"password"}'

# Use the returned token for authenticated requests
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/v1/boards
```

---

## Testing

```bash
composer test
# or
php artisan test
```

- **Feature:** Auth (register, login, logout), Board CRUD, Column CRUD, Task CRUD
- **Unit:** Repository layer (Board, Column, Task)

---

## Project Structure (high level)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── Board/          # Index, Store, Show, Update, Destroy
│   │   ├── Column/         # Index, Store, Update, Destroy (scoped to board)
│   │   ├── Task/           # Index, Store, Update, Destroy, Move (scoped to column)
│   │   └── Concerns/
│   │       └── ParsesIncludes.php   # ?include= parsing for nested responses
│   ├── Requests/           # Form Requests per action (validation)
│   └── Resources/          # BoardResource, ColumnResource, TaskResource
├── Models/                 # User, Board, Column, Task
└── Repositories/
    ├── Contracts/          # BoardRepositoryInterface, etc.
    └── Eloquent/          # Implementations
```

- **Single-responsibility controllers** — one invokable class per action; routes map directly to controller classes.
- **Repository pattern** — controllers depend on repository interfaces; persistence is swappable and testable.
- **API Resources** — consistent JSON shape; nested data via `whenLoaded()` and optional `?include=` query.

---

## Error responses

All API errors return JSON with a `message` (and `errors` for validation):

| Status | When |
|--------|------|
| `401` | Missing or invalid token → `{"message": "Unauthenticated."}` |
| `404` | Board/column/task not found or not owned → `{"message": "Resource not found."}` |
| `422` | Validation failed → `{"message": "...", "errors": { "field": ["..."] }}` |

---

## License

This project is open-sourced under the [MIT License](https://opensource.org/licenses/MIT).
