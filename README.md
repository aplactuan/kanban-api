# Kanban API

<p>
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="MIT">
</p>

A **RESTful API** for a multi-user Kanban-style task board: boards, columns, and tasks with **role-based access control (RBAC)**, board membership, token-based auth, versioned endpoints, and optional nested responses. Built with **Laravel 12** and **Sanctum**.

---

## Highlights

- **RESTful & versioned** — `POST /api/register`, `GET /api/v1/boards`, nested routes for columns and tasks
- **Multi-user boards** — `board_members` pivot with roles (`owner`, `admin`, `member`); invite by email; leave; ownership transfer
- **RBAC** — Laravel policies enforce who can view boards, manage structure, manage members, and delete boards
- **Token auth** — Laravel Sanctum; register, login, logout with API tokens
- **Nested responses** — `?include=columns` or `?include=columns.tasks` for one-shot board + columns + tasks
- **Consistent API design** — Laravel API Resources, Form Request validation, unified JSON error format (401, 403, 404, 422, 429)
- **Clean architecture** — Repository pattern, single-action (invokable) controllers, dependency injection
- **Tested** — Feature tests for auth, CRUD, membership, RBAC, and throttles; unit tests for repositories

---

## Roles (boards)

| Role | Capabilities (summary) |
|------|-------------------------|
| **owner** | Full control; only role that can delete the board or transfer ownership; cannot leave without transferring first |
| **admin** | Update board metadata; manage columns/tasks; invite/remove **members**; cannot remove another admin or assign the admin role (owner only) |
| **member** | View board; create/update/delete/move own-scope tasks; cannot manage columns, board settings, or membership |

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
| `GET` | `/api/v1/boards` | List boards you own or are a member of *(supports `?include=columns,columns.tasks`)* |
| `POST` | `/api/v1/boards` | Create board |
| `GET` | `/api/v1/boards/{board}` | Get board *(supports `?include=columns,columns.tasks`)* |
| `PUT` | `/api/v1/boards/{board}` | Update board *(admin+)* |
| `DELETE` | `/api/v1/boards/{board}` | Delete board *(owner only)* |
| `GET` | `/api/v1/boards/{board}/members` | List members and roles |
| `POST` | `/api/v1/boards/{board}/members` | Invite user by email (`role`: `admin` or `member`, default `member`) *(admin+; throttled)* |
| `PUT` | `/api/v1/boards/{board}/members/{member}` | Change a member’s role (`admin` or `member`) *(admin+; owner rules apply)* |
| `DELETE` | `/api/v1/boards/{board}/members/{member}` | Remove a member *(admin+; throttled)* |
| `DELETE` | `/api/v1/boards/{board}/members/leave` | Leave the board *(member/admin; owner must transfer first)* |
| `PATCH` | `/api/v1/boards/{board}/members/{member}/transfer-ownership` | Transfer ownership to an existing member *(owner only)* |
| `GET` | `/api/v1/boards/{board}/columns` | List columns *(supports `?include=tasks`)* |
| `POST` | `/api/v1/boards/{board}/columns` | Create column *(admin+)* |
| `PUT` | `/api/v1/boards/{board}/columns/{column}` | Update column *(admin+)* |
| `DELETE` | `/api/v1/boards/{board}/columns/{column}` | Delete column *(admin+)* |
| `GET` | `/api/v1/boards/{board}/columns/{column}/tasks` | List tasks |
| `POST` | `/api/v1/boards/{board}/columns/{column}/tasks` | Create task |
| `PUT` | `/api/v1/boards/{board}/columns/{column}/tasks/{task}` | Update task |
| `DELETE` | `/api/v1/boards/{board}/columns/{column}/tasks/{task}` | Delete task |
| `PATCH` | `/api/v1/tasks/{task}/move` | Move task to another column on the same board |

`{board}`, `{column}`, `{task}`, and `{member}` (user id scoped to board membership) use route model binding.

### Rate limits (per authenticated user)

- **Invite** (`POST .../members`) — 10 requests per minute  
- **Remove member** (`DELETE .../members/{member}`) — 10 requests per minute  

Exceeded limits respond with **429 Too Many Requests**.

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

- **Feature:** Auth, board/column/task CRUD, RBAC, board members (invite, roles, leave, transfer), throttles  
- **Unit:** Repositories (board, column, task), board role helpers  

---

## Project Structure (high level)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── Board/          # Index, Store, Show, Update, Destroy
│   │   │   └── Member/     # Index, Invite, Update, Remove, Leave, TransferOwnership
│   │   ├── Column/         # Index, Store, Update, Destroy (scoped to board)
│   │   ├── Task/           # Index, Store, Update, Destroy, Move
│   │   └── Concerns/
│   │       └── ParsesIncludes.php   # ?include= parsing for nested responses
│   ├── Requests/           # Form Requests per action (validation)
│   └── Resources/          # BoardResource, ColumnResource, TaskResource, MemberResource
├── Models/                 # User, Board, BoardMember, Column, Task
├── Policies/               # BoardPolicy, ColumnPolicy, TaskPolicy
└── Repositories/
    ├── Contracts/
    └── Eloquent/
```

- **Single-responsibility controllers** — one invokable class per action; routes map directly to controller classes.
- **Repository pattern** — controllers depend on repository interfaces; persistence is swappable and testable.
- **Policies** — authorization is explicit (`authorize()` in controllers), not hidden in query scopes alone.
- **API Resources** — consistent JSON shape; nested data via `whenLoaded()` and optional `?include=` query.

---

## Error responses

All API errors return JSON with a `message` (and `errors` for validation):

| Status | When |
|--------|------|
| `401` | Missing or invalid token → `{"message": "Unauthenticated."}` |
| `403` | Authenticated but not allowed (policy) → `{"message": "..."}` |
| `404` | Model not found for route binding → `{"message": "Resource not found."}` |
| `422` | Validation or business rule (e.g. unknown invite email, invalid role change) → `{"message": "...", "errors": { ... }}` |
| `429` | Too many requests (member invite/remove throttles) |

---

## License

This project is open-sourced under the [MIT License](https://opensource.org/licenses/MIT).
