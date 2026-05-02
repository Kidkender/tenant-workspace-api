# Tenant Workspace API

A multi-tenant workspace management REST API built with Laravel 13. Supports workspaces (tenants), role-based access control, task management with comments, member invitations, activity logging, and real-time notifications.

## Tech Stack

| Layer | Technology |
|---|---|
| Runtime | PHP 8.3+ |
| Framework | Laravel 13 |
| Auth | Laravel Sanctum (token-based) |
| Database | MySQL 8+ |
| Queue | Database driver (notifications, email) |
| Email | SMTP (Mailtrap-compatible) |

## Requirements

- PHP 8.3+
- Composer 2.x
- MySQL 8.0+

## Local Setup

```bash
# 1. Clone and install dependencies
git clone <repo-url> tenant-workspace-api
cd tenant-workspace-api
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Edit .env — set DB credentials, mail, and frontend URL
DB_DATABASE=tenant_workspace
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_user
MAIL_PASSWORD=your_mailtrap_pass

FRONTEND_URL=http://localhost:5173

# 4. Run migrations
php artisan migrate --seed

# 5. Start the server
php artisan serve
```

The API will be available at `http://localhost:8000/api`.

## Architecture

The project uses a feature-module structure under `app/Modules/`:

```
app/
├── Constants/
│   ├── ErrorCode.php          # All error code strings
│   └── Permission.php         # Permission constants
├── Http/
│   └── Middleware/
│       ├── ResolveTenant.php  # Reads X-Tenant-ID header, sets tenant context
│       └── CheckPermission.php
├── Modules/
│   ├── Access/
│   │   └── Models/Role.php
│   ├── Activity/
│   │   └── ActivityLogController.php
│   ├── Auth/
│   │   ├── AuthController.php
│   │   └── AuthService.php
│   ├── Dashboard/
│   │   └── DashboardController.php
│   ├── Task/
│   │   ├── Http/Controllers/  # TaskController, TaskCommentController
│   │   ├── Models/            # Task, TaskComment
│   │   ├── Requests/          # CreateTaskRequest, UpdateTaskRequest
│   │   └── Services/TaskService.php
│   ├── Tenant/
│   │   ├── Models/            # Tenant, TenantUser, TenantInvitation
│   │   ├── Requests/          # InvitationRequest, UpdateTenantRequest
│   │   ├── TenantController.php
│   │   └── TenantService.php
│   └── User/
│       ├── Models/User.php
│       └── UserController.php
└── Notifications/
    ├── NotificationController.php
    ├── TaskAssigned.php
    └── TenantInvite.php
```

## Multi-Tenancy

Every request that touches tenant-scoped data must include the `X-Tenant-ID` header:

```
X-Tenant-ID: <tenant-uuid>
```

The `ResolveTenant` middleware resolves the tenant from this header and verifies the authenticated user is an active member. Routes without this middleware (auth, `/me`, `/tenants`) are global.

## Roles & Permissions

| Role | Description |
|---|---|
| `owner` | Created the workspace. Can change member roles, update workspace settings. |
| `admin` | Can remove members, manage tasks. |
| `member` | Basic access. Scoped by task permissions. |

Task-level permissions are enforced via the `CheckPermission` middleware using constants in `app/Constants/Permission.php`:

| Permission | Roles granted |
|---|---|
| `task.view` | all |
| `task.create` | admin, owner |
| `task.update` | admin, owner |
| `task.delete` | admin, owner |
| `comment.create` | all |
| `comment.delete` | admin, owner |

## API Reference

All endpoints are prefixed with `/api`.

Authentication uses Bearer tokens (Sanctum). Include the token in:
```
Authorization: Bearer <token>
```

### Auth

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/auth/register` | — | Register a new user |
| POST | `/auth/login` | — | Login, returns token |
| POST | `/email/verify/resend` | — | Resend email verification link |
| GET | `/email/verify/{id}/{hash}` | signed | Verify email address |

#### Register

```json
POST /api/auth/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

#### Login

```json
POST /api/auth/login
{
  "email": "john@example.com",
  "password": "secret123"
}
// Response: { "token": "...", "user": { ... } }
```

### User (requires auth)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/me` | Get authenticated user with tenant memberships |
| PUT | `/me` | Update profile name |

