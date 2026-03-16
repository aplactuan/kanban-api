# Kanban API – Implementation Plan

A step-by-step plan for building the Kanban clone backend API. The project uses Laravel 12 with Sanctum for authentication.

---

## Phase 1: Authentication (Register, Login, Logout)

| Step | Action | Details |
|------|--------|---------|
| 1.1 | Auth API routes | Add `POST /api/register`, `POST /api/login`, `POST /api/logout` (unversioned) |
| 1.2 | Auth controller | Create `AuthController` with `register()`, `login()`, `logout()` methods |
| 1.3 | Validation | Validate registration (name, email, password) and login (email, password) |
| 1.4 | Sanctum tokens | On login/register: create token and return it; on logout: revoke current token |
| 1.5 | API response format | Return JSON with token and user data on login/register |

**Notes:** Sanctum is already installed. Use `auth:sanctum` middleware for protected routes.

---

## Phase 2: Project Boards

| Step | Action | Details |
|------|--------|---------|
| 2.1 | Board model | Create `Board` model with `user_id`, `name`, `description` (optional) |
| 2.2 | Migration | Create `boards` table (`id`, `user_id`, `name`, `description`, `timestamps`) |
| 2.3 | Relationships | `User hasMany Board`, `Board belongsTo User` |
| 2.4 | Board controller | Create `BoardController` with CRUD actions |
| 2.5 | Board API routes | Add versioned routes under `v1` |
| 2.6 | Authorization | Ensure users can only access their own boards (policies or checks) |

---

## Phase 3: Columns

| Step | Action | Details |
|------|--------|---------|
| 3.1 | Column model | Create `Column` model with `board_id`, `name`, `position` (for ordering) |
| 3.2 | Migration | Create `columns` table (`id`, `board_id`, `name`, `position`, `timestamps`) |
| 3.3 | Relationships | `Board hasMany Column`, `Column belongsTo Board` |
| 3.4 | Column controller | Create invokable single Controller for Column CRUD operation (scoped to a board) |
| 3.5 | Column API routes | Add versioned routes under `v1` |
| 3.6 | Reordering | Optional: endpoint to update `position` for drag-and-drop |

---

## Phase 4: Tasks

| Step | Action | Details |
|------|--------|---------|
| 4.1 | Task model | Create `Task` model with `column_id`, `title`, `description` (optional), `position` |
| 4.2 | Migration | Create `tasks` table (`id`, `column_id`, `title`, `description`, `position`, `timestamps`) |
| 4.3 | Relationships | `Column hasMany Task`, `Task belongsTo Column` |
| 4.4 | Task controller | Create Single responsibilty for task CRUD (scoped to a column) using invokable controllers |
| 4.5 | Task API routes | Add versioned routes under `v1` |
| 4.6 | Move task | Optional: `PATCH /api/v1/tasks/{task}/move` to move a task between columns |

---

## Phase 5: Polish & API Design

| Step | Action | Details |
|------|--------|---------|
| 5.1 | Nested responses | When returning boards, optionally include columns and tasks |
| 5.2 | API resources | Use Laravel API Resources for consistent response formatting |
| 5.3 | Error handling | Consistent error format (e.g. 404, 422, 401) with messages |
| 5.4 | Form requests | Use Form Request classes for validation where useful |

---

## Data Model

```
User (existing)
  └── hasMany → Board
                  └── hasMany → Column
                                  └── hasMany → Task
```

---

## Route Structure

### Unversioned (auth only)
```
POST   /api/register
POST   /api/login
POST   /api/logout
```

### Versioned (v1)
```
GET    /api/v1/user

GET    /api/v1/boards
POST   /api/v1/boards
GET    /api/v1/boards/{board}
PUT    /api/v1/boards/{board}
DELETE /api/v1/boards/{board}

GET    /api/v1/boards/{board}/columns
POST   /api/v1/boards/{board}/columns
PUT    /api/v1/boards/{board}/columns/{column}
DELETE /api/v1/boards/{board}/columns/{column}

GET    /api/v1/boards/{board}/columns/{column}/tasks
POST   /api/v1/boards/{board}/columns/{column}/tasks
PUT    /api/v1/boards/{board}/columns/{column}/tasks/{task}
DELETE /api/v1/boards/{board}/columns/{column}/tasks/{task}
```

---

## Execution Order

1. **Phase 1** – Authentication
2. **Phase 2** – Boards
3. **Phase 3** – Columns
4. **Phase 4** – Tasks
5. **Phase 5** – Polish and API design

Each phase builds on the previous one.
