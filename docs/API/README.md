# 🔌 GearFalcon API Documentation

This folder contains all API-related documentation and resources for integrating with the GearFalcon backend.

## 📋 What's Included

| File | Purpose | Description |
|------|---------|-------------|
| **[README.md](README.md)** | API integration guide | This file - complete API documentation |
| **[GearFalcon_API.postman_collection.json](GearFalcon_API.postman_collection.json)** | Postman collection | Pre-configured API requests for testing |

## 🚀 API Overview

### Base URL
```
Development: http://localhost:8080/api
Production:  https://your-domain.com/api
```

### Authentication
The API uses **JWT (JSON Web Token)** authentication:

1. **Login** to get access and refresh tokens
2. **Include token** in Authorization header: `Bearer {token}`
3. **Refresh tokens** automatically when expired

### Response Format
All API responses follow this structure:
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation completed successfully"
}
```

Error responses:
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

## 🔐 Authentication Endpoints

### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": { ... },
    "access_token": "jwt_token_here",
    "refresh_token": "refresh_token_here"
  }
}
```

### Register
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Refresh Token
```http
POST /api/auth/refresh
Authorization: Bearer {refresh_token}
```

## 👥 Role-Based Endpoints

### Admin Endpoints
- **User Management**: `/api/admin/users`
- **Service Management**: `/api/admin/services`
- **System Configuration**: `/api/admin/settings`

### Customer Endpoints
- **Profile Management**: `/api/customer/profile`
- **Quote Requests**: `/api/customer/quotes`
- **Service History**: `/api/customer/services`

### Technician Endpoints
- **Job Assignments**: `/api/technician/jobs`
- **Schedule Management**: `/api/technician/schedule`
- **Skill Updates**: `/api/technician/skills`

## 📝 Common API Patterns

### List Resources
```http
GET /api/{resource}
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `search` - Search term
- `sort` - Sort field
- `order` - Sort direction (asc/desc)

### Get Single Resource
```http
GET /api/{resource}/{id}
```

### Create Resource
```http
POST /api/{resource}
Content-Type: application/json

{
  // resource data
}
```

### Update Resource
```http
PUT /api/{resource}/{id}
Content-Type: application/json

{
  // updated data
}
```

### Delete Resource
```http
DELETE /api/{resource}/{id}
```

## 🛠️ Using Postman Collection

### Import Collection
1. Open Postman
2. Click **Import** button
3. Select **Upload Files**
4. Choose `GearFalcon_API.postman_collection.json`
5. Collection will appear in your workspace

### Setup Environment
1. Create new Environment in Postman
2. Add variables:
   - `base_url` = `http://localhost:8080/api`
   - `access_token` = (leave empty, will be set after login)

### Authentication Flow
1. **Login Request**: Use the Login endpoint to get tokens
2. **Set Token**: Copy access_token from response
3. **Update Environment**: Set `access_token` variable
4. **Test Protected Endpoints**: All other requests will use the token

## 📊 Status Codes

| Code | Description |
|------|-------------|
| `200` | Success |
| `201` | Created |
| `400` | Bad Request (validation errors) |
| `401` | Unauthorized (invalid/missing token) |
| `403` | Forbidden (insufficient permissions) |
| `404` | Not Found |
| `422` | Unprocessable Entity (validation failed) |
| `500` | Internal Server Error |

## 🔒 Security Headers

All API responses include security headers:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security: max-age=31536000`

## 🚦 Rate Limiting

API endpoints are rate-limited to prevent abuse:
- **Authentication endpoints**: 5 requests per minute
- **General endpoints**: 60 requests per minute
- **Admin endpoints**: 30 requests per minute

Rate limit headers:
- `X-RateLimit-Limit` - Maximum requests per window
- `X-RateLimit-Remaining` - Remaining requests
- `X-RateLimit-Reset` - Time until reset (Unix timestamp)

## 📧 Error Handling

### Validation Errors
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Authentication Errors
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

### Server Errors
```json
{
  "success": false,
  "message": "Internal server error",
  "error_id": "unique_error_identifier"
}
```

## 🔄 Pagination

List endpoints return paginated results:
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10,
    "from": 1,
    "to": 15
  }
}
```

## 📝 Content Types

### Request Content Types
- `application/json` - For JSON data
- `multipart/form-data` - For file uploads
- `application/x-www-form-urlencoded` - For form data

### Response Content Types
- `application/json` - Standard API responses
- `text/html` - For some legacy endpoints
- `application/pdf` - For generated documents

## 🧪 Testing API Endpoints

### Using cURL
```bash
# Login
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Get user profile (with token)
curl -X GET http://localhost:8080/api/customer/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Using JavaScript (Fetch)
```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('http://localhost:8080/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  return response.json();
};

// Use token for authenticated requests
const getProfile = async (token) => {
  const response = await fetch('http://localhost:8080/api/customer/profile', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return response.json();
};
```

## 📞 Support

For API-related questions:
1. Check this documentation first
2. Review the Postman collection examples
3. Test endpoints using the provided cURL examples
4. Check the main [project documentation](../README.md) for setup issues

---

**Need help with a specific endpoint?** Check the Postman collection for detailed examples and test cases!