### Tenants (requires auth)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/tenants` | Create a new workspace |
| POST | `/tenants/accept/{token}` | Accept workspace invitation |

### Tenant Management (requires auth + `X-Tenant-ID`)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/tenant/members` | List workspace members with roles |
| DELETE | `/tenant/members/{userId}` | Remove a member (owner or admin) |
| PUT | `/tenant/members/{userId}/role` | Update member role (owner only) |
| PUT | `/tenants/settings` | Update workspace name (owner only) |
| POST | `/tenants/invite` | Invite a user by email |
| GET | `/roles` | List assignable roles (admin, member) |

#### Invite Member

```json
POST /api/tenants/invite
X-Tenant-ID: <uuid>
{
  "email": "colleague@example.com",
  "role_id": 2
}
```

#### Update Member Role

```json
PUT /api/tenant/members/{userId}/role
X-Tenant-ID: <uuid>
{
  "role_id": 2
}
```

### Tasks (requires auth + `X-Tenant-ID`)

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/tasks` | `task.view` | List tasks (filterable, paginated) |
| GET | `/tasks/{id}` | `task.view` | Get task detail |
| POST | `/tasks` | `task.create` | Create task |
| PUT | `/tasks/{id}` | `task.update` | Update task |
| DELETE | `/tasks/{id}` | `task.delete` | Delete task |
| POST | `/tasks/{id}/assign` | `task.update` | Assign task to member |

#### List Tasks — Query Parameters

| Param | Type | Description |
|---|---|---|
| `status` | string | Filter by `todo`, `doing`, `done` |
| `assigned_to` | uuid | Filter by assignee user ID |
| `search` | string | Search by title |
| `page` | int | Page number (default: 1) |
| `per_page` | int | Items per page (default: 15) |

#### Create Task

```json
POST /api/tasks
X-Tenant-ID: <uuid>
{
  "title": "Fix login bug",
  "description": "Optional details",
  "assigned_to": "<user-uuid>",
  "due_date": "2025-06-01"
}
```

### Comments (requires auth + `X-Tenant-ID`)

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/tasks/{taskId}/comments` | `task.view` | List comments for a task |
| POST | `/tasks/{taskId}/comments` | `comment.create` | Add a comment |
| PUT | `/tasks/{taskId}/comments/{commentId}` | — | Edit own comment |
| DELETE | `/tasks/{taskId}/comments/{commentId}` | `comment.delete` | Delete comment |

### Dashboard (requires auth + `X-Tenant-ID`)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/dashboard/stats` | Task counts by status, member count, recent activity |

### Activity Log (requires auth + `X-Tenant-ID`)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/activity-logs` | Paginated activity feed (filterable by action/type) |

### Notifications (requires auth + `X-Tenant-ID`)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/notifications` | List all notifications |
| GET | `/notifications/unread` | List unread notifications |
| POST | `/notifications/{id}/read` | Mark one notification as read |
| POST | `/notifications/read-all` | Mark all notifications as read |

## Error Response Format

All errors follow a consistent JSON envelope:

```json
{
  "error": "error.code.string"
}
```

### Error Codes

| Code | HTTP | Description |
|---|---|---|
| `auth.invalid_credentials` | 401 | Wrong email or password |
| `auth.email_not_verified` | 403 | Email not yet verified |
| `auth.email_taken` | 422 | Email already registered |
| `auth.unauthorized` | 401 | Not authenticated |
| `tenant.not_found` | 404 | Workspace not found |
| `tenant.not_provided` | 400 | Missing X-Tenant-ID header |
| `tenant.not_member` | 403 | Not a member of this workspace |
| `tenant.already_member` | 409 | User is already a member |
| `permission.denied` | 403 | Insufficient role for this action |
| `resource.not_found` | 404 | Requested resource not found |
| `validation.failed` | 422 | Request validation failed (includes `errors` field) |
| `internal_server_error` | 500 | Unexpected server error |

## Validation Error Example

```json
{
  "error": "validation.failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

## Notifications

Notifications are sent via email and stored in the `notifications` table for in-app display:

- **Task assigned** — sent to the assigned user when a task is assigned to them
- **Workspace invitation** — sent via email with a one-time token link (expires in 48 hours)

## Running Tests

```bash
php artisan test --compact
```

## Code Style

```bash
vendor/bin/pint
```
