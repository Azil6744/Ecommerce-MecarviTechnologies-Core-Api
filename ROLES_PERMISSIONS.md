# Roles and Permissions

This document outlines the role-based access control (RBAC) system implemented in the CMS Backend API.

## Role Definitions

### 👑 Super Admin
**Full power - Can do everything**

**Permissions:**
- ✅ Manage users (view, create, update, delete)
- ✅ Assign roles to users
- ✅ Edit all content (home page, pages, services, products, FAQs, jobs, technologies)
- ✅ View leads
- ✅ Upload media/images
- ✅ Access all endpoints
- ✅ Delete home page content

---

### ✍️ Editor
**Content manager - Can edit content but cannot manage users**

**Permissions:**
- ✅ Edit pages
- ✅ Add/edit services/products
- ✅ Edit FAQs, jobs, technologies
- ✅ Upload images/media
- ✅ Manage home page content (create, update, delete)
- ✅ View own profile

**Restrictions:**
- ❌ Cannot create/delete users
- ❌ Cannot view/manage users list
- ❌ Cannot change user roles
- ❌ Cannot access user management endpoints

---

### 👀 Viewer
**Read-only access**

**Permissions:**
- ✅ View content
- ✅ View leads (future feature)
- ✅ View dashboard (future feature)
- ✅ View home page content
- ✅ View own profile

**Restrictions:**
- ❌ Cannot edit anything
- ❌ Cannot upload media
- ❌ Cannot manage users
- ❌ Cannot access admin endpoints

---

## API Endpoint Permissions

### Authentication Endpoints
| Endpoint | Super Admin | Editor | Viewer | Public |
|----------|-------------|--------|--------|--------|
| POST /register | ✅ | ✅ | ✅ | ✅ |
| POST /login | ✅ | ✅ | ✅ | ✅ |
| GET /user | ✅ | ✅ | ✅ | ❌ |
| POST /logout | ✅ | ✅ | ✅ | ❌ |

### User Management Endpoints
| Endpoint | Super Admin | Editor | Viewer |
|----------|-------------|--------|--------|
| GET /users | ✅ | ❌ | ❌ |
| GET /users/{id} | ✅ | ❌ | ❌ |
| POST /users | ✅ | ❌ | ❌ |
| PUT/PATCH /users/{id} | ✅ | ❌ | ❌ |
| DELETE /users/{id} | ✅ | ❌ | ❌ |

### Home Page Management Endpoints
| Endpoint | Super Admin | Editor | Viewer | Public |
|----------|-------------|--------|--------|--------|
| GET /home-page | ✅ | ✅ | ✅ | ✅ |
| GET /home-page/{id} | ✅ | ✅ | ✅ | ✅ |
| POST /home-page | ✅ | ✅ | ❌ | ❌ |
| PUT/PATCH/POST /home-page/{id} | ✅ | ✅ | ❌ | ❌ |
| DELETE /home-page/{id} | ✅ | ✅ | ❌ | ❌ |

---

## Implementation Details

### User Model Methods

```php
$user->isSuperAdmin()      // Returns true if role is 'super_admin'
$user->isEditor()          // Returns true if role is 'editor'
$user->isViewer()          // Returns true if role is 'viewer'
$user->hasAdminAccess()    // Returns true if role is 'super_admin' or 'editor'
```

### Checking Permissions in Controllers

```php
// Super Admin only
if (!$user->isSuperAdmin()) {
    return response()->json(['message' => 'Unauthorized'], 403);
}

// Admin access (Super Admin + Editor)
if (!$user->hasAdminAccess()) {
    return response()->json(['message' => 'Unauthorized'], 403);
}
```

---

## Role Assignment

- **Default Role**: New registrations automatically get `viewer` role
- **Role Assignment**: Only Super Admin can assign/change roles when creating/updating users
- **Roles**: `super_admin`, `editor`, `viewer`

---

## Security Notes

1. All protected endpoints require a valid Sanctum token
2. Role checks are performed at the controller level
3. Passwords are never exposed in API responses
4. Users cannot modify their own role (only Super Admin can)
5. Super Admin cannot delete themselves (protected in code)

