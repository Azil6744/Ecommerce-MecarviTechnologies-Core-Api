# API Documentation

This document provides comprehensive documentation for all API endpoints in the CMS Backend.

**Base URL**: `http://localhost:8000/api/v1`

**Authentication**: Most endpoints require a Bearer token in the Authorization header. Public endpoints do not require authentication.

---

## API Routes Quick Reference

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| **Footer** |
| GET | `/api/v1/footer` | Public | Get full footer content |
| POST | `/api/v1/footer` | Admin | Save footer |
| PUT | `/api/v1/footer` | Admin | Save footer |
| PUT | `/api/v1/footer/{id}` | Admin | Update footer by ID |
| PATCH | `/api/v1/footer/{id}` | Admin | Update footer by ID |
| DELETE | `/api/v1/footer/{id}` | Admin | Delete footer |
| DELETE | `/api/v1/footer/{id}/field/{field}` | Admin | Delete single footer field |
| DELETE | `/api/v1/footer/links/{id}` | Admin | Delete single footer link |
| DELETE | `/api/v1/footer/payment-methods/{id}` | Admin | Delete single payment method |
| **Site Settings** |
| GET | `/api/v1/site-settings` | Public | Get site settings (SEO title, logo, favicon, button, header links) |
| POST | `/api/v1/site-settings` | Admin | Save site settings |
| PUT | `/api/v1/site-settings` | Admin | Save site settings |
| PUT | `/api/v1/site-settings/{id}` | Admin | Update site settings by ID |
| PATCH | `/api/v1/site-settings/{id}` | Admin | Update site settings by ID |
| DELETE | `/api/v1/site-settings/{id}` | Admin | Delete site settings |
| DELETE | `/api/v1/site-settings/{id}/field/{field}` | Admin | Delete single field (e.g. logo, favicon, button_name) |
| DELETE | `/api/v1/site-settings/links/{id}` | Admin | Delete single header link |

**Examples (base URL: `http://localhost:8000`):**
- Get footer: `GET http://localhost:8000/api/v1/footer`
- Get site settings: `GET http://localhost:8000/api/v1/site-settings`
- Save site settings: `POST http://localhost:8000/api/v1/site-settings` (with JSON body and `Authorization: Bearer {token}`)

---

## Table of Contents

1. [Authentication Endpoints](#authentication-endpoints)
2. [User Management Endpoints](#user-management-endpoints)
3. [Home Page Management Endpoints](#home-page-management-endpoints)
4. [Service Page Management Endpoints](#service-page-management-endpoints)
5. [Features Section Management Endpoints](#features-section-management-endpoints)
6. [Analytics Section Management Endpoints](#analytics-section-management-endpoints)
7. [Chart Section Management Endpoints](#chart-section-management-endpoints)
8. [Tab Section Management Endpoints](#tab-section-management-endpoints)
9. [Showcase Section Management Endpoints](#showcase-section-management-endpoints)
10. [About Section Management Endpoints](#about-section-management-endpoints)
11. [Service Section Management Endpoints](#service-section-management-endpoints)
12. [Service Card Management Endpoints](#service-card-management-endpoints)
13. [What We Create Section Management Endpoints](#what-we-create-section-management-endpoints)
14. [What We Create Tab Management Endpoints](#what-we-create-tab-management-endpoints)
15. [Category Tab Management Endpoints](#category-tab-management-endpoints)
16. [Why Choose Us Section Management Endpoints](#why-choose-us-section-management-endpoints)
17. [Why Choose Us Tab Management Endpoints](#why-choose-us-tab-management-endpoints)
18. [Our Facts Section Management Endpoints](#our-facts-section-management-endpoints)
19. [Our Fact Management Endpoints](#our-fact-management-endpoints)
20. [Our Promise Management Endpoints](#our-promise-management-endpoints)
21. [Process Step Management Endpoints](#process-step-management-endpoints)
22. [About Page Management Endpoints](#about-page-management-endpoints)
23. [Hero Section Management Endpoints](#hero-section-management-endpoints)
24. [About the Founder Section Management Endpoints](#about-the-founder-section-management-endpoints)
25. [About our Company Section Management Endpoints](#about-our-company-section-management-endpoints)
26. [Mission and Vision Section Management Endpoints](#mission-and-vision-section-management-endpoints)
27. [Core Value Management Endpoints](#core-value-management-endpoints)
28. [Core Values Section Management Endpoints](#core-values-section-management-endpoints)
29. [FAQ Hero Section Management Endpoints](#faq-hero-section-management-endpoints)
30. [FAQ Intro Paragraph Management Endpoints](#faq-intro-paragraph-management-endpoints)
31. [FAQ Category Management Endpoints](#faq-category-management-endpoints)
32. [FAQ Item Management Endpoints](#faq-item-management-endpoints)
33. [FAQ Ask Question Section Management Endpoints](#faq-ask-question-section-management-endpoints)
34. [User Submitted Questions Management Endpoints](#user-submitted-questions-management-endpoints)

---

## Authentication Endpoints

### 1. User Registration

**Postman URL**: `http://localhost:8000/api/v1/register`

**Method**: `POST`

**Headers**:
```
Content-Type: application/json
```

**JSON Data**:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

---

### 2. User Login

**Postman URL**: `http://localhost:8000/api/v1/login`

**Method**: `POST`

**Headers**:
```
Content-Type: application/json
```

**JSON Data**:
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

---

### 3. Get Authenticated User

**Postman URL**: `http://localhost:8000/api/v1/user`

**Method**: `GET`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

---

### 4. Logout

**Postman URL**: `http://localhost:8000/api/v1/logout`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

---

## User Management Endpoints

### 5. Get All Users (Super Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/users`

**Method**: `GET`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` role.

**JSON Data**: None

---

### 6. Get User by ID (Super Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/users/{id}`

**Method**: `GET`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` role. Replace `{id}` with the user ID.

**Example**: `http://localhost:8000/api/v1/users/1`

**JSON Data**: None

---

### 7. Create User (Super Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/users`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` role.

**JSON Data**:
```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "editor"
}
```

---

### 8. Update User (Super Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/users/{id}`

**Method**: `PUT` or `PATCH`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` role. Replace `{id}` with the user ID.

**JSON Data**:
```json
{
    "name": "Updated Name",
    "email": "updated@example.com",
    "role": "editor"
}
```

---

### 9. Delete User (Super Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/users/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` role. Replace `{id}` with the user ID.

**JSON Data**: None

---

## Home Page Management Endpoints

### 10. Get Home Page Content

**Postman URL**: `http://localhost:8000/api/v1/home-page`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 11. Get Home Page by ID

**Postman URL**: `http://localhost:8000/api/v1/home-page/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the home page ID.

**JSON Data**: None

---

### 12. Create or Update Home Page (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/home-page`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**: (varies based on home page structure)

---

### 13. Update Home Page (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/home-page/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

---

### 14. Delete Home Page (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/home-page/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

---

### 15. Delete Specific Field from Home Page (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/home-page/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**URL Parameters**:
- `{id}`: The ID of the home page record
- `{field}`: The field name to delete (allowed values: `title`, `button_text`, `button_url`, `description`, `background_image`, `secondary_image`)

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Field 'background_image' deleted successfully from home page.",
    "data": {
        "home_page": {
            "id": 1,
            "title": "Welcome to Our Company",
            "button_text": "Get Started",
            "button_url": "/contact",
            "description": "We provide excellent services.",
            "background_image": null,
            "secondary_image": "http://localhost:8000/storage/home-page/secondary.jpg",
            "updated_at": "2024-01-01T12:00:00.000000Z"
        }
    }
}
```

**Error Response (400)** - Invalid field name:
```json
{
    "success": false,
    "message": "Invalid field name.",
    "allowed_fields": ["title", "button_text", "button_url", "description", "background_image", "secondary_image"]
}
```

**Error Response (403)** - Unauthorized:
```json
{
    "success": false,
    "message": "Unauthorized. Only admins can manage home page content."
}
```

**Error Response (404)** - Home page not found:
```json
{
    "success": false,
    "message": "Home page not found."
}
```

---

## Service Page Management Endpoints

### 16. Get All Service Page Slides

**Postman URL**: `http://localhost:8000/api/v1/service-page`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "service_pages": [
            {
                "id": 1,
                "page_heading": "Our Services",
                "bg_image": "http://localhost:8000/storage/service-page/background1.jpg",
                "small_text": "Professional Solutions",
                "main_heading": "Expert Services",
                "outlined_heading": "For Your Business",
                "description": "We provide comprehensive solutions tailored to your needs.",
                "background_text": "Services",
                "button_text": "Learn More",
                "button_url": "/contact",
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z"
            },
            {
                "id": 2,
                "page_heading": "Premium Services",
                "bg_image": "http://localhost:8000/storage/service-page/background2.jpg",
                "small_text": "Advanced Solutions",
                "main_heading": "Expert Team",
                "outlined_heading": "Ready to Help",
                "description": "Our team of experts is ready to provide premium services.",
                "background_text": "Premium",
                "button_text": "Get Started",
                "button_url": "/get-started",
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z"
            }
        ]
    }
}
```

---

### 16. Get Service Page by ID

**Postman URL**: `http://localhost:8000/api/v1/service-page/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the service page ID.

**JSON Data**: None

---

### 17. Create Service Page Slide (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-page`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
- `page_heading` (optional, string, max:255) - Page heading
- `bg_image` (optional, image) - Background image file (jpeg,png,jpg,gif,webp, max:5MB)
- `small_text` (optional, string, max:255) - Small text
- `main_heading` (optional, string, max:255) - Main heading
- `outlined_heading` (optional, string, max:255) - Outlined heading
- `description` (optional, string) - Description text
- `background_text` (optional, string, max:255) - Background text
- `button_text` (optional, string, max:255) - Button text
- `button_url` (optional, url, max:255) - Button URL

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Service page slide created successfully",
    "data": {
        "service_page": {
            "id": 2,
            "page_heading": "Premium Services",
            "bg_image": "http://localhost:8000/storage/service-page/background2.jpg",
            "small_text": "Advanced Solutions",
            "main_heading": "Expert Team",
            "outlined_heading": "Ready to Help",
            "description": "Our team of experts is ready to provide premium services.",
            "background_text": "Premium",
            "button_text": "Get Started",
            "button_url": "/get-started",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    }
}
```

---

### 18. Update Service Page (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-page/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**: Same as create/update endpoint (all fields are optional)

---

### 19. Delete Service Page (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-page/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Service page deleted successfully"
}
```

---

### 20. Delete Specific Field from Service Page (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-page/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**URL Parameters**:
- `{id}` - Service page ID
- `{field}` - Field name to delete (one of: `page_heading`, `bg_image`, `small_text`, `main_heading`, `outlined_heading`, `description`, `background_text`, `button_text`, `button_url`)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Field 'bg_image' deleted successfully from service page.",
    "data": {
        "service_page": {
            "id": 1,
            "page_heading": "Our Services",
            "bg_image": null,
            "small_text": "Professional Solutions",
            "main_heading": "Expert Services",
            "outlined_heading": "For Your Business",
            "description": "We provide comprehensive solutions tailored to your needs.",
            "background_text": "Services",
            "button_text": "Learn More",
            "button_url": "/contact",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    }
}
```

**Error Response (400)** - Invalid field name:
```json
{
    "success": false,
    "message": "Invalid field name.",
    "allowed_fields": [
        "page_heading",
        "bg_image",
        "small_text",
        "main_heading",
        "outlined_heading",
        "description",
        "background_text",
        "button_text",
        "button_url"
    ]
}
```

---

## Features Section Management Endpoints

### 21. Get All Features Sections (Public)

**Postman URL**: `http://localhost:8000/api/v1/features-sections`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "features_sections": [
            {
                "id": 1,
                "section_title": "Our Amazing Features",
                "subtitle": "Discover what makes us special",
                "title": "Why Choose Us",
                "description": "We offer the best features for your needs",
                "features": [
                    {
                        "title": "Fast Performance",
                        "description": "Lightning fast response times"
                    },
                    {
                        "title": "24/7 Support",
                        "description": "Round the clock assistance"
                    }
                ],
                "button_text": "Learn More",
                "main_image": "http://localhost:8000/storage/features-section/main.jpg",
                "small_image": "http://localhost:8000/storage/features-section/small.jpg",
                "created_at": "2026-01-31T14:14:00.000000Z",
                "updated_at": "2026-01-31T14:14:00.000000Z"
            }
        ]
    }
}
```

---

### 22. Get Specific Features Section (Public)

**Postman URL**: `http://localhost:8000/api/v1/features-sections/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "features_section": {
            "id": 1,
            "section_title": "Our Amazing Features",
            "subtitle": "Discover what makes us special",
            "title": "Why Choose Us",
            "description": "We offer the best features for your needs",
            "features": [
                {
                    "title": "Fast Performance",
                    "description": "Lightning fast response times"
                }
            ],
            "button_text": "Learn More",
            "main_image": "http://localhost:8000/storage/features-section/main.jpg",
            "small_image": "http://localhost:8000/storage/features-section/small.jpg",
            "created_at": "2026-01-31T14:14:00.000000Z",
            "updated_at": "2026-01-31T14:14:00.000000Z"
        }
    }
}
```

---

### 23. Create Features Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/features-sections`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data**:
```json
{
    "section_title": "Our Amazing Features",
    "subtitle": "Discover what makes us special",
    "title": "Why Choose Us",
    "description": "We offer the best features for your needs",
    "features": [
        {
            "title": "Fast Performance",
            "description": "Lightning fast response times"
        },
        {
            "title": "24/7 Support",
            "description": "Round the clock assistance"
        }
    ],
    "button_text": "Learn More"
}
```

**For Form-Data (with images)**:
```
section_title: "Our Amazing Features"
subtitle: "Discover what makes us special"
title: "Why Choose Us"
description: "We offer the best features for your needs"
features[0][title]: "Fast Performance"
features[0][description]: "Lightning fast response times"
features[1][title]: "24/7 Support"
features[1][description]: "Round the clock assistance"
button_text: "Learn More"
main_image: [file]
small_image: [file]
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Features section created successfully",
    "data": {
        "features_section": {
            "id": 1,
            "section_title": "Our Amazing Features",
            "subtitle": "Discover what makes us special",
            "title": "Why Choose Us",
            "description": "We offer the best features for your needs",
            "features": [
                {
                    "title": "Fast Performance",
                    "description": "Lightning fast response times"
                }
            ],
            "button_text": "Learn More",
            "main_image": "http://localhost:8000/storage/features-section/main.jpg",
            "small_image": "http://localhost:8000/storage/features-section/small.jpg",
            "updated_at": "2026-01-31T14:14:00.000000Z"
        }
    }
}
```

---

### 24. Update Features Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/features-sections/{id}`

**Method**: `PUT` or `PATCH` or `POST` (with `_method=PUT`)

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data** (partial update):
```json
{
    "section_title": "Updated Features Title",
    "features": [
        {
            "title": "New Feature",
            "description": "Updated description"
        }
    ]
}
```

**For Form-Data (with images)**:
```
section_title: "Updated Features Title"
main_image: [file]
_method: PUT
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Features section updated successfully",
    "data": {
        "features_section": {
            "id": 1,
            "section_title": "Updated Features Title",
            "subtitle": "Discover what makes us special",
            "title": "Why Choose Us",
            "description": "We offer the best features for your needs",
            "features": [
                {
                    "title": "New Feature",
                    "description": "Updated description"
                }
            ],
            "button_text": "Learn More",
            "main_image": "http://localhost:8000/storage/features-section/new_main.jpg",
            "small_image": "http://localhost:8000/storage/features-section/small.jpg",
            "updated_at": "2026-01-31T14:20:00.000000Z"
        }
    }
}
```

---

### 25. Delete Features Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/features-sections/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Features section deleted successfully"
}
```

---

### 26. Delete Specific Field from Features Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/features-sections/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**URL Parameters**:
- `{id}`: Features section ID
- `{field}`: Field name to delete (section_title, subtitle, title, description, features, button_text, main_image, small_image)

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Field 'main_image' deleted successfully from features section.",
    "data": {
        "features_section": {
            "id": 1,
            "section_title": "Our Amazing Features",
            "subtitle": "Discover what makes us special",
            "title": "Why Choose Us",
            "description": "We offer the best features for your needs",
            "features": [
                {
                    "title": "Fast Performance",
                    "description": "Lightning fast response times"
                }
            ],
            "button_text": "Learn More",
            "main_image": null,
            "small_image": "http://localhost:8000/storage/features-section/small.jpg",
            "updated_at": "2026-01-31T14:25:00.000000Z"
        }
    }
}
```

---

## Analytics Section Management Endpoints

### 27. Get All Analytics Sections (Public)

**Postman URL**: `http://localhost:8000/api/v1/analytics-sections`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "analytics_sections": [
            {
                "id": 1,
                "section_title": "Analytics & Insights",
                "subtitle": "Data insights",
                "title": "Transform your data into actionable insights",
                "description": "Harness the power of advanced analytics to make informed decisions and drive business growth. Our comprehensive tools help you visualize trends, identify patterns, and optimize performance.",
                "features": [
                    {
                        "title": "Real-time data processing and analysis",
                        "description": "Process and analyze data in real-time for immediate insights"
                    },
                    {
                        "title": "Interactive dashboards and custom reports",
                        "description": "Create beautiful dashboards and reports tailored to your needs"
                    },
                    {
                        "title": "AI-powered predictions and recommendations",
                        "description": "Leverage AI to predict trends and get intelligent recommendations"
                    }
                ],
                "button_text": "Explore Analytics",
                "button_url": "/analytics",
                "main_image": "http://localhost:8000/storage/analytics-section/analytic_img.png",
                "small_image": "http://localhost:8000/storage/analytics-section/analytic_small.png",
                "created_at": "2026-01-31T15:40:00.000000Z",
                "updated_at": "2026-01-31T15:40:00.000000Z"
            }
        ]
    }
}
```

---

### 28. Get Specific Analytics Section (Public)

**Postman URL**: `http://localhost:8000/api/v1/analytics-sections/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "analytics_section": {
            "id": 1,
            "section_title": "Analytics & Insights",
            "subtitle": "Data insights",
            "title": "Transform your data into actionable insights",
            "description": "Harness the power of advanced analytics to make informed decisions and drive business growth. Our comprehensive tools help you visualize trends, identify patterns, and optimize performance.",
            "features": [
                {
                    "title": "Real-time data processing and analysis",
                    "description": "Process and analyze data in real-time for immediate insights"
                }
            ],
            "button_text": "Explore Analytics",
            "button_url": "/analytics",
            "main_image": "http://localhost:8000/storage/analytics-section/analytic_img.png",
            "small_image": "http://localhost:8000/storage/analytics-section/analytic_small.png",
            "created_at": "2026-01-31T15:40:00.000000Z",
            "updated_at": "2026-01-31T15:40:00.000000Z"
        }
    }
}
```

---

### 29. Create Analytics Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/analytics-sections`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data**:
```json
{
    "section_title": "Analytics & Insights",
    "subtitle": "Data insights",
    "title": "Transform your data into actionable insights",
    "description": "Harness the power of advanced analytics to make informed decisions and drive business growth. Our comprehensive tools help you visualize trends, identify patterns, and optimize performance.",
    "features": [
        {
            "title": "Real-time data processing and analysis",
            "description": "Process and analyze data in real-time for immediate insights"
        },
        {
            "title": "Interactive dashboards and custom reports",
            "description": "Create beautiful dashboards and reports tailored to your needs"
        },
        {
            "title": "AI-powered predictions and recommendations",
            "description": "Leverage AI to predict trends and get intelligent recommendations"
        }
    ],
    "button_text": "Explore Analytics",
    "button_url": "/analytics"
}
```

**For Form-Data (with images)**:
```
section_title: "Analytics & Insights"
subtitle: "Data insights"
title: "Transform your data into actionable insights"
description: "Harness the power of advanced analytics to make informed decisions and drive business growth. Our comprehensive tools help you visualize trends, identify patterns, and optimize performance."
features[0][title]: "Real-time data processing and analysis"
features[0][description]: "Process and analyze data in real-time for immediate insights"
features[1][title]: "Interactive dashboards and custom reports"
features[1][description]: "Create beautiful dashboards and reports tailored to your needs"
features[2][title]: "AI-powered predictions and recommendations"
features[2][description]: "Leverage AI to predict trends and get intelligent recommendations"
button_text: "Explore Analytics"
button_url: "/analytics"
main_image: [file]
small_image: [file]
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Analytics section created successfully",
    "data": {
        "analytics_section": {
            "id": 1,
            "section_title": "Analytics & Insights",
            "subtitle": "Data insights",
            "title": "Transform your data into actionable insights",
            "description": "Harness the power of advanced analytics to make informed decisions and drive business growth. Our comprehensive tools help you visualize trends, identify patterns, and optimize performance.",
            "features": [
                {
                    "title": "Real-time data processing and analysis",
                    "description": "Process and analyze data in real-time for immediate insights"
                }
            ],
            "button_text": "Explore Analytics",
            "button_url": "/analytics",
            "main_image": "http://localhost:8000/storage/analytics-section/analytic_img.png",
            "small_image": "http://localhost:8000/storage/analytics-section/analytic_small.png",
            "updated_at": "2026-01-31T15:40:00.000000Z"
        }
    }
}
```

---

### 30. Update Analytics Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/analytics-sections/{id}`

**Method**: `PUT` or `PATCH` or `POST` (with `_method=PUT`)

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data** (partial update):
```json
{
    "section_title": "Updated Analytics Title",
    "features": [
        {
            "title": "New Feature",
            "description": "Updated description"
        }
    ],
    "button_url": "/new-analytics"
}
```

**For Form-Data (with images)**:
```
section_title: "Updated Analytics Title"
main_image: [file]
_method: PUT
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Analytics section updated successfully",
    "data": {
        "analytics_section": {
            "id": 1,
            "section_title": "Updated Analytics Title",
            "subtitle": "Data insights",
            "title": "Transform your data into actionable insights",
            "description": "Harness the power of advanced analytics to make informed decisions and drive business growth. Our comprehensive tools help you visualize trends, identify patterns, and optimize performance.",
            "features": [
                {
                    "title": "New Feature",
                    "description": "Updated description"
                }
            ],
            "button_text": "Explore Analytics",
            "button_url": "/new-analytics",
            "main_image": "http://localhost:8000/storage/analytics-section/new_analytic_img.png",
            "small_image": "http://localhost:8000/storage/analytics-section/analytic_small.png",
            "updated_at": "2026-01-31T15:45:00.000000Z"
        }
    }
}
```

---

### 31. Delete Analytics Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/analytics-sections/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Analytics section deleted successfully"
}
```

---

### 32. Delete Specific Field from Analytics Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/analytics-sections/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**URL Parameters**:
- `{id}`: Analytics section ID
- `{field}`: Field name to delete (section_title, subtitle, title, description, features, button_text, button_url, main_image, small_image)

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Field 'main_image' deleted successfully from analytics section.",
    "data": {
        "analytics_section": {
            "id": 1,
            "section_title": "Analytics & Insights",
            "subtitle": "Data insights",
            "title": "Transform your data into actionable insights",
            "description": "Harness the power of advanced analytics to make informed decisions and drive business growth. Our comprehensive tools help you visualize trends, identify patterns, and optimize performance.",
            "features": [
                {
                    "title": "Real-time data processing and analysis",
                    "description": "Process and analyze data in real-time for immediate insights"
                }
            ],
            "button_text": "Explore Analytics",
            "button_url": "/analytics",
            "main_image": null,
            "small_image": "http://localhost:8000/storage/analytics-section/analytic_small.png",
            "updated_at": "2026-01-31T15:50:00.000000Z"
        }
    }
}
```

---

## Chart Section Management Endpoints

### 33. Get All Chart Sections (Public)

**Postman URL**: `http://localhost:8000/api/v1/chart-sections`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "chart_sections": [
            {
                "id": 1,
                "section_title": "Performance Charts",
                "subtitle": "Performance metrics",
                "title": "Track your progress with detailed analytics",
                "description": "Monitor key performance indicators and gain valuable insights into your business operations. Our comprehensive dashboard provides real-time data visualization and reporting tools to help you make informed decisions.",
                "features": [
                    {
                        "title": "Comprehensive data visualization tools",
                        "description": "Advanced tools for visualizing complex data sets"
                    },
                    {
                        "title": "Customizable reports and dashboards",
                        "description": "Tailor reports and dashboards to your specific needs"
                    },
                    {
                        "title": "Real-time performance monitoring",
                        "description": "Monitor performance metrics in real-time"
                    }
                ],
                "button_text": "View Charts",
                "button_url": "/charts",
                "main_image": "http://localhost:8000/storage/chart-section/Chart.png",
                "small_image": "http://localhost:8000/storage/chart-section/12.png",
                "created_at": "2026-01-31T16:13:00.000000Z",
                "updated_at": "2026-01-31T16:13:00.000000Z"
            }
        ]
    }
}
```

---

### 34. Get Specific Chart Section (Public)

**Postman URL**: `http://localhost:8000/api/v1/chart-sections/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "chart_section": {
            "id": 1,
            "section_title": "Performance Charts",
            "subtitle": "Performance metrics",
            "title": "Track your progress with detailed analytics",
            "description": "Monitor key performance indicators and gain valuable insights into your business operations. Our comprehensive dashboard provides real-time data visualization and reporting tools to help you make informed decisions.",
            "features": [
                {
                    "title": "Comprehensive data visualization tools",
                    "description": "Advanced tools for visualizing complex data sets"
                }
            ],
            "button_text": "View Charts",
            "button_url": "/charts",
            "main_image": "http://localhost:8000/storage/chart-section/Chart.png",
            "small_image": "http://localhost:8000/storage/chart-section/12.png",
            "created_at": "2026-01-31T16:13:00.000000Z",
            "updated_at": "2026-01-31T16:13:00.000000Z"
        }
    }
}
```

---

### 35. Create Chart Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/chart-sections`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data**:
```json
{
    "section_title": "Performance Charts",
    "subtitle": "Performance metrics",
    "title": "Track your progress with detailed analytics",
    "description": "Monitor key performance indicators and gain valuable insights into your business operations. Our comprehensive dashboard provides real-time data visualization and reporting tools to help you make informed decisions.",
    "features": [
        {
            "title": "Comprehensive data visualization tools",
            "description": "Advanced tools for visualizing complex data sets"
        },
        {
            "title": "Customizable reports and dashboards",
            "description": "Tailor reports and dashboards to your specific needs"
        },
        {
            "title": "Real-time performance monitoring",
            "description": "Monitor performance metrics in real-time"
        }
    ],
    "button_text": "View Charts",
    "button_url": "/charts"
}
```

**For Form-Data (with images)**:
```
section_title: "Performance Charts"
subtitle: "Performance metrics"
title: "Track your progress with detailed analytics"
description: "Monitor key performance indicators and gain valuable insights into your business operations. Our comprehensive dashboard provides real-time data visualization and reporting tools to help you make informed decisions."
features[0][title]: "Comprehensive data visualization tools"
features[0][description]: "Advanced tools for visualizing complex data sets"
features[1][title]: "Customizable reports and dashboards"
features[1][description]: "Tailor reports and dashboards to your specific needs"
features[2][title]: "Real-time performance monitoring"
features[2][description]: "Monitor performance metrics in real-time"
button_text: "View Charts"
button_url: "/charts"
main_image: [file]
small_image: [file]
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Chart section created successfully",
    "data": {
        "chart_section": {
            "id": 1,
            "section_title": "Performance Charts",
            "subtitle": "Performance metrics",
            "title": "Track your progress with detailed analytics",
            "description": "Monitor key performance indicators and gain valuable insights into your business operations. Our comprehensive dashboard provides real-time data visualization and reporting tools to help you make informed decisions.",
            "features": [
                {
                    "title": "Comprehensive data visualization tools",
                    "description": "Advanced tools for visualizing complex data sets"
                }
            ],
            "button_text": "View Charts",
            "button_url": "/charts",
            "main_image": "http://localhost:8000/storage/chart-section/Chart.png",
            "small_image": "http://localhost:8000/storage/chart-section/12.png",
            "updated_at": "2026-01-31T16:13:00.000000Z"
        }
    }
}
```

---

### 36. Update Chart Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/chart-sections/{id}`

**Method**: `PUT` or `PATCH` or `POST` (with `_method=PUT`)

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data** (partial update):
```json
{
    "section_title": "Updated Chart Title",
    "features": [
        {
            "title": "New Feature",
            "description": "Updated description"
        }
    ],
    "button_url": "/new-charts"
}
```

**For Form-Data (with images)**:
```
section_title: "Updated Chart Title"
main_image: [file]
_method: PUT
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Chart section updated successfully",
    "data": {
        "chart_section": {
            "id": 1,
            "section_title": "Updated Chart Title",
            "subtitle": "Performance metrics",
            "title": "Track your progress with detailed analytics",
            "description": "Monitor key performance indicators and gain valuable insights into your business operations. Our comprehensive dashboard provides real-time data visualization and reporting tools to help you make informed decisions.",
            "features": [
                {
                    "title": "New Feature",
                    "description": "Updated description"
                }
            ],
            "button_text": "View Charts",
            "button_url": "/new-charts",
            "main_image": "http://localhost:8000/storage/chart-section/new_Chart.png",
            "small_image": "http://localhost:8000/storage/chart-section/12.png",
            "updated_at": "2026-01-31T16:18:00.000000Z"
        }
    }
}
```

---

### 37. Delete Chart Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/chart-sections/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Chart section deleted successfully"
}
```

---

### 38. Delete Specific Field from Chart Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/chart-sections/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**URL Parameters**:
- `{id}`: Chart section ID
- `{field}`: Field name to delete (section_title, subtitle, title, description, features, button_text, button_url, main_image, small_image)

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Field 'main_image' deleted successfully from chart section.",
    "data": {
        "chart_section": {
            "id": 1,
            "section_title": "Performance Charts",
            "subtitle": "Performance metrics",
            "title": "Track your progress with detailed analytics",
            "description": "Monitor key performance indicators and gain valuable insights into your business operations. Our comprehensive dashboard provides real-time data visualization and reporting tools to help you make informed decisions.",
            "features": [
                {
                    "title": "Comprehensive data visualization tools",
                    "description": "Advanced tools for visualizing complex data sets"
                }
            ],
            "button_text": "View Charts",
            "button_url": "/charts",
            "main_image": null,
            "small_image": "http://localhost:8000/storage/chart-section/12.png",
            "updated_at": "2026-01-31T16:23:00.000000Z"
        }
    }
}
```

---

## Tab Section Management Endpoints

### 39. Get All Tab Sections (Public)

**Postman URL**: `http://localhost:8000/api/v1/tab-sections`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "tab_sections": [
            {
                "id": 1,
                "section_title": "We Provide Expert Service",
                "section_description": "We aim to earn your trust and have a long term relationship with you. Our team provides exceptional automotive services to keep your vehicle running smoothly.",
                "tabs": [
                    {
                        "id": 1,
                        "tab_title": "Additional Services",
                        "tab_icon": "http://localhost:8000/storage/tab-section/icons/icon1.png",
                        "tab_content": "We offer a comprehensive range of additional services to meet all your automotive needs.",
                        "features": [
                            "We Make It Easy",
                            "OEM Factory Parts Warranty",
                            "Fair And Transparent Pricing",
                            "Happiness Guaranteed"
                        ],
                        "tab_image": "http://localhost:8000/storage/tab-section/images/analytic_small.png",
                        "order": 0
                    },
                    {
                        "id": 2,
                        "tab_title": "Our Advantages",
                        "tab_icon": "http://localhost:8000/storage/tab-section/icons/icon2.png",
                        "tab_content": "Discover the advantages that set us apart from the competition.",
                        "features": [
                            "Expert Technicians",
                            "Latest Technology",
                            "24/7 Support"
                        ],
                        "tab_image": null,
                        "order": 1
                    },
                    {
                        "id": 3,
                        "tab_title": "About Company",
                        "tab_icon": "http://localhost:8000/storage/tab-section/icons/icon3.png",
                        "tab_content": "Learn more about our company history and values.",
                        "features": [],
                        "tab_image": null,
                        "order": 2
                    }
                ],
                "created_at": "2026-01-31T16:37:00.000000Z",
                "updated_at": "2026-01-31T16:37:00.000000Z"
            }
        ]
    }
}
```

---

### 40. Get Specific Tab Section (Public)

**Postman URL**: `http://localhost:8000/api/v1/tab-sections/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "tab_section": {
            "id": 1,
            "section_title": "We Provide Expert Service",
            "section_description": "We aim to earn your trust and have a long term relationship with you. Our team provides exceptional automotive services to keep your vehicle running smoothly.",
            "tabs": [
                {
                    "id": 1,
                    "tab_title": "Additional Services",
                    "tab_icon": "http://localhost:8000/storage/tab-section/icons/icon1.png",
                    "tab_content": "We offer a comprehensive range of additional services to meet all your automotive needs.",
                    "features": [
                        "We Make It Easy",
                        "OEM Factory Parts Warranty",
                        "Fair And Transparent Pricing",
                        "Happiness Guaranteed"
                    ],
                    "tab_image": "http://localhost:8000/storage/tab-section/images/analytic_small.png",
                    "order": 0
                }
            ],
            "created_at": "2026-01-31T16:37:00.000000Z",
            "updated_at": "2026-01-31T16:37:00.000000Z"
        }
    }
}
```

---

### 41. Create Tab Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/tab-sections`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data**:
```json
{
    "section_title": "We Provide Expert Service",
    "section_description": "We aim to earn your trust and have a long term relationship with you. Our team provides exceptional automotive services to keep your vehicle running smoothly.",
    "tab1_title": "Additional Services",
    "tab1_content": "We offer a comprehensive range of additional services to meet all your automotive needs.",
    "tab1_features": [
        "We Make It Easy",
        "OEM Factory Parts Warranty",
        "Fair And Transparent Pricing",
        "Happiness Guaranteed"
    ],
    "tab2_title": "Our Advantages",
    "tab2_content": "Discover the advantages that set us apart from the competition.",
    "tab2_features": [
        "Expert Technicians",
        "Latest Technology",
        "24/7 Support"
    ],
    "tab3_title": "About Company",
    "tab3_features": [
        {
            "title": "Feature Title 1",
            "description": "Feature description 1"
        },
        {
            "title": "Feature Title 2", 
            "description": "Feature description 2"
        }
    ]
}
```

**For Form-Data (with images)**:
```
section_title: "We Provide Expert Service"
section_description: "We aim to earn your trust and have a long term relationship with you. Our team provides exceptional automotive services to keep your vehicle running smoothly."
tab1_title: "Additional Services"
tab1_content: "We offer a comprehensive range of additional services to meet all your automotive needs."
tab1_features[]: "We Make It Easy"
tab1_features[]: "OEM Factory Parts Warranty"
tab1_features[]: "Fair And Transparent Pricing"
tab1_features[]: "Happiness Guaranteed"
tab1_icon: [file]
tab1_image: [file]
tab2_title: "Our Advantages"
tab2_content: "Discover the advantages that set us apart from the competition."
tab2_features[]: "Expert Technicians"
tab2_features[]: "Latest Technology"
tab2_features[]: "24/7 Support"
tab2_icon: [file]
tab2_image: [file]
tab3_title: "About Company"
tab3_features[0][title]: "Feature Title 1"
tab3_features[0][description]: "Feature description 1"
tab3_features[1][title]: "Feature Title 2"
tab3_features[1][description]: "Feature description 2"
tab3_icon: [file]
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Tab section created successfully",
    "data": {
        "tab_section": {
            "id": 1,
            "section_title": "We Provide Expert Service",
            "section_description": "We aim to earn your trust and have a long term relationship with you. Our team provides exceptional automotive services to keep your vehicle running smoothly.",
            "tabs": [
                {
                    "id": 1,
                    "tab_title": "Additional Services",
                    "tab_icon": "http://localhost:8000/storage/tab-section/icons/icon1.png",
                    "tab_content": "We offer a comprehensive range of additional services to meet all your automotive needs.",
                    "features": [
                        "We Make It Easy",
                        "OEM Factory Parts Warranty",
                        "Fair And Transparent Pricing",
                        "Happiness Guaranteed"
                    ],
                    "tab_image": "http://localhost:8000/storage/tab-section/images/analytic_small.png",
                    "order": 0
                }
            ],
            "updated_at": "2026-01-31T16:37:00.000000Z"
        }
    }
}
```

---

### 42. Update Tab Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/tab-sections/{id}`

**Method**: `PUT` or `PATCH` or `POST` (with `_method=PUT`)

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data** (partial update):
```json
{
    "section_title": "Updated Tab Section Title",
    "tab1_title": "Updated Additional Services",
    "tab1_content": "Updated content for additional services.",
    "tab1_features": [
        "Updated Feature 1",
        "Updated Feature 2"
    ],
    "tab2_title": "Updated Our Advantages",
    "tab2_content": "Updated advantages content."
}
```

**For Form-Data (with images)**:
```
section_title: "Updated Tab Section Title"
tab1_title: "Updated Additional Services"
tab1_content: "Updated content for additional services."
tab1_features[]: "Updated Feature 1"
tab1_features[]: "Updated Feature 2"
tab1_icon: [file]
tab1_image: [file]
tab2_title: "Updated Our Advantages"
tab2_content: "Updated advantages content."
tab2_icon: [file]
tab3_title: "Updated About Company"
tab3_features[0][title]: "Updated Feature Title 1"
tab3_features[0][description]: "Updated Feature description 1"
tab3_icon: [file]
_method: PUT
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Tab section updated successfully",
    "data": {
        "tab_section": {
            "id": 1,
            "section_title": "Updated Tab Section Title",
            "section_description": "We aim to earn your trust and have a long term relationship with you. Our team provides exceptional automotive services to keep your vehicle running smoothly.",
            "tabs": [
                {
                    "id": 1,
                    "tab_title": "Updated Additional Services",
                    "tab_icon": "http://localhost:8000/storage/tab-section/icons/new_icon1.png",
                    "tab_content": "Updated content for additional services.",
                    "features": [
                        "Updated Feature 1",
                        "Updated Feature 2"
                    ],
                    "tab_image": "http://localhost:8000/storage/tab-section/images/new_analytic_small.png",
                    "order": 0
                }
            ],
            "updated_at": "2026-01-31T16:42:00.000000Z"
        }
    }
}
```

---

### 43. Delete Tab Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/tab-sections/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Tab section deleted successfully"
}
```

---

### 44. Delete Specific Field from Tab Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/tab-sections/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**URL Parameters**:
- `{id}`: Tab section ID
- `{field}`: Field name to delete (section_title, section_description, tab1_title, tab1_icon, tab1_content, tab1_features, tab1_image, tab2_title, tab2_icon, tab2_content, tab2_features, tab2_image, tab3_title, tab3_icon, tab3_features, tab3_image)

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Field 'section_title' deleted successfully from tab section.",
    "data": {
        "tab_section": {
            "id": 1,
            "section_title": null,
            "section_description": "We aim to earn your trust and have a long term relationship with you. Our team provides exceptional automotive services to keep your vehicle running smoothly.",
            "tabs": [
                {
                    "id": 1,
                    "tab_title": "Additional Services",
                    "tab_icon": "http://localhost:8000/storage/tab-section/icons/icon1.png",
                    "tab_content": "We offer a comprehensive range of additional services to meet all your automotive needs.",
                    "features": [
                        "We Make It Easy",
                        "OEM Factory Parts Warranty",
                        "Fair And Transparent Pricing",
                        "Happiness Guaranteed"
                    ],
                    "tab_image": "http://localhost:8000/storage/tab-section/images/analytic_small.png",
                    "order": 0
                }
            ],
            "updated_at": "2026-01-31T16:47:00.000000Z"
        }
    }
}
```

---

## Showcase Section Management Endpoints

### 45. Get All Showcase Sections (Public)

**Postman URL**: `http://localhost:8000/api/v1/showcase-sections`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "showcase_sections": [
            {
                "id": 1,
                "section_title": "Our Showcase",
                "section_description": "Explore our amazing work and projects that showcase our expertise and creativity.",
                "section_image": "http://localhost:8000/storage/showcase-section/images/section-hero.jpg",
                "background_image": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg.jpg",
                "background_image_mobile": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg-mobile.jpg",
                "showcase_items": [
                    {
                        "id": 1,
                        "title": "Project Alpha",
                        "description": "A revolutionary web application that transforms how businesses manage their operations.",
                        "image": "http://localhost:8000/storage/showcase-section/images/project1.jpg",
                        "order": 0
                    },
                    {
                        "id": 2,
                        "title": "Mobile Innovation",
                        "description": "Cutting-edge mobile solution that brings seamless user experience to your fingertips.",
                        "image": "http://localhost:8000/storage/showcase-section/images/project2.jpg",
                        "order": 1
                    },
                    {
                        "id": 3,
                        "title": "Digital Transformation",
                        "description": "Complete digital overhaul that modernizes legacy systems and processes.",
                        "image": "http://localhost:8000/storage/showcase-section/images/project3.jpg",
                        "order": 2
                    }
                ],
                "created_at": "2026-01-31T22:00:00.000000Z",
                "updated_at": "2026-01-31T22:00:00.000000Z"
            }
        ]
    }
}
```

---

### 46. Get Specific Showcase Section (Public)

**Postman URL**: `http://localhost:8000/api/v1/showcase-sections/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "showcase_section": {
            "id": 1,
            "section_title": "Our Showcase",
            "section_description": "Explore our amazing work and projects that showcase our expertise and creativity.",
            "section_image": "http://localhost:8000/storage/showcase-section/images/section-hero.jpg",
            "background_image": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg.jpg",
            "background_image_mobile": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg-mobile.jpg",
            "showcase_items": [
                {
                    "id": 1,
                    "title": "Project Alpha",
                    "description": "A revolutionary web application that transforms how businesses manage their operations.",
                    "image": "http://localhost:8000/storage/showcase-section/images/project1.jpg",
                    "order": 0
                }
            ],
            "created_at": "2026-01-31T22:00:00.000000Z",
            "updated_at": "2026-01-31T22:00:00.000000Z"
        }
    }
}
```

---

### 47. Create Showcase Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/showcase-sections`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data**:
```json
{
    "section_title": "Our Showcase",
    "section_description": "Explore our amazing work and projects that showcase our expertise and creativity.",
    "background_image": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg.jpg",
    "background_image_mobile": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg-mobile.jpg",
    "showcase_items": [
        {
            "title": "Project Alpha",
            "description": "A revolutionary web application that transforms how businesses manage their operations.",
            "order": 0
        },
        {
            "title": "Mobile Innovation",
            "description": "Cutting-edge mobile solution that brings seamless user experience to your fingertips.",
            "order": 1
        },
        {
            "title": "Digital Transformation",
            "description": "Complete digital overhaul that modernizes legacy systems and processes.",
            "order": 2
        }
    ]
}
```

**For Form-Data (with images)**:
```
section_title: "Our Showcase"
section_description: "Explore our amazing work and projects that showcase our expertise and creativity."
section_image: [file]
background_image: [file]
background_image_mobile: [file]
showcase_items[0][title]: "Project Alpha"
showcase_items[0][description]: "A revolutionary web application that transforms how businesses manage their operations."
showcase_items[0][order]: 0
showcase_items[0][image]: [file]
showcase_items[1][title]: "Mobile Innovation"
showcase_items[1][description]: "Cutting-edge mobile solution that brings seamless user experience to your fingertips."
showcase_items[1][order]: 1
showcase_items[1][image]: [file]
showcase_items[2][title]: "Digital Transformation"
showcase_items[2][description]: "Complete digital overhaul that modernizes legacy systems and processes."
showcase_items[2][order]: 2
showcase_items[2][image]: [file]
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Showcase section created successfully",
    "data": {
        "showcase_section": {
            "id": 1,
            "section_title": "Our Showcase",
            "section_description": "Explore our amazing work and projects that showcase our expertise and creativity.",
            "section_image": "http://localhost:8000/storage/showcase-section/images/section-hero.jpg",
            "background_image": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg.jpg",
            "background_image_mobile": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg-mobile.jpg",
            "showcase_items": [
                {
                    "id": 1,
                    "title": "Project Alpha",
                    "description": "A revolutionary web application that transforms how businesses manage their operations.",
                    "image": "http://localhost:8000/storage/showcase-section/images/project1.jpg",
                    "order": 0
                }
            ],
            "updated_at": "2026-01-31T22:00:00.000000Z"
        }
    }
}
```

---

### 48. Update Showcase Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/showcase-sections/{id}`

**Method**: `PUT` or `PATCH` or `POST` (with `_method=PUT`)

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**JSON Data** (partial update):
```json
{
    "section_title": "Updated Showcase Title",
    "showcase_items": [
        {
            "id": 1,
            "title": "Updated Project Alpha",
            "description": "Updated description for the project.",
            "order": 0
        },
        {
            "id": 2,
            "title": "Mobile Innovation",
            "description": "Cutting-edge mobile solution that brings seamless user experience to your fingertips.",
            "order": 1
        }
    ]
}
```

**For Form-Data (with images)**:
```
section_title: "Updated Showcase Title"
section_image: [file]
showcase_items[0][id]: 1
showcase_items[0][title]: "Updated Project Alpha"
showcase_items[0][description]: "Updated description for the project."
showcase_items[0][order]: 0
showcase_items[0][image]: [file]
showcase_items[1][id]: 2
showcase_items[1][title]: "Mobile Innovation"
showcase_items[1][description]: "Cutting-edge mobile solution that brings seamless user experience to your fingertips."
showcase_items[1][order]: 1
_method: PUT
```

**Note**: 
- Optional section-level fields: `section_title`, `section_description`, `section_image`, `background_image`, `background_image_mobile`. Use form-data to upload images (jpeg, png, jpg, gif, webp, max 5MB).
- Include the `id` field for existing items to update them instead of creating new ones
- Items without an `id` will be created as new items
- Existing items not included in the request will be deleted
- Images are only updated if a new file is provided

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Showcase section updated successfully",
    "data": {
        "showcase_section": {
            "id": 1,
            "section_title": "Updated Showcase Title",
            "section_description": "Explore our amazing work and projects that showcase our expertise and creativity.",
            "section_image": "http://localhost:8000/storage/showcase-section/images/section-hero.jpg",
            "background_image": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg.jpg",
            "background_image_mobile": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg-mobile.jpg",
            "showcase_items": [
                {
                    "id": 1,
                    "title": "Updated Project Alpha",
                    "description": "Updated description for the project.",
                    "image": "http://localhost:8000/storage/showcase-section/images/updated_project1.jpg",
                    "order": 0
                }
            ],
            "updated_at": "2026-01-31T22:05:00.000000Z"
        }
    }
}
```

---

### 49. Delete Showcase Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/showcase-sections/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Showcase section deleted successfully"
}
```

---

### 50. Delete Specific Field from Showcase Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/showcase-sections/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**JSON Data**: None

**URL Parameters**:
- `{id}`: Showcase section ID
- `{field}`: Field name to delete (section_title, section_description, section_image, background_image, background_image_mobile)

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Field 'section_title' deleted successfully from showcase section.",
    "data": {
        "showcase_section": {
            "id": 1,
            "section_title": null,
            "section_description": "Explore our amazing work and projects that showcase our expertise and creativity.",
            "section_image": "http://localhost:8000/storage/showcase-section/images/section-hero.jpg",
            "background_image": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg.jpg",
            "background_image_mobile": "http://localhost:8000/storage/showcase-section/backgrounds/showcase-bg-mobile.jpg",
            "showcase_items": [
                {
                    "id": 1,
                    "title": "Project Alpha",
                    "description": "A revolutionary web application that transforms how businesses manage their operations.",
                    "image": "http://localhost:8000/storage/showcase-section/images/project1.jpg",
                    "order": 0
                }
            ],
            "updated_at": "2026-01-31T22:10:00.000000Z"
        }
    }
}
```

---

## About Section Management Endpoints

### 51. Get About Section Content

**Postman URL**: `http://localhost:8000/api/v1/about-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 16. Create or Update About Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

---

### 17. Update About Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

---

### 18. Delete Specific Field from About Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Allows deletion of individual fields (e.g., images) without deleting the entire section.

**Example**: `http://localhost:8000/api/v1/about-section/1/field/about_image_1`

**JSON Data**: None

---

### 19. Delete About Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

---

## Service Section Management Endpoints

### 20. Get Service Section Content

**Postman URL**: `http://localhost:8000/api/v1/service-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 21. Create or Update Service Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
```
subtitle: [string - optional]
main_title: [string - optional]
button_text: [string - optional]
background_image: [file upload - optional]
```

---

### 22. Update Service Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

---

### 23. Delete Specific Field from Service Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Available Fields**: `background_image`

**JSON Data**: None

---

### 24. Delete Service Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

---

## Service Card Management Endpoints

### 25. Get All Service Cards

**Postman URL**: `http://localhost:8000/api/v1/service-cards`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns all service cards ordered by `order` field.

---

### 26. Get Service Card by ID

**Postman URL**: `http://localhost:8000/api/v1/service-cards/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 27. Create Service Card (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-cards`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
```
subtitle: [string - optional]
description: [string - optional]
image: [file upload - optional]
order: [integer - optional]
```

---

### 28. Update Service Card (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-cards/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

---

### 29. Delete Service Card (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/service-cards/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

---

## What We Create Section Management Endpoints

### 30. Get What We Create Section Content

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 31. Create or Update What We Create Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
```
section_title: [string - optional]
background_image: [file upload - optional]
```

---

### 32. Update What We Create Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

---

### 33. Delete Specific Field from What We Create Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Available Fields**: `background_image`

**JSON Data**: None

---

### 34. Delete What We Create Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

---

## What We Create Tab Management Endpoints

### 35. Get All What We Create Tabs

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-tabs`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns all tabs ordered by `order` field.

---

### 36. Get What We Create Tab by ID

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-tabs/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 37. Create What We Create Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-tabs`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
```
category_tab_id: [integer - required]
tag_label: [string - optional] (auto-populated from category_name if not provided)
main_heading: [string - optional]
description: [string - optional]
features: [JSON array - optional]
button_text: [string - optional]
image_1: [file upload - optional]
image_2: [file upload - optional]
image_3: [file upload - optional]
order: [integer - optional]
```

**Note**: The `tag_label` field will automatically be set to the `category_name` of the associated `category_tab_id` if not provided.

---

### 38. Update What We Create Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-tabs/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

---

### 39. Delete Specific Field from What We Create Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-tabs/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Available Fields**: `image_1`, `image_2`, `image_3`

**JSON Data**: None

---

### 40. Delete What We Create Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/what-we-create-tabs/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

---

## Category Tab Management Endpoints

### 41. Get All Category Tabs

**Postman URL**: `http://localhost:8000/api/v1/category-tabs`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns all category tabs ordered by `order` field.

---

### 42. Get Category Tab by ID

**Postman URL**: `http://localhost:8000/api/v1/category-tabs/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns category tab with all associated `what_we_create_tabs`.

---

### 43. Create Category Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/category-tabs`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**:
```json
{
    "category_name": "Data Visualization",
    "order": 1
}
```

---

### 44. Update Category Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/category-tabs/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**:
```json
{
    "category_name": "Updated Category Name",
    "order": 2
}
```

---

### 45. Delete Category Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/category-tabs/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. This will permanently delete the category tab and all its associated tabs (with all their images).

**JSON Data**: None

---

## Why Choose Us Section Management Endpoints

### 46. Get Why Choose Us Section Content

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 47. Create or Update Why Choose Us Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
```
section_title: Why Choose Us
background_image: [file upload - optional]
image_1: [file upload - optional]
image_2: [file upload - optional]
```

**JSON Alternative (without images)**:
```json
{
    "section_title": "Why Choose Us"
}
```

---

### 48. Update Why Choose Us Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
```
section_title: Why Choose Us
background_image: [file upload - optional]
image_1: [file upload - optional]
image_2: [file upload - optional]
```

**Deleting Images**:
To delete an image, set it to `null` or `"delete"`:
```json
{
    "background_image": null,
    "image_1": "delete",
    "image_2": null
}
```

---

### 49. Delete Specific Field from Why Choose Us Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Available Fields**: `background_image`, `image_1`, `image_2`

**Examples**:
- Delete image_1: `DELETE /api/v1/why-choose-us-section/1/field/image_1`
- Delete background_image: `DELETE /api/v1/why-choose-us-section/1/field/background_image`

**JSON Data**: None

---

### 50. Delete Why Choose Us Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

---

## Why Choose Us Tab Management Endpoints

### 51. Get All Why Choose Us Tabs

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-tabs`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns all tabs ordered by the `order` field.

---

### 52. Get Why Choose Us Tab by ID

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-tabs/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 53. Create Why Choose Us Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-tabs`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**:
```json
{
    "title": "Adorable Pricing",
    "description": "Lorem ipsum dolor sit amet consectetur adipiscing elit.",
    "order": 1
}
```

**Note**: The `order` field is optional. If not provided, it will be set to the maximum order + 1.

---

### 54. Update Why Choose Us Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-tabs/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**:
```json
{
    "title": "Updated Title",
    "description": "Updated description text",
    "order": 2
}
```

---

### 55. Delete Why Choose Us Tab (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/why-choose-us-tabs/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**: None

---

## Our Facts Section Management Endpoints

### 56. Get Our Facts Section Content

**Postman URL**: `http://localhost:8000/api/v1/our-facts-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 57. Create or Update Our Facts Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-facts-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new our facts section or updates existing one.

**Form Data**:
```
section_title: Our Facts
small_description: A short tagline or description for the section
large_number: 15+
background_image: [file upload - optional]
```

**JSON Alternative (without images)**:
```json
{
    "section_title": "Our Facts",
    "small_description": "A short tagline or description for the section",
    "large_number": "15+"
}
```

---

### 58. Update Our Facts Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-facts-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/our-facts-section/1`

**Form Data**:
```
section_title: Our Facts
small_description: A short tagline or description for the section
large_number: 20+
background_image: [file upload - optional]
```

**Deleting Background Image**:
To delete the background image, set it to `null` or `"delete"`:
```json
{
    "background_image": null
}
```

---

### 59. Delete Specific Field from Our Facts Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-facts-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. This endpoint allows you to clear a single field (set to null) without deleting the entire section. For `background_image`, the file is also removed from storage.

**Example**: `http://localhost:8000/api/v1/our-facts-section/1/field/background_image`

**Available Fields**: `section_title`, `small_description`, `large_number`, `background_image`

**JSON Data**: None

---

### 60. Delete Our Facts Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-facts-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the section ID. This will permanently delete the section and the associated background image.

**Example**: `http://localhost:8000/api/v1/our-facts-section/1`

**JSON Data**: None

---

## Our Fact Management Endpoints

### 61. Get All Our Facts

**Postman URL**: `http://localhost:8000/api/v1/our-facts`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns all facts ordered by the `order` field.

---

### 62. Get Our Fact by ID

**Postman URL**: `http://localhost:8000/api/v1/our-facts/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the fact ID.

**Example**: `http://localhost:8000/api/v1/our-facts/1`

**JSON Data**: None

---

### 63. Create Our Fact (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-facts`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**:
```json
{
    "percentage": "99%",
    "label": "Satisfaction Rate",
    "order": 1
}
```

**Note**: The `order` field is optional. If not provided, it will be set to the maximum order + 1.

---

### 64. Update Our Fact (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-facts/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the fact ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/our-facts/1`

**JSON Data**:
```json
{
    "percentage": "100%",
    "label": "Updated Satisfaction Rate",
    "order": 2
}
```

---

### 65. Delete Our Fact (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-facts/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the fact ID. This will permanently delete the fact.

**Example**: `http://localhost:8000/api/v1/our-facts/1`

**JSON Data**: None

---

## Our Promise Management Endpoints

### 66. Get Our Promise Content

**Postman URL**: `http://localhost:8000/api/v1/our-promise`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 67. Create or Update Our Promise (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-promise`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new our promise or updates existing one.

**JSON Data**:
```json
{
    "title": "Our Promise",
    "description": "We help you scale your vision and services through thoughtful planning and consultation."
}
```

---

### 68. Update Our Promise (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-promise/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the promise ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/our-promise/1`

**JSON Data**:
```json
{
    "title": "Updated Our Promise",
    "description": "Updated description text"
}
```

---

### 69. Delete Our Promise (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/our-promise/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the promise ID. This will permanently delete the promise.

**Example**: `http://localhost:8000/api/v1/our-promise/1`

**JSON Data**: None

---

## Process Step Management Endpoints

### 70. Get All Process Steps

**Postman URL**: `http://localhost:8000/api/v1/process-steps`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns all steps ordered by the `order` field.

---

### 71. Get Process Step by ID

**Postman URL**: `http://localhost:8000/api/v1/process-steps/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the step ID.

**Example**: `http://localhost:8000/api/v1/process-steps/1`

**JSON Data**: None

---

### 72. Create Process Step (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/process-steps`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**JSON Data**:
```json
{
    "number": 1,
    "title": "Information Collection",
    "description": "We gather comprehensive details about your business needs, objectives, and requirements to create tailored solutions.",
    "order": 1
}
```

**Note**: The `order` field is optional. If not provided, it will be set to the maximum order + 1.

---

### 73. Update Process Step (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/process-steps/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the step ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/process-steps/1`

**JSON Data**:
```json
{
    "number": 2,
    "title": "Updated Title",
    "description": "Updated description text",
    "order": 2
}
```

---

### 74. Delete Process Step (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/process-steps/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the step ID. This will permanently delete the step.

**Example**: `http://localhost:8000/api/v1/process-steps/1`

**JSON Data**: None

---

## Notes

- All endpoints that require authentication expect a Bearer token in the Authorization header.
- Admin endpoints are accessible by users with `super_admin` or `editor` roles only.
- User management endpoints are accessible by `super_admin` role only.
- Public endpoints do not require authentication.
- Image uploads should use `multipart/form-data` content type.
- JSON data endpoints should use `application/json` content type.
- For PUT/PATCH requests with file uploads, you can use POST method with `_method=PUT` or `_method=PATCH` in form data.
- To delete individual images, you can set the image field to `null` or `"delete"` in update requests, or use the dedicated delete field endpoint.

---

## Error Responses

All endpoints follow a consistent error response format:

```json
{
    "success": false,
    "message": "Error message here",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

---

## Reviews Section Management Endpoints

### 75. Get Reviews Section Content

**Postman URL**: `http://localhost:8000/api/v1/reviews-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 76. Create or Update Reviews Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/reviews-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new reviews section or updates existing one.

**Form Data**:
```
main_heading: WHAT OUR CLIENT SAY
average_rating: 4.9
call_to_action_text: Customer experiences that speak for themselves
client_label: Our Client
review_count: 5k+
button_text: Book Now
button_url: https://example.com/book
avatar_1: [file upload - optional]
avatar_2: [file upload - optional]
avatar_3: [file upload - optional]
avatar_4: [file upload - optional]
```

**JSON Alternative (without images)**:
```json
{
    "main_heading": "WHAT OUR CLIENT SAY",
    "average_rating": "4.9",
    "call_to_action_text": "Customer experiences that speak for themselves",
    "client_label": "Our Client",
    "review_count": "5k+",
    "button_text": "Book Now",
    "button_url": "https://example.com/book"
}
```

---

### 77. Update Reviews Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/reviews-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/reviews-section/1`

**Form Data**:
```
main_heading: WHAT OUR CLIENT SAY
average_rating: 4.9
call_to_action_text: Updated text
client_label: Our Client
review_count: 10k+
button_text: Book Now
button_url: https://example.com/book
avatar_1: [file upload - optional]
avatar_2: [file upload - optional]
avatar_3: [file upload - optional]
avatar_4: [file upload - optional]
```

**Deleting Avatars**:
To delete an avatar, set it to `null` or `"delete"`:
```json
{
    "avatar_1": null,
    "avatar_2": "delete"
}
```

---

### 78. Delete Specific Field from Reviews Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/reviews-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. This endpoint allows you to delete a single avatar image without deleting the entire section.

**Example**: `http://localhost:8000/api/v1/reviews-section/1/field/avatar_1`

**Available Fields**: `avatar_1`, `avatar_2`, `avatar_3`, `avatar_4`

**JSON Data**: None

---

### 79. Delete Reviews Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/reviews-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the section ID. This will permanently delete the section and all associated avatar images.

**Example**: `http://localhost:8000/api/v1/reviews-section/1`

**JSON Data**: None

---

## Review Management Endpoints

### 80. Get All Reviews

**Postman URL**: `http://localhost:8000/api/v1/reviews`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns all reviews ordered by the `order` field.

---

### 81. Get Review by ID

**Postman URL**: `http://localhost:8000/api/v1/reviews/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the review ID.

**Example**: `http://localhost:8000/api/v1/reviews/1`

**JSON Data**: None

---

### 82. Create Review (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/reviews`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
```
review_quote: A studio with passionate, professional and full creativity. Much more than I'm expect. Great services, high quality products & affordable prices. I'm extremely satisfied.
avatar: [file upload - optional]
name: Micheal
designation: Designation
rating: 4.0
order: 1
```

**JSON Alternative (without avatar)**:
```json
{
    "review_quote": "A studio with passionate, professional and full creativity.",
    "name": "Micheal",
    "designation": "Designation",
    "rating": 4.0,
    "order": 1
}
```

**Note**: The `order` field is optional. If not provided, it will be set to the maximum order + 1. The `rating` field accepts decimal values from 0 to 5 (e.g., 4.0, 4.5, 5.0).

---

### 83. Update Review (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/reviews/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the review ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/reviews/1`

**Form Data**:
```
review_quote: Updated review quote text
avatar: [file upload - optional]
name: Updated Name
designation: Updated Designation
rating: 4.5
order: 2
```

**Deleting Avatar**:
To delete the avatar, set it to `null` or `"delete"`:
```json
{
    "avatar": null
}
```

---

### 84. Delete Review (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/reviews/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the review ID. This will permanently delete the review and its associated avatar image.

**Example**: `http://localhost:8000/api/v1/reviews/1`

**JSON Data**: None

---

## Portfolio Section Management Endpoints

### 85. Get Portfolio Section Content

**Postman URL**: `http://localhost:8000/api/v1/portfolio-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 86. Create or Update Portfolio Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/portfolio-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new portfolio section or updates existing one.

**Form Data**:
```
main_heading: PORTFOLIO
description: Lorem ipsum dolor sit amet consectetur adipisicing elit...
background_image: [file upload - optional]
```

**JSON Alternative (without images)**:
```json
{
    "main_heading": "PORTFOLIO",
    "description": "Lorem ipsum dolor sit amet consectetur adipisicing elit..."
}
```

---

### 87. Update Portfolio Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/portfolio-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/portfolio-section/1`

**Form Data**:
```
main_heading: PORTFOLIO
description: Updated description text
background_image: [file upload - optional]
```

**Deleting Background Image**:
To delete the background image, set it to `null` or `"delete"`:
```json
{
    "background_image": null
}
```

---

### 88. Delete Specific Field from Portfolio Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/portfolio-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. This endpoint allows you to delete the background image without deleting the entire section.

**Example**: `http://localhost:8000/api/v1/portfolio-section/1/field/background_image`

**Available Fields**: `background_image`

**JSON Data**: None

---

### 89. Delete Portfolio Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/portfolio-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the section ID. This will permanently delete the section and the associated background image.

**Example**: `http://localhost:8000/api/v1/portfolio-section/1`

**JSON Data**: None

---

## Portfolio Item Management Endpoints

### 90. Get All Portfolio Items

**Postman URL**: `http://localhost:8000/api/v1/portfolio-items`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns all portfolio items ordered by the `order` field.

---

### 91. Get Portfolio Item by ID

**Postman URL**: `http://localhost:8000/api/v1/portfolio-items/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the portfolio item ID.

**Example**: `http://localhost:8000/api/v1/portfolio-items/1`

**JSON Data**: None

---

### 92. Create Portfolio Item (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/portfolio-items`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
```
title: Rum Distillery
link: https://example.com
image: [file upload - optional]
order: 1
```

**JSON Alternative (without image)**:
```json
{
    "title": "Rum Distillery",
    "link": "https://example.com",
    "order": 1
}
```

**Note**: The `order` field is optional. If not provided, it will be set to the maximum order + 1. The `link` field is optional and should be a valid URL if provided.

---

### 93. Update Portfolio Item (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/portfolio-items/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the portfolio item ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/portfolio-items/1`

**Form Data**:
```
title: Updated Title
link: https://updated-example.com
image: [file upload - optional]
order: 2
```

**Deleting Image**:
To delete the image, set it to `null` or `"delete"`:
```json
{
    "image": null
}
```

---

### 94. Delete Portfolio Item (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/portfolio-items/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the portfolio item ID. This will permanently delete the portfolio item and its associated image.

**Example**: `http://localhost:8000/api/v1/portfolio-items/1`

**JSON Data**: None

---

## Quote Section Management Endpoints

### 95. Get Quote Section Content

**Postman URL**: `http://localhost:8000/api/v1/quote-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

---

### 96. Create or Update Quote Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/quote-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new quote section or updates existing one.

**Form Data**:
```
request_quote_title: Start Your Project updated
request_quote_subtitle: Get the Signs You Need, at the Right Price
description: We're here to help. Take the first step by sharing a few details about your project...
button_text: Quote Request
title_1: Start Now, Pay Later
paragraph_1: Mecarvi Advantage Credit makes it easier to move forward...
title_2: Start Now, Pay Later
paragraph_2: Mecarvi Advantage Credit makes it easier to move forward...
image_1: [file upload - optional]
image_2: [file upload - optional]
```

**JSON Alternative (without images)**:
```json
{
    "request_quote_title": "Start Your Project updated",
    "request_quote_subtitle": "Get the Signs You Need, at the Right Price",
    "description": "We're here to help. Take the first step by sharing a few details about your project...",
    "button_text": "Quote Request",
    "title_1": "Start Now, Pay Later",
    "paragraph_1": "Mecarvi Advantage Credit makes it easier to move forward...",
    "title_2": "Start Now, Pay Later",
    "paragraph_2": "Mecarvi Advantage Credit makes it easier to move forward..."
}
```

---

### 97. Update Quote Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/quote-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/quote-section/1`

**Form Data**:
```
request_quote_title: Start Your Project
request_quote_subtitle: Get the Signs You Need, at the Right Price
description: Updated description text
button_text: Quote Request
title_1: Start Now, Pay Later
paragraph_1: Updated paragraph text
title_2: Start Now, Pay Later
paragraph_2: Updated paragraph text
image_1: [file upload - optional]
image_2: [file upload - optional]
```

**Deleting Images**:
To delete an image, set it to `null` or `"delete"`:
```json
{
    "image_1": null,
    "image_2": "delete"
}
```

---

### 98. Delete Specific Field from Quote Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/quote-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. This endpoint allows you to delete a single image without deleting the entire section.

**Example**: `http://localhost:8000/api/v1/quote-section/1/field/image_1`

**Available Fields**: `image_1`, `image_2`

**Examples**:
- Delete image_1: `DELETE /api/v1/quote-section/1/field/image_1`
- Delete image_2: `DELETE /api/v1/quote-section/1/field/image_2`

**JSON Data**: None

---

### 99. Delete Quote Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/quote-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the section ID. This will permanently delete the section and all associated images (image_1, image_2).

**Example**: `http://localhost:8000/api/v1/quote-section/1`

**JSON Data**: None

---

## About Page Management Endpoints

**Note**: The About Page has been split into separate sections for better organization. Each section has its own endpoints:

- **Hero Section** - See [Hero Section Management Endpoints](#hero-section-management-endpoints)
- **About the Founder Section** - See [About the Founder Section Management Endpoints](#about-the-founder-section-management-endpoints)
- **About our Company Section** - See [About our Company Section Management Endpoints](#about-our-company-section-management-endpoints)
- **Mission and Vision Section** - See [Mission and Vision Section Management Endpoints](#mission-and-vision-section-management-endpoints)
- **Core Values** - See [Core Value Management Endpoints](#core-value-management-endpoints)

Each section can be managed independently through its dedicated endpoints.

---

## Hero Section Management Endpoints

### 106. Get Hero Section Content

**Postman URL**: `http://localhost:8000/api/v1/hero-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns the current hero section configuration.

---

### 107. Get Hero Section by ID

**Postman URL**: `http://localhost:8000/api/v1/hero-section/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the hero section ID.

**Example**: `http://localhost:8000/api/v1/hero-section/1`

**JSON Data**: None

---

### 108. Create or Update Hero Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/hero-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new hero section or updates existing one.

**Form Data**:
```
hero_background_image: [file upload - optional]
title_part_1: About Mecarvi
title_part_2: Technologies
description_1: Leading the industry with innovation, quality, and exceptional service since 1989.
description_2: We create exceptional signage solutions that help businesses stand out. With over 35 years of experience, we combine cutting-edge technology with traditional craftsmanship to deliver results that exceed expectations.
hero_image: [file upload - optional]
```

**JSON Alternative (without images)**:
```json
{
    "title_part_1": "About Mecarvi",
    "title_part_2": "Technologies",
    "description_1": "Leading the industry with innovation, quality, and exceptional service since 1989.",
    "description_2": "We create exceptional signage solutions that help businesses stand out. With over 35 years of experience, we combine cutting-edge technology with traditional craftsmanship to deliver results that exceed expectations."
}
```

---

### 109. Update Hero Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/hero-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the hero section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/hero-section/1`

**Form Data**:
```
hero_background_image: [file upload - optional]
title_part_1: Updated Title Part 1
title_part_2: Updated Title Part 2
description_1: Updated description 1
description_2: Updated description 2
hero_image: [file upload - optional]
```

**JSON Alternative (without images)**:
```json
{
    "title_part_1": "Updated About Mecarvi",
    "title_part_2": "Updated Technologies",
    "description_1": "Updated leading the industry with innovation...",
    "description_2": "Updated we create exceptional signage solutions..."
}
```

**Deleting Images**:
To delete an image, set it to `null` or `"delete"`:
```json
{
    "hero_background_image": null,
    "hero_image": "delete"
}
```

---

### 110. Delete Specific Field from Hero Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/hero-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. This endpoint allows you to delete a single field without deleting the entire section.

**Example**: `http://localhost:8000/api/v1/hero-section/1/field/hero_background_image`

**Available Fields**: 
- `hero_background_image`
- `title_part_1`
- `title_part_2`
- `description_1`
- `description_2`
- `hero_image`

**Examples**:
- Delete hero_background_image: `DELETE /api/v1/hero-section/1/field/hero_background_image`
- Delete hero_image: `DELETE /api/v1/hero-section/1/field/hero_image`
- Delete title_part_1: `DELETE /api/v1/hero-section/1/field/title_part_1`

**JSON Data**: None

---

### 111. Delete Hero Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/hero-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the hero section ID. This will permanently delete the hero section and all associated images.

**Example**: `http://localhost:8000/api/v1/hero-section/1`

**JSON Data**: None

---

## About the Founder Section Management Endpoints

### 112. Get About Founder Section Content

**Postman URL**: `http://localhost:8000/api/v1/about-founder-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns the current about founder section configuration.

---

### 113. Get About Founder Section by ID

**Postman URL**: `http://localhost:8000/api/v1/about-founder-section/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the about founder section ID.

**Example**: `http://localhost:8000/api/v1/about-founder-section/1`

**JSON Data**: None

---

### 114. Create or Update About Founder Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-founder-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new about founder section or updates existing one.

**JSON Data**:
```json
{
    "founder_title": "About the Founder",
    "founder_description": "The first foundations were laid in 2012 by Kera Vazquez along with and her friends. She is the brain behind the name and possesses 17 years of industry's experience. Kera had a vision of producing effective, compelling, targeted and accountable solutions. She built relationships and focused on customer satisfaction, which led to the company's growth and success."
}
```

---

### 115. Update About Founder Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-founder-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the about founder section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/about-founder-section/1`

**JSON Data**:
```json
{
    "founder_title": "Updated About the Founder",
    "founder_description": "Updated description about the founder..."
}
```

---

### 116. Delete Specific Field from About Founder Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-founder-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. This endpoint allows you to delete a single field without deleting the entire section.

**Example**: `http://localhost:8000/api/v1/about-founder-section/1/field/founder_title`

**Available Fields**: 
- `founder_title`
- `founder_description`

**Examples**:
- Delete founder_title: `DELETE /api/v1/about-founder-section/1/field/founder_title`
- Delete founder_description: `DELETE /api/v1/about-founder-section/1/field/founder_description`

**JSON Data**: None

---

### 117. Delete About Founder Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-founder-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the about founder section ID. This will permanently delete the section.

**Example**: `http://localhost:8000/api/v1/about-founder-section/1`

**JSON Data**: None

---

## About our Company Section Management Endpoints

### 118. Get About Company Section Content

**Postman URL**: `http://localhost:8000/api/v1/about-company-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns the current about company section configuration.

---

### 119. Get About Company Section by ID

**Postman URL**: `http://localhost:8000/api/v1/about-company-section/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the about company section ID.

**Example**: `http://localhost:8000/api/v1/about-company-section/1`

**JSON Data**: None

---

### 120. Create or Update About Company Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-company-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new about company section or updates existing one.

**Form Data**:
```
company_title: About our Company
company_description: Mecarvi Prints is an industry leading printing company that offers a comprehensive range of integrated advertising and marketing solutions, with an aim of providing unparalleled print & digital services. From R&G Multimedia Enterprise to Mecarvi Holding Corporation, we've grown to serve clients across the USA, Canada, and the Caribbean. With warehouses in Texas, California, and Georgia, and a team of experts using state-of-the-art technology, we've delivered over a million products and continue to create clever solutions that deliver relevant and innovative results.
company_image: [file upload - optional]
```

**JSON Alternative (without image)**:
```json
{
    "company_title": "About our Company",
    "company_description": "Mecarvi Prints is an industry leading printing company that offers a comprehensive range of integrated advertising and marketing solutions, with an aim of providing unparalleled print & digital services. From R&G Multimedia Enterprise to Mecarvi Holding Corporation, we've grown to serve clients across the USA, Canada, and the Caribbean. With warehouses in Texas, California, and Georgia, and a team of experts using state-of-the-art technology, we've delivered over a million products and continue to create clever solutions that deliver relevant and innovative results."
}
```

---

### 121. Update About Company Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-company-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the about company section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/about-company-section/1`

**Form Data**:
```
company_title: Updated About our Company
company_description: Updated company description...
company_image: [file upload - optional]
```

**JSON Alternative (without image)**:
```json
{
    "company_title": "Updated About our Company",
    "company_description": "Updated company description text..."
}
```

**Deleting Company Image**:
To delete the company image, set it to `null` or `"delete"`:
```json
{
    "company_image": null
}
```

---

### 122. Delete Specific Field from About Company Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-company-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. This endpoint allows you to delete a single field without deleting the entire section.

**Example**: `http://localhost:8000/api/v1/about-company-section/1/field/company_image`

**Available Fields**: 
- `company_title`
- `company_description`
- `company_image`

**Examples**:
- Delete company_image: `DELETE /api/v1/about-company-section/1/field/company_image`
- Delete company_title: `DELETE /api/v1/about-company-section/1/field/company_title`
- Delete company_description: `DELETE /api/v1/about-company-section/1/field/company_description`

**JSON Data**: None

---

### 123. Delete About Company Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/about-company-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the about company section ID. This will permanently delete the section and the associated company image.

**Example**: `http://localhost:8000/api/v1/about-company-section/1`

**JSON Data**: None

---

## Mission and Vision Section Management Endpoints

### 124. Get Mission and Vision Section Content

**Postman URL**: `http://localhost:8000/api/v1/mission-vision-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns the current mission and vision section configuration.

---

### 125. Get Mission and Vision Section by ID

**Postman URL**: `http://localhost:8000/api/v1/mission-vision-section/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the mission and vision section ID.

**Example**: `http://localhost:8000/api/v1/mission-vision-section/1`

**JSON Data**: None

---

### 126. Create or Update Mission and Vision Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/mission-vision-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new mission and vision section or updates existing one.

**JSON Data**:
```json
{
    "mission_title": "Mission Statement",
    "vision_title": "Vision Statement",
    "mission_description": "Our mission is to serve as our client's most trusted indispensable partner We work in close liaison with customers to empower their brand value, fuel their growth and achieve their goals by providing fast innovative solutions that will lead to unprecedented results.",
    "vision_description": "Our vision is to be the leading provider of innovative signage and marketing solutions, recognized for excellence, quality, and customer satisfaction. We strive to continuously evolve and adapt to meet the changing needs of our clients while maintaining our commitment to traditional craftsmanship and cutting-edge technology."
}
```

---

### 127. Update Mission and Vision Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/mission-vision-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the mission and vision section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/mission-vision-section/1`

**JSON Data**:
```json
{
    "mission_title": "Updated Mission Statement",
    "vision_title": "Updated Vision Statement",
    "mission_description": "Updated mission description text...",
    "vision_description": "Updated vision description text..."
}
```

---

### 128. Delete Specific Field from Mission and Vision Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/mission-vision-section/{id}/field/{field}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. This endpoint allows you to delete a single field without deleting the entire section.

**Example**: `http://localhost:8000/api/v1/mission-vision-section/1/field/mission_title`

**Available Fields**: 
- `mission_title`
- `vision_title`
- `mission_description`
- `vision_description`

**Examples**:
- Delete mission_title: `DELETE /api/v1/mission-vision-section/1/field/mission_title`
- Delete vision_description: `DELETE /api/v1/mission-vision-section/1/field/vision_description`
- Delete mission_description: `DELETE /api/v1/mission-vision-section/1/field/mission_description`

**JSON Data**: None

---

### 129. Delete Mission and Vision Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/mission-vision-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the mission and vision section ID. This will permanently delete the section.

**Example**: `http://localhost:8000/api/v1/mission-vision-section/1`

**JSON Data**: None

---

## Core Value Management Endpoints

### 130. Get All Core Values

**Postman URL**: `http://localhost:8000/api/v1/core-values`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns the core values section (section title and description) and all core values ordered by the `order` field. The response includes `core_values_section` (may be `null` if not configured) and `core_values` (array of items).

---

### 131. Get Core Value by ID

**Postman URL**: `http://localhost:8000/api/v1/core-values/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the core value ID.

**Example**: `http://localhost:8000/api/v1/core-values/1`

**JSON Data**: None

---

### 132. Create Core Value (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/core-values`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles.

**Form Data**:
```
icon: [file upload - optional]
title: Innovation
description: Lorem ipsum dolor sit amet, consectetur adipiscing elit...
order: 1
```

**JSON Alternative (without icon)**:
```json
{
    "title": "Innovation",
    "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit...",
    "order": 1
}
```

**Note**: The `order` field is optional. If not provided, it will be set to the maximum order + 1. The `icon` field accepts image files (jpeg, png, jpg, gif, webp, svg) up to 2MB.

---

### 133. Update Core Value (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/core-values/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the core value ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/core-values/1`

**Form Data**:
```
icon: [file upload - optional]
title: Updated Innovation
description: Updated description text
order: 2
```

**Deleting Icon**:
To delete the icon, set it to `null` or `"delete"`:
```json
{
    "icon": null
}
```

---

### 134. Delete Core Value (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/core-values/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the core value ID. This will permanently delete the core value and its associated icon image.

**Example**: `http://localhost:8000/api/v1/core-values/1`

**JSON Data**: None

---

## Core Values Section Management Endpoints

The Core Values Section holds the section-level title and description displayed above the list of core values on the About Us page. Use these endpoints to get or update that section content.

### 135. Get Core Values Section

**Postman URL**: `http://localhost:8000/api/v1/core-values-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns the current core values section configuration (`section_title`, `section_description`). Returns `core_values_section: null` with a message if not configured yet.

---

### 136. Get Core Values Section by ID

**Postman URL**: `http://localhost:8000/api/v1/core-values-section/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the core values section ID.

**Example**: `http://localhost:8000/api/v1/core-values-section/1`

**JSON Data**: None

---

### 137. Create or Update Core Values Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/core-values-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates the section if none exists, or updates the existing one.

**JSON Data**:
```json
{
    "section_title": "Our Core Values",
    "section_description": "These principles guide everything we do."
}
```

**Response**: Returns the created or updated core values section with `id`, `section_title`, `section_description`, and `updated_at`.

---

### 138. Update Core Values Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/core-values-section/{id}`

**Method**: `PUT` or `PATCH`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the core values section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/core-values-section/1`

**JSON Data**:
```json
{
    "section_title": "Our Core Values",
    "section_description": "Updated section description."
}
```

---

## FAQ Hero Section Management Endpoints

### 139. Get FAQ Hero Section Content

**Postman URL**: `http://localhost:8000/api/v1/faq-hero-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns the current FAQ hero section configuration.

---

### 140. Get FAQ Hero Section by ID

**Postman URL**: `http://localhost:8000/api/v1/faq-hero-section/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the FAQ hero section ID.

**Example**: `http://localhost:8000/api/v1/faq-hero-section/1`

**JSON Data**: None

---

### 141. Create or Update FAQ Hero Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-hero-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new FAQ hero section or updates existing one.

**JSON Data**:
```json
{
    "hero_title": "Frequently Asked Question"
}
```

---

### 142. Update FAQ Hero Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-hero-section/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the FAQ hero section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/faq-hero-section/1`

**JSON Data**:
```json
{
    "hero_title": "Updated FAQ Title"
}
```

---

### 143. Delete FAQ Hero Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-hero-section/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the FAQ hero section ID. This will permanently delete the section.

**Example**: `http://localhost:8000/api/v1/faq-hero-section/1`

**JSON Data**: None

---

## FAQ Intro Paragraph Management Endpoints

### 144. Get FAQ Intro Paragraph Content

**Postman URL**: `http://localhost:8000/api/v1/faq-intro-paragraph`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns the current FAQ intro paragraph configuration.

---

### 145. Get FAQ Intro Paragraph by ID

**Postman URL**: `http://localhost:8000/api/v1/faq-intro-paragraph/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the FAQ intro paragraph ID.

**Example**: `http://localhost:8000/api/v1/faq-intro-paragraph/1`

**JSON Data**: None

---

### 146. Create or Update FAQ Intro Paragraph (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-intro-paragraph`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates new FAQ intro paragraph or updates existing one.

**JSON Data**:
```json
{
    "paragraph_text": "Find answers to commonly asked questions about our services, products, and processes. If you can't find what you're looking for, feel free to ask us a question using the form below."
}
```

---

### 147. Update FAQ Intro Paragraph (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-intro-paragraph/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the FAQ intro paragraph ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/faq-intro-paragraph/1`

**JSON Data**:
```json
{
    "paragraph_text": "Updated intro paragraph text..."
}
```

---

### 148. Delete FAQ Intro Paragraph (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-intro-paragraph/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the FAQ intro paragraph ID. This will permanently delete the section.

**Example**: `http://localhost:8000/api/v1/faq-intro-paragraph/1`

**JSON Data**: None

---

## FAQ Category Management Endpoints

### 149. Get All FAQ Categories

**Postman URL**: `http://localhost:8000/api/v1/faq-categories`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns all FAQ categories ordered by the `order` field, including the count of FAQ items in each category.

---

### 150. Get FAQ Category by ID

**Postman URL**: `http://localhost:8000/api/v1/faq-categories/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the FAQ category ID.

**Example**: `http://localhost:8000/api/v1/faq-categories/1`

**JSON Data**: None

**Response**: Returns the FAQ category with all its associated FAQ items.

---

### 151. Create FAQ Category (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-categories`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. **Important**: Categories must be created first before creating FAQ items.

**JSON Data**:
```json
{
    "category_name": "Inquery",
    "order": 1
}
```

**Note**: The `order` field is optional. If not provided, it will be set to the maximum order + 1.

---

### 152. Update FAQ Category (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-categories/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the FAQ category ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/faq-categories/1`

**JSON Data**:
```json
{
    "category_name": "Updated Category Name",
    "order": 2
}
```

---

### 153. Delete FAQ Category (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-categories/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the FAQ category ID. This will permanently delete the category and all its associated FAQ items (cascade delete).

**Example**: `http://localhost:8000/api/v1/faq-categories/1`

**JSON Data**: None

---

## FAQ Item Management Endpoints

### 154. Get All FAQ Items

**Postman URL**: `http://localhost:8000/api/v1/faq-items`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Query Parameters**:
- `faq_category_id` (optional) - Filter FAQ items by category ID

**Example**: `http://localhost:8000/api/v1/faq-items?faq_category_id=1`

**JSON Data**: None

**Response**: Returns all FAQ items ordered by the `order` field. If `faq_category_id` is provided, returns only items for that category.

---

### 155. Get FAQ Item by ID

**Postman URL**: `http://localhost:8000/api/v1/faq-items/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the FAQ item ID.

**Example**: `http://localhost:8000/api/v1/faq-items/1`

**JSON Data**: None

---

### 156. Create FAQ Item (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-items`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. **Important**: FAQ categories must be created first. The `faq_category_id` is required.

**JSON Data**:
```json
{
    "faq_category_id": 1,
    "question": "What is Flowbite?",
    "answer": "Flowbite is a collection of utility-first CSS components built with Tailwind CSS that you can use to build faster custom layouts and components.",
    "order": 1
}
```

**Note**: The `order` field is optional. If not provided, it will be set to the maximum order + 1 within that category.

---

### 157. Update FAQ Item (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-items/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the FAQ item ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/faq-items/1`

**JSON Data**:
```json
{
    "faq_category_id": 2,
    "question": "Updated question text?",
    "answer": "Updated answer text...",
    "order": 2
}
```

**Note**: You can change the category of an FAQ item by updating the `faq_category_id`.

---

### 158. Delete FAQ Item (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-items/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the FAQ item ID. This will permanently delete the FAQ item.

**Example**: `http://localhost:8000/api/v1/faq-items/1`

**JSON Data**: None

---

## FAQ Ask Question Section Management Endpoints

The FAQ "Ask Question" form section provides the **heading** and **description** displayed above the form (e.g. "Didn't Get Your Answer" and the descriptive paragraph). The form itself submits to the User Submitted Questions endpoint and includes **name**, **email**, and **question_message**.

### 159. Get FAQ Ask Question Section (Public)

**Postman URL**: `http://localhost:8000/api/v1/faq-ask-question-section`

**Method**: `GET`

**Headers**: None required (public endpoint)

**JSON Data**: None

**Response**: Returns the ask question form section content (`heading`, `description`). Returns `faq_ask_question_section: null` if not configured yet.

---

### 160. Get FAQ Ask Question Section by ID (Public)

**Postman URL**: `http://localhost:8000/api/v1/faq-ask-question-section/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the section ID.

**Example**: `http://localhost:8000/api/v1/faq-ask-question-section/1`

**JSON Data**: None

---

### 161. Create or Update FAQ Ask Question Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-ask-question-section`

**Method**: `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Creates the section if none exists, or updates the existing one.

**JSON Data**:
```json
{
    "heading": "Didn't Get Your Answer",
    "description": "Lorem ipsum dolor sit amet consectetur adipisicing elit. Praesentium hic consectetur provident, saepe eaque reprehenderit possimus unde porro, doloremque optio odit et rem id quos sunt enim! Dolore, a possimus."
}
```

**Response**: Returns the created or updated section with `id`, `heading`, `description`, and `updated_at`.

---

### 162. Update FAQ Ask Question Section (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/faq-ask-question-section/{id}`

**Method**: `PUT` or `PATCH`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the section ID. All fields are optional.

**Example**: `http://localhost:8000/api/v1/faq-ask-question-section/1`

**JSON Data**:
```json
{
    "heading": "Didn't Get Your Answer",
    "description": "Updated description text."
}
```

---

## User Submitted Questions Management Endpoints

### 163. Get All User Submitted Questions

**Postman URL**: `http://localhost:8000/api/v1/user-submitted-questions`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Query Parameters**:
- `status` (optional) - Filter by status: `pending`, `answered`, or `dismissed`

**Example**: `http://localhost:8000/api/v1/user-submitted-questions?status=pending`

**JSON Data**: None

**Response**: Returns all user submitted questions ordered by created_at (newest first). If `status` is provided, returns only questions with that status.

---

### 164. Get User Submitted Question by ID

**Postman URL**: `http://localhost:8000/api/v1/user-submitted-questions/{id}`

**Method**: `GET`

**Headers**: None required (public endpoint)

**Note**: Replace `{id}` with the user submitted question ID.

**Example**: `http://localhost:8000/api/v1/user-submitted-questions/1`

**JSON Data**: None

---

### 165. Submit User Question (Public)

**Postman URL**: `http://localhost:8000/api/v1/user-submitted-questions`

**Method**: `POST`

**Headers**:
```
Content-Type: application/json
```

**Note**: This is a public endpoint. No authentication required. Users can submit questions from the website form.

**Request Body Parameters**:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Submitter's full name (max 255 characters) |
| `email` | string | Yes | Submitter's email address (valid email, max 255 characters) |
| `question_message` | string | Yes | The question or message text |

**JSON Data**:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "question_message": "I have a question about your services. Can you help me?"
}
```

**Note**: The `name`, `email`, and `question_message` fields are required. The question will be created with `status: "pending"` by default. To display the form heading and description (e.g. "Didn't Get Your Answer") above the form, use `GET /api/v1/faq-ask-question-section`.

---

### 166. Update User Submitted Question Status (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/user-submitted-questions/{id}`

**Method**: `PUT`, `PATCH`, or `POST`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the user submitted question ID. Used to update the status of submitted questions.

**Example**: `http://localhost:8000/api/v1/user-submitted-questions/1`

**JSON Data**:
```json
{
    "status": "answered"
}
```

**Available Status Values**:
- `pending` - Question is pending review
- `answered` - Question has been answered
- `dismissed` - Question has been dismissed

**Note**: You can also update `name`, `email`, and `question_message` if needed.

---

### 167. Delete User Submitted Question (Admin Only)

**Postman URL**: `http://localhost:8000/api/v1/user-submitted-questions/{id}`

**Method**: `DELETE`

**Headers**:
```
Authorization: Bearer {token}
```

**Note**: Only accessible by `super_admin` or `editor` roles. Replace `{id}` with the user submitted question ID. This will permanently delete the question.

**Example**: `http://localhost:8000/api/v1/user-submitted-questions/1`

**JSON Data**: None

---

## Footer Management Endpoints

### Get Footer Content (Public)

**Endpoint**: `GET /api/v1/footer`

**Description**: Returns the full footer configuration: contact info, company links, policy center links, our brands links, social URLs, payment methods, and copyright text.

**Authentication**: Not required (public endpoint)

**Example**: `http://localhost:8000/api/v1/footer`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "footer": {
            "contact_info": {
                "section_heading": "CONTACT INFO",
                "phone": "(877) 853-3484",
                "email": "contact@mecarviprints.com",
                "hours_mon_fri": "Mon - Fri: 8am - 8pm",
                "hours_sat": "Sat: 10am-6pm",
                "hours_sun_holidays": "Sun & Public Holidays: CLOSED",
                "chat_title": "Chat With Us",
                "chat_subtitle": "24/7 Customer Support"
            },
            "company": {
                "section_heading": "COMPANY",
                "links": [
                    { "id": 1, "text": "About Us", "path": "/about", "sort_order": 0 },
                    { "id": 2, "text": "Careers", "path": "/careers", "sort_order": 1 }
                ]
            },
            "policy_center": {
                "section_heading": "POLICY CENTER",
                "links": [
                    { "id": 1, "text": "Shipping Policy", "path": "/shipping-policy", "sort_order": 0 }
                ]
            },
            "our_brands": {
                "section_heading": "OUR BRANDS",
                "links": [
                    { "id": 1, "text": "Mecarvi Signs", "path": "/brands/mecarvi-signs", "sort_order": 0 }
                ]
            },
            "social_links": {
                "section_heading": "SOCIAL LINKS",
                "facebook_url": null,
                "twitter_url": null,
                "instagram_url": null,
                "linkedin_url": null,
                "tiktok_url": null
            },
            "payment_methods": {
                "section_heading": "PAYMENT METHODS",
                "items": [
                    { "id": 1, "name": "Visa", "image_url": null, "is_enabled": true, "sort_order": 0 },
                    { "id": 2, "name": "Mastercard", "image_url": null, "is_enabled": true, "sort_order": 1 }
                ]
            },
            "copyright_text": "Copyright © 2015-2025 by Mecarvi Holdings Group. All Rights Reserved."
        }
    }
}
```

### Save Footer (Admin Only)

**Endpoint**: `POST /api/v1/footer` or `PUT /api/v1/footer`

**Description**: Create or update the full footer. Send the same structure as the GET response. Sections and link arrays are optional; only provided keys are updated.

**Authentication**: Required (Bearer token). Super admin or editor only.

**Example**: `http://localhost:8000/api/v1/footer`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**: Same structure as GET response. Use `contact_info`, `company` (with `section_heading` and `links` array of `{ "text", "path", "sort_order" }`), `policy_center`, `our_brands`, `social_links` (URLs and `section_heading`), `payment_methods` (with `items` array of `{ "name", "image_url", "is_enabled", "sort_order" }`), and `copyright_text`. Empty `image_url` uses default icon. Link items use `text` (or `label`) and `path`.

**JSON Data**:
```json
{
    "contact_info": {
        "section_heading": "CONTACT INFO",
        "phone": "(877) 853-3484",
        "email": "contact@mecarviprints.com",
        "hours_mon_fri": "Mon - Fri: 8am - 8pm",
        "hours_sat": "Sat: 10am-6pm",
        "hours_sun_holidays": "Sun & Public Holidays: CLOSED",
        "chat_title": "Chat With Us",
        "chat_subtitle": "24/7 Customer Support"
    },
    "company": {
        "section_heading": "COMPANY",
        "links": [
            { "text": "About Us", "path": "/about" },
            { "text": "Careers", "path": "/careers" }
        ]
    },
    "policy_center": { "section_heading": "POLICY CENTER", "links": [] },
    "our_brands": { "section_heading": "OUR BRANDS", "links": [] },
    "social_links": {
        "section_heading": "SOCIAL LINKS",
        "facebook_url": "",
        "twitter_url": "",
        "instagram_url": "",
        "linkedin_url": "",
        "tiktok_url": ""
    },
    "payment_methods": {
        "section_heading": "PAYMENT METHODS",
        "items": [
            { "name": "Visa", "image_url": null, "is_enabled": true, "sort_order": 0 },
            { "name": "Mastercard", "image_url": null, "is_enabled": true, "sort_order": 1 }
        ]
    },
    "copyright_text": "Copyright © 2015-2025 by Mecarvi Holdings Group. All Rights Reserved."
}
```

**Success Response (200)**: Returns the same structure as GET with the saved footer data.

### Update Footer by ID (Admin Only)

**Endpoint**: `PUT /api/v1/footer/{id}` or `PATCH /api/v1/footer/{id}`

**Description**: Update footer content by ID. Same request body as Save Footer; only provided sections are updated.

**Authentication**: Required (Bearer token). Super admin or editor only.

**Example**: `http://localhost:8000/api/v1/footer/1`

**Headers**: `Authorization: Bearer {token}`, `Content-Type: application/json`

**Request Body**: Same as Save Footer (partial payload allowed).

### Delete Footer (Admin Only)

**Endpoint**: `DELETE /api/v1/footer/{id}`

**Description**: Deletes the footer content row and all footer links and payment methods.

**Authentication**: Required (Bearer token). Super admin or editor only.

**Example**: `http://localhost:8000/api/v1/footer/1`

**JSON Data**: None

### Delete Single Field from Footer (Admin Only)

**Endpoint**: `DELETE /api/v1/footer/{id}/field/{field}`

**Description**: Clears a single field on the footer content row (sets it to null).

**Authentication**: Required (Bearer token). Super admin or editor only.

**Example**: `http://localhost:8000/api/v1/footer/1/field/phone`

**Allowed field names**: `contact_section_heading`, `phone`, `email`, `hours_mon_fri`, `hours_sat`, `hours_sun_holidays`, `chat_title`, `chat_subtitle`, `company_section_heading`, `policy_center_section_heading`, `our_brands_section_heading`, `social_links_section_heading`, `facebook_url`, `twitter_url`, `instagram_url`, `linkedin_url`, `tiktok_url`, `payment_methods_section_heading`, `copyright_text`

### Delete Single Footer Link (Admin Only)

**Endpoint**: `DELETE /api/v1/footer/links/{id}`

**Description**: Deletes one footer link (company, policy center, or our brands link by ID).

**Authentication**: Required (Bearer token). Super admin or editor only.

**Example**: `http://localhost:8000/api/v1/footer/links/5`

### Delete Single Footer Payment Method (Admin Only)

**Endpoint**: `DELETE /api/v1/footer/payment-methods/{id}`

**Description**: Deletes one payment method by ID.

**Authentication**: Required (Bearer token). Super admin or editor only.

**Example**: `http://localhost:8000/api/v1/footer/payment-methods/3`

---

## Site Settings Management Endpoints

### Get Site Settings (Public)

**Endpoint**: `GET /api/v1/site-settings`

**Description**: Returns site settings: SEO site title, logo (path and full URL), favicon (path and full URL), button (name and url), and header links (label, url, show_in_header, sort_order).

**Authentication**: Not required (public endpoint)

**Example**: `http://localhost:8000/api/v1/site-settings`

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "site_settings": {
            "seo_site_title": "Mecarvi Technologies - Welcome to Our Mecarvi Signs",
            "logo": "/assets/images/logo.webp",
            "logo_url": "/assets/images/logo.webp",
            "favicon": "/assets/images/favicon.ico",
            "favicon_url": "/assets/images/favicon.ico",
            "button": { "name": "Contact Us", "url": "/contact" },
            "header_links": [
                { "id": 1, "label": "Home", "url": "/", "show_in_header": true, "sort_order": 0 },
                { "id": 2, "label": "About us", "url": "/website/pages/about", "show_in_header": true, "sort_order": 1 }
            ]
        }
    }
}
```

### Save Site Settings (Admin Only)

**Endpoint**: `POST /api/v1/site-settings` or `PUT /api/v1/site-settings`

**Description**: Create or update site settings. Send `seo_site_title`, `logo`, `logo_file`, `favicon`, `favicon_file`, `button` (object with `name` and `url`) or `button_name` and `button_url`, and optionally `header_links` array. Set `logo` or `favicon` to empty or null to clear. Header links can include `label`, `url`, `show_in_header`, `sort_order`.

**Authentication**: Required (Bearer token). Super admin or editor only.

**Example**: `http://localhost:8000/api/v1/site-settings`

**Request Body (JSON)**:
```json
{
    "seo_site_title": "Mecarvi Technologies - Welcome to Our Mecarvi Signs",
    "logo": "/assets/images/logo.webp",
    "favicon": "/assets/images/favicon.ico",
    "button": { "name": "Contact Us", "url": "/contact" },
    "header_links": [
        { "label": "Home", "url": "/", "show_in_header": true, "sort_order": 0 },
        { "label": "About us", "url": "/website/pages/about", "show_in_header": true, "sort_order": 1 },
        { "label": "Contact", "url": "/website/pages/contact", "show_in_header": true, "sort_order": 8 }
    ]
}
```

**Form Data (alternative)**: `seo_site_title`, `logo` (string), `logo_file` (image file), `favicon` (string), `favicon_file` (image file), `header_links` (JSON string or form array). Logo and favicon accept same formats: jpeg, png, gif, webp, svg, ico, bmp, tiff (max 10MB).

### Update Site Settings by ID (Admin Only)

**Endpoint**: `PUT /api/v1/site-settings/{id}` or `PATCH /api/v1/site-settings/{id}`

**Description**: Update site settings by ID. Same body as Save; only provided fields are updated.

**Example**: `http://localhost:8000/api/v1/site-settings/1`

### Delete Site Settings (Admin Only)

**Endpoint**: `DELETE /api/v1/site-settings/{id}`

**Description**: Deletes the site settings row and all header links. Uploaded logo and favicon files are removed from storage.

**Example**: `http://localhost:8000/api/v1/site-settings/1`

### Delete Single Field from Site Settings (Admin Only)

**Endpoint**: `DELETE /api/v1/site-settings/{id}/field/{field}`

**Description**: Clears a single field (sets to null). Allowed fields: `seo_site_title`, `logo`, `favicon`, `button_name`, `button_url`. Use this to "Reset to default" for logo or favicon.

**Example**: `http://localhost:8000/api/v1/site-settings/1/field/logo` or `.../field/favicon` or `.../field/button_name`

### Delete Single Header Link (Admin Only)

**Endpoint**: `DELETE /api/v1/site-settings/links/{id}`

**Description**: Deletes one header link by ID (e.g. "Hide" in admin UI).

**Example**: `http://localhost:8000/api/v1/site-settings/links/5`

<!-- carrer page start -->

---

## Career Page Hero Section Management Endpoints

### Get Career Page Hero Section Content

**Endpoint**: `GET /api/v1/career-page-hero-section`
    
**Description**: Retrieves the current career page hero section configuration.

**Authentication**: Not required (public endpoint)

**Example**: `http://localhost:8000/api/v1/career-page-hero-section`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "career_page_hero_section": {
            "id": 1,
            "image": "http://localhost:8000/storage/career-page-hero-section/image_1642123456.jpg",
            "title": "Build Your Career With Us",
            "subtitle": "Join our team and grow professionally",
            "heading": "Career Opportunities",
            "description": "Explore exciting career opportunities and join our dynamic team.",
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

### Get Career Page Hero Section by ID

**Endpoint**: `GET /api/v1/career-page-hero-section/{id}`

**Description**: Retrieves a specific career page hero section by ID.

**Authentication**: Not required (public endpoint)

**Example**: `http://localhost:8000/api/v1/career-page-hero-section/1`

**JSON Data**: None

### Create or Update Career Page Hero Section

**Endpoint**: `POST /api/v1/career-page-hero-section`

**Description**: Creates a new career page hero section if none exists, or updates the existing one.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/career-page-hero-section`

**Form Data**:
```
image: [file] (optional, jpeg, png, jpg, gif, webp, max 5MB)
title: "Build Your Career With Us" (optional, string, max 255 chars)
subtitle: "Join our team and grow professionally" (optional, string)
heading: "Career Opportunities" (optional, string, max 255 chars)
description: "Explore exciting career opportunities..." (optional, string)
```

**JSON Alternative**:
```json
{
    "title": "Build Your Career With Us",
    "subtitle": "Join our team and grow professionally",
    "heading": "Career Opportunities",
    "description": "Explore exciting career opportunities..."
}
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Career page hero section updated successfully",
    "data": {
        "career_page_hero_section": {
            "id": 1,
            "image": "http://localhost:8000/storage/career-page-hero-section/image_1642123456.jpg",
            "title": "Build Your Career With Us",
            "subtitle": "Join our team and grow professionally",
            "heading": "Career Opportunities",
            "description": "Explore exciting career opportunities and join our dynamic team.",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

### Update Career Page Hero Section

**Endpoint**: `PUT /api/v1/career-page-hero-section/{id}` or `PATCH /api/v1/career-page-hero-section/{id}`

**Description**: Updates the existing career page hero section configuration.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/career-page-hero-section/1`

**Form Data**:
```
image: [file] (optional, jpeg, png, jpg, gif, webp, max 5MB)
title: "Updated Career Title" (optional, string, max 255 chars)
subtitle: "Updated subtitle" (optional, string)
heading: "New Heading" (optional, string, max 255 chars)
description: "Updated description" (optional, string)
```

**JSON Alternative**:
```json
{
    "title": "Updated Career Title",
    "subtitle": "Updated subtitle",
    "heading": "New Heading",
    "description": "Updated description"
}
```

**Delete Image**: To delete an image, send the field with value `delete` or `null`

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Career page hero section updated successfully",
    "data": {
        "career_page_hero_section": {
            "id": 1,
            "image": "http://localhost:8000/storage/career-page-hero-section/new_image_1642123456.jpg",
            "title": "Updated Career Title",
            "subtitle": "Updated subtitle",
            "heading": "New Heading",
            "description": "Updated description",
            "updated_at": "2024-01-15T11:00:00.000000Z"
        }
    }
}
```

### Delete Career Page Hero Section

**Endpoint**: `DELETE /api/v1/career-page-hero-section/{id}`

**Description**: Deletes the career page hero section and associated images.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/career-page-hero-section/1`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Career page hero section deleted successfully"
}
```

### Delete Specific Field from Career Page Hero Section

**Endpoint**: `DELETE /api/v1/career-page-hero-section/{id}/field/{field}`

**Description**: Deletes a specific field from the career page hero section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Available Fields**: `image`, `title`, `subtitle`, `heading`, `description`

**Example**: `http://localhost:8000/api/v1/career-page-hero-section/1/field/image`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Image deleted successfully",
    "data": {
        "career_page_hero_section": {
            "id": 1,
            "image": null,
            "title": "Build Your Career With Us",
            "subtitle": "Join our team and grow professionally",
            "heading": "Career Opportunities",
            "description": "Explore exciting career opportunities and join our dynamic team.",
            "updated_at": "2024-01-15T11:15:00.000000Z"
        }
    }
}
```
<!-- career cards start -->

---

## Career Cards Management Endpoints

### Get Career Cards

**Endpoint**: `GET /api/v1/career-cards`

**Description**: Retrieves all active career cards ordered by sort order.

**Authentication**: Not required (public endpoint)

**Example**: `http://localhost:8000/api/v1/career-cards`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "career_cards": [
            {
                "id": 1,
                "section_title": "Career Opportunities",
                "title": "Senior Developer",
                "description": "We are looking for experienced developers to join our team.",
                "image": "http://localhost:8000/storage/career-cards/career_card_1642123456.jpg",
                "is_active": true,
                "sort_order": 1,
                "created_at": "2024-01-15T10:30:00.000000Z",
                "updated_at": "2024-01-15T10:30:00.000000Z"
            }
        ]
    }
}
```

### Get Career Card by ID

**Endpoint**: `GET /api/v1/career-cards/{id}`

**Description**: Retrieves a specific career card by ID.

**Authentication**: Not required (public endpoint)

**Example**: `http://localhost:8000/api/v1/career-cards/1`

**JSON Data**: None

### Create Career Card

**Endpoint**: `POST /api/v1/career-cards`

**Description**: Creates a new career card.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/career-cards`

**Form Data**:
```
section_title: "Career Opportunities" (required, string, max 255 chars)
title: "Senior Developer" (required, string, max 255 chars)
description: "We are looking for experienced developers..." (optional, string)
image: [file] (optional, jpeg, png, jpg, gif, webp, max 5MB)
is_active: true (optional, boolean)
sort_order: 1 (optional, integer, min 0)
```

**JSON Alternative**:
```json
{
    "section_title": "Career Opportunities",
    "title": "Senior Developer",
    "description": "We are looking for experienced developers to join our team.",
    "is_active": true,
    "sort_order": 1
}
```

**Success Response (201)**:
```json
{
    "success": true,
    "message": "Career card created successfully",
    "data": {
        "career_card": {
            "id": 1,
            "section_title": "Career Opportunities",
            "title": "Senior Developer",
            "description": "We are looking for experienced developers to join our team.",
            "image": "http://localhost:8000/storage/career-cards/career_card_1642123456.jpg",
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

### Update Career Card

**Endpoint**: `PUT /api/v1/career-cards/{id}` or `PATCH /api/v1/career-cards/{id}`

**Description**: Updates an existing career card.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/career-cards/1`

**Form Data**:
```
section_title: "Updated Career Opportunities" (optional, string, max 255 chars)
title: "Updated Senior Developer" (optional, string, max 255 chars)
description: "Updated description..." (optional, string)
image: [file] (optional, jpeg, png, jpg, gif, webp, max 5MB)
is_active: false (optional, boolean)
sort_order: 2 (optional, integer, min 0)
```

**JSON Alternative**:
```json
{
    "section_title": "Updated Career Opportunities",
    "title": "Updated Senior Developer",
    "description": "Updated description...",
    "is_active": false,
    "sort_order": 2
}
```

**Delete Image**: To delete an image, send the field with value `delete` or `null`

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Career card updated successfully",
    "data": {
        "career_card": {
            "id": 1,
            "section_title": "Updated Career Opportunities",
            "title": "Updated Senior Developer",
            "subtitle": "Updated position",
            "description": "Updated description...",
            "image": "http://localhost:8000/storage/career-cards/new_image_1642123456.jpg",
            "is_active": false,
            "sort_order": 2,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T11:00:00.000000Z"
        }
    }
}
```

### Delete Career Card

**Endpoint**: `DELETE /api/v1/career-cards/{id}`

**Description**: Deletes a career card and associated images.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/career-cards/1`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Career card deleted successfully"
}
```

### Delete Specific Field from Career Card

**Endpoint**: `DELETE /api/v1/career-cards/{id}/field/{field}`

**Description**: Deletes a specific field from a career card.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Available Fields**: `image`, `section_title`, `title`, `description`, `is_active`, `sort_order`

**Example**: `http://localhost:8000/api/v1/career-cards/1/field/image`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Image deleted successfully",
    "data": {
        "career_card": {
            "id": 1,
            "section_title": "Career Opportunities",
            "title": "Senior Developer",
            "description": "We are looking for experienced developers to join our team.",
            "image": null,
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T11:15:00.000000Z"
        }
    }
}
```
<!-- job sections start -->

---

## Job Sections Management Endpoints

### Get Job Sections

**Endpoint**: `GET /api/v1/job-sections`

**Description**: Retrieves all active job sections ordered by sort order.

**Authentication**: Not required (public endpoint)

**Example**: `http://localhost:8000/api/v1/job-sections`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "job_sections": [
            {
                "id": 1,
                "section_title": "Available Positions",
                "section_description": "Explore our current job openings and find your perfect role.",
                "title": "Software Engineer",
                "description": "We are looking for talented software engineers to join our team.",
                "employment_type": "Full Time",
                "experience_required": "2 Years",
                "company_name": "House of Code",
                "image": "http://localhost:8000/storage/job-sections/job_section_1642123456.jpg",
                "is_active": true,
                "sort_order": 1,
                "created_at": "2024-01-15T10:30:00.000000Z",
                "updated_at": "2024-01-15T10:30:00.000000Z"
            }
        ]
    }
}
```

### Get Job Section by ID

**Endpoint**: `GET /api/v1/job-sections/{id}`

**Description**: Retrieves a specific job section by ID.

**Authentication**: Not required (public endpoint)

**Example**: `http://localhost:8000/api/v1/job-sections/1`

**JSON Data**: None

### Create Job Section

**Endpoint**: `POST /api/v1/job-sections`

**Description**: Creates a new job section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/job-sections`

**Form Data**:
```
section_title: "Available Positions" (required, string, max 255 chars)
section_description: "Explore our current job openings..." (optional, string)
title: "Software Engineer" (required, string, max 255 chars)
description: "We are looking for talented software engineers..." (optional, string)
employment_type: "Full Time" (optional, string, max 255 chars)
experience_required: "2 Years" (optional, string, max 255 chars)
company_name: "House of Code" (optional, string, max 255 chars)
image: [file] (optional, jpeg, png, jpg, gif, webp, max 5MB)
is_active: true (optional, boolean)
sort_order: 1 (optional, integer, min 0)
```

**JSON Alternative**:
```json
{
    "section_title": "Available Positions",
    "section_description": "Explore our current job openings and find your perfect role.",
    "title": "Software Engineer",
    "description": "We are looking for talented software engineers to join our team.",
    "employment_type": "Full Time",
    "experience_required": "2 Years",
    "company_name": "House of Code",
    "is_active": true,
    "sort_order": 1
}
```

**Success Response (201)**:
```json
{
    "success": true,
    "message": "Job section created successfully",
    "data": {
        "job_section": {
            "id": 1,
            "section_title": "Available Positions",
            "section_description": "Explore our current job openings and find your perfect role.",
            "title": "Software Engineer",
            "description": "We are looking for talented software engineers to join our team.",
            "image": "http://localhost:8000/storage/job-sections/job_section_1642123456.jpg",
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

### Update Job Section

**Endpoint**: `PUT /api/v1/job-sections/{id}` or `PATCH /api/v1/job-sections/{id}`

**Description**: Updates an existing job section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/job-sections/1`

**Form Data**:
```
section_title: "Updated Available Positions" (optional, string, max 255 chars)
section_description: "Updated description..." (optional, string)
title: "Updated Software Engineer" (optional, string, max 255 chars)
description: "Updated description..." (optional, string)
employment_type: "Part Time" (optional, string, max 255 chars)
experience_required: "3 Years" (optional, string, max 255 chars)
company_name: "Updated Company" (optional, string, max 255 chars)
image: [file] (optional, jpeg, png, jpg, gif, webp, max 5MB)
is_active: false (optional, boolean)
sort_order: 2 (optional, integer, min 0)
```

**JSON Alternative**:
```json
{
    "section_title": "Updated Available Positions",
    "section_description": "Updated description...",
    "title": "Updated Software Engineer",
    "description": "Updated description...",
    "employment_type": "Part Time",
    "experience_required": "3 Years",
    "company_name": "Updated Company",
    "is_active": false,
    "sort_order": 2
}
```

**Delete Image**: To delete an image, send the field with value `delete` or `null`

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Job section updated successfully",
    "data": {
        "job_section": {
            "id": 1,
            "section_title": "Updated Available Positions",
            "title": "Updated Software Engineer",
            "description": "Updated description...",
            "image": "http://localhost:8000/storage/job-sections/new_image_1642123456.jpg",
            "is_active": false,
            "sort_order": 2,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T11:00:00.000000Z"
        }
    }
}
```

### Delete Job Section

**Endpoint**: `DELETE /api/v1/job-sections/{id}`

**Description**: Deletes a job section and associated images.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/job-sections/1`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Job section deleted successfully"
}
```

### Delete Specific Field from Job Section

**Endpoint**: `DELETE /api/v1/job-sections/{id}/field/{field}`

**Description**: Deletes a specific field from a job section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Available Fields**: `image`, `section_title`, `section_description`, `title`, `description`, `employment_type`, `experience_required`, `company_name`, `is_active`, `sort_order`

**Example**: `http://localhost:8000/api/v1/job-sections/1/field/image`

**JSON Data**: None

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Image deleted successfully",
    "data": {
        "job_section": {
            "id": 1,
            "section_title": "Available Positions",
            "section_description": "Explore our current job openings and find your perfect role.",
            "title": "Software Engineer",
            "description": "We are looking for talented software engineers to join our team.",
            "image": null,
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T11:15:00.000000Z"
        }
    }
}
```

<!-- job sections end -->

---

## Notes

**Common HTTP Status Codes**:
- `200` - Success
- `201` - Created
- `403` - Unauthorized (insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Success Responses

All endpoints follow a consistent success response format:

```json
{
    "success": true,
    "message": "Success message here",
    "data": {
        // Response data
    }
}
```

---

## **FAQ Sections API**

### **Get All FAQ Sections (Public)**

**Endpoint**: `GET /api/v1/faq-sections`

**Description**: Retrieves all active FAQ sections ordered by sort_order and created_at.

**Authentication**: Not required

**Example**: `http://localhost:8000/api/v1/faq-sections`

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "faq_sections": [
            {
                "id": 1,
                "section_title": "Frequently Asked Questions",
                "question": "How can I get started?",
                "answer": "Getting started is easy. Simply contact us through our contact form.",
                "image": "http://localhost:8000/storage/faq-sections/faq_section_1642234567.jpg",
                "is_active": true,
                "sort_order": 1,
                "created_at": "2024-01-15T10:30:00.000000Z",
                "updated_at": "2024-01-15T10:30:00.000000Z"
            }
        ]
    }
}
```

---

### **Get FAQ Section by ID (Public)**

**Endpoint**: `GET /api/v1/faq-sections/{id}`

**Description**: Retrieves a specific FAQ section by ID.

**Authentication**: Not required

**Example**: `http://localhost:8000/api/v1/faq-sections/1`

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "faq_section": {
            "id": 1,
            "section_title": "Frequently Asked Questions",
            "section_description": "Find answers to common questions about our services.",
            "question": "How can I get started?",
            "answer": "Getting started is easy. Simply contact us through our contact form.",
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

---

### **Create FAQ Section (Admin Only)**

**Endpoint**: `POST /api/v1/faq-sections`

**Description**: Creates a new FAQ section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/faq-sections`

**Form Data**:
```
section_title: "Frequently Asked Questions" (required, string, max 255 chars)
question: "How can I get started?" (required, string, max 255 chars)
answer: "Getting started is easy..." (optional, string)
is_active: true (optional, boolean)
sort_order: 1 (optional, integer, min 0)
```

**JSON Alternative**:
```json
{
    "section_title": "Frequently Asked Questions",
    "question": "How can I get started?",
    "answer": "Getting started is easy. Simply contact us through our contact form.",
    "is_active": true,
    "sort_order": 1
}
```

**Success Response (201)**:
```json
{
    "success": true,
    "message": "FAQ section created successfully",
    "data": {
        "faq_section": {
            "id": 1,
            "section_title": "Frequently Asked Questions",
            "section_description": "Find answers to common questions about our services.",
            "question": "How can I get started?",
            "answer": "Getting started is easy. Simply contact us through our contact form.",
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

---

### **Update FAQ Section (Admin Only)**

**Endpoint**: `PUT /api/v1/faq-sections/{id}`

**Description**: Updates an existing FAQ section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/faq-sections/1`

**Form Data**:
```
section_title: "Updated FAQ Section" (optional, string, max 255 chars)
question: "Updated question" (optional, string, max 255 chars)
answer: "Updated answer..." (optional, string)
is_active: false (optional, boolean)
sort_order: 2 (optional, integer, min 0)
```

**JSON Alternative**:
```json
{
    "section_title": "Updated FAQ Section",
    "question": "Updated question",
    "answer": "Updated answer",
    "is_active": false,
    "sort_order": 2
}
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "FAQ section updated successfully",
    "data": {
        "faq_section": {
            "id": 1,
            "section_title": "Updated FAQ Section",
            "section_description": "Updated description...",
            "question": "Updated question",
            "answer": "Updated answer...",
            "is_active": false,
            "sort_order": 2,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T11:00:00.000000Z"
        }
    }
}
```

---

### **Delete FAQ Section (Admin Only)**

**Endpoint**: `DELETE /api/v1/faq-sections/{id}`

**Description**: Deletes a FAQ section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/faq-sections/1`

**Success Response (200)**:
```json
{
    "success": true,
    "message": "FAQ section deleted successfully"
}
```

---

### **Delete Specific Field from FAQ Section (Admin Only)**

**Endpoint**: `DELETE /api/v1/faq-sections/{id}/field/{field}`

**Description**: Deletes a specific field from a FAQ section without deleting the entire section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Available Fields**: `section_title`, `question`, `answer`, `is_active`, `sort_order`

**Example**: `http://localhost:8000/api/v1/faq-sections/1/field/section_title`

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Section title deleted successfully",
    "data": {
        "faq_section": {
            "id": 1,
            "section_title": null,
            "question": "How can I get started?",
            "answer": "Getting started is easy. Simply contact us through our contact form.",
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T11:15:00.000000Z"
        }
    }
}
```

---

## **Error Responses**

### **Authentication Error (401)**:
```json
{
    "success": false,
    "message": "Unauthorized. Please provide a valid authentication token."
}
```

### **Authorization Error (403)**:
```json
{
    "success": false,
    "message": "Unauthorized. Only admins can manage FAQ sections."
}
```

### **Validation Error (422)**:
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "section_title": ["The section title field is required."],
        "question": ["The question field is required."]
    }
}
```

### **Not Found Error (404)**:
```json
{
    "success": false,
    "message": "FAQ section not found."
}
```

---

## **Available Fields for FAQ Sections**

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `section_title` | string | Yes | 255 | The main title of FAQ section |
| `question` | string | Yes | 255 | The FAQ question |
| `answer` | text | No | - | The FAQ answer |
| `is_active` | boolean | No | - | Whether section is active (default: true) |
| `sort_order` | integer | No | - | Display order (default: 0) |

---

- **Supported Formats**: JPEG, PNG, JPG, GIF, WebP
- **Maximum Size**: 5MB (5120 KB)
- **Storage Path**: `storage/app/public/faq-sections/`
- **Public URL**: `http://localhost:8000/storage/faq-sections/`
- **File Naming**: `faq_section_{timestamp}.{extension}`

---

## **Notes**

- All timestamps are in UTC format (ISO 8601)
- Images are automatically deleted when the FAQ section is deleted
- Image URLs are automatically generated with the full public path
- The API supports both form-data and JSON requests
- Base64 image strings should include the data URI scheme (e.g., `data:image/jpeg;base64,...`)
- The `deleteField` endpoint is useful for removing specific fields like images without deleting the entire section
- Public endpoints (GET) do not require authentication
- Admin endpoints (POST, PUT, DELETE) require authentication and proper authorization

---
<!-- support section -->

## Support Section Management Endpoints

### Get Support Sections

**Endpoint**: `GET /api/v1/support-sections`

**Description**: Retrieves all support sections ordered by sort_order.

**Authentication**: Not required

**Example**: `http://localhost:8000/api/v1/support-sections`

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "support_sections": [
            {
                "id": 1,
                "section_title": "Contact Support",
                "title": "Get in Touch",
                "description": "We're here to help you with any questions or concerns.",
                "call_icon": "phone-icon.svg",
                "call_title": "Call Us",
                "call_description": "Available Monday to Friday, 9 AM to 6 PM",
                "call_phone": "+1-234-567-8900",
                "email_icon": "email-icon.svg",
                "email_title": "Email Us",
                "email_description": "We'll respond within 24 hours",
                "email_address": "support@example.com",
                "is_active": true,
                "sort_order": 1,
                "created_at": "2024-01-15T10:30:00.000000Z",
                "updated_at": "2024-01-15T10:30:00.000000Z"
            }
        ]
    }
}
```

---

### Get Specific Support Section

**Endpoint**: `GET /api/v1/support-sections/{id}`

**Description**: Retrieves a specific support section by ID.

**Authentication**: Not required

**Example**: `http://localhost:8000/api/v1/support-sections/1`

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "support_section": {
            "id": 1,
            "section_title": "Contact Support",
            "title": "Get in Touch",
            "description": "We're here to help you with any questions or concerns.",
            "call_icon": "phone-icon.svg",
            "call_title": "Call Us",
            "call_description": "Available Monday to Friday, 9 AM to 6 PM",
            "call_phone": "+1-234-567-8900",
            "email_icon": "email-icon.svg",
            "email_title": "Email Us",
            "email_description": "We'll respond within 24 hours",
            "email_address": "support@example.com",
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

---

### Create Support Section (Admin Only)

**Endpoint**: `POST /api/v1/support-sections`

**Description**: Creates a new support section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/support-sections`

**Form Data**:
```
section_title: "Contact Support" (required, string, max 255 chars)
title: "Get in Touch" (required, string, max 255 chars)
description: "We're here to help you..." (optional, string)
call_icon: [file] (optional, image file - jpeg, png, jpg, gif, webp, max 5MB)
call_title: "Call Us" (optional, string, max 255 chars)
call_description: "Available Monday to Friday..." (optional, string)
call_phone: "+1-234-567-8900" (optional, string, max 255 chars)
email_icon: [file] (optional, image file - jpeg, png, jpg, gif, webp, max 5MB)
email_title: "Email Us" (optional, string, max 255 chars)
email_description: "We'll respond within 24 hours" (optional, string)
email_address: "support@example.com" (optional, email, max 255 chars)
is_active: true (optional, boolean)
sort_order: 1 (optional, integer, min 0)
```

**JSON Alternative**:
```json
{
    "section_title": "Contact Support",
    "title": "Get in Touch",
    "description": "We're here to help you with any questions or concerns.",
    "call_icon": "phone-icon.svg",
    "call_title": "Call Us",
    "call_description": "Available Monday to Friday, 9 AM to 6 PM",
    "call_phone": "+1-234-567-8900",
    "email_icon": "email-icon.svg",
    "email_title": "Email Us",
    "email_description": "We'll respond within 24 hours",
    "email_address": "support@example.com",
    "is_active": true,
    "sort_order": 1
}
```

**Success Response (201)**:
```json
{
    "success": true,
    "message": "Support section created successfully",
    "data": {
        "support_section": {
            "id": 1,
            "section_title": "Contact Support",
            "title": "Get in Touch",
            "description": "We're here to help you with any questions or concerns.",
            "call_icon": "phone-icon.svg",
            "call_title": "Call Us",
            "call_description": "Available Monday to Friday, 9 AM to 6 PM",
            "call_phone": "+1-234-567-8900",
            "email_icon": "email-icon.svg",
            "email_title": "Email Us",
            "email_description": "We'll respond within 24 hours",
            "email_address": "support@example.com",
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

---

### Update Support Section (Admin Only)

**Endpoint**: `PUT /api/v1/support-sections/{id}`

**Description**: Updates an existing support section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/support-sections/1`

**Form Data**:
```
section_title: "Updated Support Section" (optional, string, max 255 chars)
title: "Updated Title" (optional, string, max 255 chars)
description: "Updated description..." (optional, string)
call_icon: [file] (optional, image file - jpeg, png, jpg, gif, webp, max 5MB)
call_title: "Updated Call Title" (optional, string, max 255 chars)
call_description: "Updated call description..." (optional, string)
call_phone: "+1-234-567-8901" (optional, string, max 255 chars)
email_icon: [file] (optional, image file - jpeg, png, jpg, gif, webp, max 5MB)
email_title: "Updated Email Title" (optional, string, max 255 chars)
email_description: "Updated email description..." (optional, string)
email_address: "updated-support@example.com" (optional, email, max 255 chars)
is_active: false (optional, boolean)
sort_order: 2 (optional, integer, min 0)
```

**JSON Alternative**:
```json
{
    "section_title": "Updated Support Section",
    "title": "Updated Title",
    "description": "Updated description",
    "call_icon": "updated-icon.svg",
    "call_title": "Updated Call Title",
    "call_description": "Updated call description",
    "call_phone": "+1-234-567-8901",
    "email_icon": "updated-email-icon.svg",
    "email_title": "Updated Email Title",
    "email_description": "Updated email description",
    "email_address": "updated-support@example.com",
    "is_active": false,
    "sort_order": 2
}
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Support section updated successfully",
    "data": {
        "support_section": {
            "id": 1,
            "section_title": "Updated Support Section",
            "title": "Updated Title",
            "description": "Updated description",
            "call_icon": "updated-icon.svg",
            "call_title": "Updated Call Title",
            "call_description": "Updated call description",
            "call_phone": "+1-234-567-8901",
            "email_icon": "updated-email-icon.svg",
            "email_title": "Updated Email Title",
            "email_description": "Updated email description",
            "email_address": "updated-support@example.com",
            "is_active": false,
            "sort_order": 2,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T11:00:00.000000Z"
        }
    }
}
```

---

### Delete Support Section (Admin Only)

**Endpoint**: `DELETE /api/v1/support-sections/{id}`

**Description**: Deletes a support section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Example**: `http://localhost:8000/api/v1/support-sections/1`

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Support section deleted successfully"
}
```

---

### Delete Specific Field from Support Section (Admin Only)

**Endpoint**: `DELETE /api/v1/support-sections/{id}/field/{field}`

**Description**: Deletes a specific field from a support section without deleting the entire section.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Available Fields**: `section_title`, `title`, `description`, `call_icon`, `call_title`, `call_description`, `call_phone`, `email_icon`, `email_title`, `email_description`, `email_address`, `is_active`, `sort_order`

**Example**: `http://localhost:8000/api/v1/support-sections/1/field/call_phone`

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Call phone deleted successfully",
    "data": {
        "support_section": {
            "id": 1,
            "section_title": "Contact Support",
            "title": "Get in Touch",
            "description": "We're here to help you with any questions or concerns.",
            "call_icon": "phone-icon.svg",
            "call_title": "Call Us",
            "call_description": "Available Monday to Friday, 9 AM to 6 PM",
            "call_phone": null,
            "email_icon": "email-icon.svg",
            "email_title": "Email Us",
            "email_description": "We'll respond within 24 hours",
            "email_address": "support@example.com",
            "is_active": true,
            "sort_order": 1,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T11:15:00.000000Z"
        }
    }
}
```

---

## Available Fields for Support Sections

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `section_title` | string | Yes | 255 | The main title of support section |
| `title` | string | Yes | 255 | The display title |
| `description` | text | No | - | Support section description |
| `call_icon` | file/string | No | 255 | Icon for call section (image file or string) |
| `call_title` | string | No | 255 | Title for call section |
| `call_description` | text | No | - | Description for call section |
| `call_phone` | string | No | 255 | Phone number |
| `email_icon` | file/string | No | 255 | Icon for email section (image file or string) |
| `email_title` | string | No | 255 | Title for email section |
| `email_description` | text | No | - | Description for email section |
| `email_address` | string | No | 255 | Email address |
| `is_active` | boolean | No | - | Whether section is active (default: true) |
| `sort_order` | integer | No | - | Display order (default: 0) |

---

## Notes

- The API supports both form-data and JSON requests
- The `deleteField` endpoint is useful for removing specific fields without deleting the entire section
- Public endpoints (GET) do not require authentication
- Admin endpoints (POST, PUT, DELETE) require authentication and proper authorization
- Email address field is validated as a proper email format
- Icon fields (`call_icon`, `email_icon`) support both file uploads and string values
- **File Upload Details**:
  - Supported Formats: JPEG, PNG, JPG, GIF, WebP
  - Maximum Size: 5MB (5120 KB)
  - Storage Path: `storage/app/public/support-icons/`
  - Public URL: `http://localhost:8000/storage/support-icons/`
  - File Naming: Automatically generated by Laravel
  - Old icon files are automatically deleted when updated
  - Icon files are automatically deleted when support section is deleted

---

### Get Specific Contact Form Submission (Admin Only)

**Endpoint**: `GET /api/v1/contact-form-submissions/{id}`

**Postman URL**: `http://localhost:8000/api/v1/contact-form-submissions/1`

**Method**: `GET`

**Description**: Retrieves a specific contact form submission by ID.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the contact form submission

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "phone": "+1-234-567-8900",
        "company": "Example Company",
        "message": "I would like to inquire about your services...",
        "is_read": false,
        "created_at": "2024-01-20T10:30:00.000000Z",
        "updated_at": "2024-01-20T10:30:00.000000Z"
    }
}
```

**Error Response (404 - Not Found)**:
```json
{
    "success": false,
    "message": "Contact form submission not found",
    "error": "No query results for model [App\\Models\\ContactFormSubmission] 999"
}
```

---

### Mark Contact Form Submission as Read (Admin Only)

**Endpoint**: `POST /api/v1/contact-form-submissions/{id}/mark-read`

**Postman URL**: `http://localhost:8000/api/v1/contact-form-submissions/1/mark-read`

**Method**: `POST`

**Description**: Marks a contact form submission as read.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the contact form submission

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Contact form submission marked as read",
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "phone": "+1-234-567-8900",
        "company": "Example Company",
        "message": "I would like to inquire about your services...",
        "is_read": true,
        "created_at": "2024-01-20T10:30:00.000000Z",
        "updated_at": "2024-01-20T11:00:00.000000Z"
    }
}
```

**Error Response (404 - Not Found)**:
```json
{
    "success": false,
    "message": "Failed to mark as read",
    "error": "No query results for model [App\\Models\\ContactFormSubmission] 999"
}
```

---

### Mark Contact Form Submission as Unread (Admin Only)

**Endpoint**: `POST /api/v1/contact-form-submissions/{id}/mark-unread`

**Postman URL**: `http://localhost:8000/api/v1/contact-form-submissions/1/mark-unread`

**Method**: `POST`

**Description**: Marks a contact form submission as unread.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the contact form submission

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Contact form submission marked as unread",
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "phone": "+1-234-567-8900",
        "company": "Example Company",
        "message": "I would like to inquire about your services...",
        "is_read": false,
        "created_at": "2024-01-20T10:30:00.000000Z",
        "updated_at": "2024-01-20T11:15:00.000000Z"
    }
}
```

**Error Response (404 - Not Found)**:
```json
{
    "success": false,
    "message": "Failed to mark as unread",
    "error": "No query results for model [App\\Models\\ContactFormSubmission] 999"
}
```

---

### Delete Contact Form Submission (Admin Only)

**Endpoint**: `DELETE /api/v1/contact-form-submissions/{id}`

**Postman URL**: `http://localhost:8000/api/v1/contact-form-submissions/1`

**Method**: `DELETE`

**Description**: Deletes a contact form submission.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the contact form submission to delete

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Contact form submission deleted successfully"
}
```

**Error Response (404 - Not Found)**:
```json
{
    "success": false,
    "message": "Failed to delete contact form submission",
    "error": "No query results for model [App\\Models\\ContactFormSubmission] 999"
}
```

---

## Available Fields for Contact Form Submissions

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `first_name` | string | Yes | 255 | Contact person's first name |
| `last_name` | string | Yes | 255 | Contact person's last name |
| `email` | string | Yes | 255 | Contact person's email address (must be valid email format) |
| `phone` | string | No | 255 | Contact person's phone number |
| `company` | string | No | 255 | Contact person's company name |
| `message` | string | Yes | 5000 | Message or inquiry details |
| `is_read` | boolean | No | - | Whether submission has been read (default: false) |
| `created_at` | timestamp | No | - | When the form was submitted (auto-set) |
| `updated_at` | timestamp | No | - | When the record was last updated (auto-set) |

---

## Notes

- The contact form submission endpoint is publicly accessible (no authentication required)
- All management endpoints require admin authentication and proper authorization
- Submissions are automatically marked as unread when created
- The API supports both form-data and JSON requests for form submission
- Search functionality searches across `first_name`, `last_name`, `email`, `phone`, `company`, and `message` fields
- Pagination is supported with customizable `per_page` parameter (default: 15)
- All timestamps are in UTC format
- Email field is validated as a proper email format
- Phone and company fields are optional
- First name, last name, email, and message fields are required

---

## Career Support Form Management Endpoints

### Submit Career Support Form (Public)

**Endpoint**: `POST /api/v1/career-support-form`

**Postman URL**: `http://localhost:8000/api/v1/career-support-form`

**Method**: `POST`

**Description**: Submits a new career support form entry. This endpoint is publicly accessible.

**Authentication**: Not required

**Headers**:
- `Content-Type: application/json` (for JSON) or `multipart/form-data` (for form data)

**Form Data**:
```
full_name: "John Doe" (required, string, max 255 chars)
email: "john.doe@example.com" (required, email, max 255 chars)
phone: "+1-234-567-8900" (optional, string, max 255 chars)
job_location: "New York, NY" (optional, string, max 255 chars)
preferred_contact_method: "email" (optional, one of: email, phone, sms, whatsapp, any)
best_time_to_contact: "morning" (optional, one of: morning, afternoon, evening, anytime)
message: "Describe your project or any special requirements..." (required, string, max 5000 chars)
```

**JSON Alternative**:
```json
{
    "full_name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+1-234-567-8900",
    "job_location": "New York, NY",
    "preferred_contact_method": "email",
    "best_time_to_contact": "morning",
    "message": "Describe your project or any special requirements..."
}
```

**Success Response (201)**:
```json
{
    "success": true,
    "message": "Career support form submitted successfully",
    "data": {
        "career_support_form_submission": {
            "id": 1,
            "full_name": "John Doe",
            "email": "john.doe@example.com",
            "phone": "+1-234-567-8900",
            "job_location": "New York, NY",
            "preferred_contact_method": "email",
            "best_time_to_contact": "morning",
            "message": "Describe your project or any special requirements...",
            "is_read": false,
            "created_at": "2024-01-20T10:30:00.000000Z",
            "updated_at": "2024-01-20T10:30:00.000000Z"
        }
    }
}
```

**Error Response (422 - Validation Error)**:
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "full_name": ["The full name field is required."],
        "email": ["The email field is required.", "The email must be a valid email address."],
        "message": ["The message field is required."],
        "preferred_contact_method": ["The selected preferred contact method is invalid."],
        "best_time_to_contact": ["The selected best time to contact is invalid."]
    }
}
```

---

### Get Career Support Form Submissions (Admin Only)

**Endpoint**: `GET /api/v1/career-support-form-submissions`

**Postman URL**: `http://localhost:8000/api/v1/career-support-form-submissions`

**Method**: `GET`

**Description**: Retrieves all career support form submissions with pagination, filtering, and search capabilities.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Query Parameters**:
```
is_read: true/false (optional, filter by read status)
search: "search term" (optional, search in full_name, email, phone, job_location, message)
per_page: 15 (optional, number of items per page, default: 15)
```

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 1,
                "full_name": "John Doe",
                "email": "john.doe@example.com",
                "phone": "+1-234-567-8900",
                "job_location": "New York, NY",
                "preferred_contact_method": "email",
                "best_time_to_contact": "morning",
                "message": "Describe your project or any special requirements...",
                "is_read": false,
                "created_at": "2024-01-20T10:30:00.000000Z",
                "updated_at": "2024-01-20T10:30:00.000000Z"
            }
        ],
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 1,
        "from": 1,
        "to": 1
    }
}
```

---

### Get Career Support Form Statistics (Admin Only)

**Endpoint**: `GET /api/v1/career-support-form-submissions/stats`

**Postman URL**: `http://localhost:8000/api/v1/career-support-form-submissions/stats`

**Method**: `GET`

**Description**: Retrieves statistics about career support form submissions.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "total_submissions": 25,
        "unread_submissions": 8,
        "read_submissions": 17,
        "recent_submissions": 12
    }
}
```

---

### Get Specific Career Support Form Submission (Admin Only)

**Endpoint**: `GET /api/v1/career-support-form-submissions/{id}`

**Postman URL**: `http://localhost:8000/api/v1/career-support-form-submissions/1`

**Method**: `GET`

**Description**: Retrieves a specific career support form submission by ID.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the career support form submission

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone": "+1-234-567-8900",
        "job_location": "New York, NY",
        "preferred_contact_method": "email",
        "best_time_to_contact": "morning",
        "message": "Describe your project or any special requirements...",
        "is_read": false,
        "created_at": "2024-01-20T10:30:00.000000Z",
        "updated_at": "2024-01-20T10:30:00.000000Z"
    }
}
```

**Error Response (404 - Not Found)**:
```json
{
    "success": false,
    "message": "Career support form submission not found",
    "error": "No query results for model [App\\Models\\CareerSupportFormSubmission] 999"
}
```

---

### Mark Career Support Form Submission as Read (Admin Only)

**Endpoint**: `POST /api/v1/career-support-form-submissions/{id}/mark-read`

**Postman URL**: `http://localhost:8000/api/v1/career-support-form-submissions/1/mark-read`

**Method**: `POST`

**Description**: Marks a career support form submission as read.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the career support form submission

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Career support form submission marked as read",
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone": "+1-234-567-8900",
        "job_location": "New York, NY",
        "preferred_contact_method": "email",
        "best_time_to_contact": "morning",
        "message": "Describe your project or any special requirements...",
        "is_read": true,
        "created_at": "2024-01-20T10:30:00.000000Z",
        "updated_at": "2024-01-20T11:00:00.000000Z"
    }
}
```

---

### Mark Career Support Form Submission as Unread (Admin Only)

**Endpoint**: `POST /api/v1/career-support-form-submissions/{id}/mark-unread`

**Postman URL**: `http://localhost:8000/api/v1/career-support-form-submissions/1/mark-unread`

**Method**: `POST`

**Description**: Marks a career support form submission as unread.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the career support form submission

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Career support form submission marked as unread",
    "data": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "phone": "+1-234-567-8900",
        "job_location": "New York, NY",
        "preferred_contact_method": "email",
        "best_time_to_contact": "morning",
        "message": "Describe your project or any special requirements...",
        "is_read": false,
        "created_at": "2024-01-20T10:30:00.000000Z",
        "updated_at": "2024-01-20T11:15:00.000000Z"
    }
}
```

---

### Delete Career Support Form Submission (Admin Only)

**Endpoint**: `DELETE /api/v1/career-support-form-submissions/{id}`

**Postman URL**: `http://localhost:8000/api/v1/career-support-form-submissions/1`

**Method**: `DELETE`

**Description**: Deletes a career support form submission.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the career support form submission to delete

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Career support form submission deleted successfully"
}
```

**Error Response (404 - Not Found)**:
```json
{
    "success": false,
    "message": "Failed to delete career support form submission",
    "error": "No query results for model [App\\Models\\CareerSupportFormSubmission] 999"
}
```

---

## Available Fields for Career Support Form Submissions

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `full_name` | string | Yes | 255 | Contact person's full name |
| `email` | string | Yes | 255 | Contact person's email address (must be valid email format) |
| `phone` | string | No | 255 | Contact person's phone number |
| `job_location` | string | No | 255 | Job or project location |
| `preferred_contact_method` | string | No | - | Preferred contact method: `email`, `phone`, `sms`, `whatsapp`, or `any` |
| `best_time_to_contact` | string | No | - | Best time to contact: `morning`, `afternoon`, `evening`, or `anytime` |
| `message` | string | Yes | 5000 | Project description or special requirements |
| `is_read` | boolean | No | - | Whether submission has been read (default: false) |
| `created_at` | timestamp | No | - | When the form was submitted (auto-set) |
| `updated_at` | timestamp | No | - | When the record was last updated (auto-set) |

---

## Notes

- The career support form submission endpoint is publicly accessible (no authentication required)
- All management endpoints require admin authentication and proper authorization
- Submissions are automatically marked as unread when created
- The API supports both form-data and JSON requests for form submission
- Search functionality searches across `full_name`, `email`, `phone`, `job_location`, and `message` fields
- Pagination is supported with customizable `per_page` parameter (default: 15)
- All timestamps are in UTC format
- Email field is validated as a proper email format
- Phone, job_location, preferred_contact_method, and best_time_to_contact fields are optional
- Full name, email, and message fields are required
- **Preferred Contact Method** allowed values: `email`, `phone`, `sms`, `whatsapp`, `any`
- **Best Time to Contact** allowed values: `morning`, `afternoon`, `evening`, `anytime`

---

## Quote Form Management Endpoints

### Submit Quote Form (Public)

**Endpoint**: `POST /api/v1/quote-form`

**Postman URL**: `http://localhost:8000/api/v1/quote-form`

**Method**: `POST`

**Description**: Submits a new quote form entry. This endpoint is publicly accessible.

**Authentication**: Not required

**Headers**:
- `Content-Type: multipart/form-data` (for file uploads) or `application/json`

**Form Data / JSON Fields**:

- `first_name` (required, string, max 255 chars)
- `last_name` (required, string, max 255 chars)
- `phone` (optional, string, max 255 chars)
- `email` (required, email, max 255 chars)
- `company_name` (optional, string, max 255 chars)
- `country` (optional, string, max 255 chars)
- `project_type` (optional, string, max 255 chars)
- `estimate_budget` (optional, string, max 255 chars)
- `maximum_time_for_project` (optional, string, max 255 chars)
- `required_skills` (optional, string, max 5000 chars)
- `uploaded_files` (optional, array of files - max 10MB per file, allowed: pdf, doc, docx, xls, xlsx, ppt, pptx, txt, jpg, jpeg, png, gif, zip, rar)
- `message` (optional, string, max 5000 chars)

**JSON Example**:
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+1-234-567-8900",
    "email": "john.doe@example.com",
    "company_name": "Example Corp",
    "country": "United States",
    "project_type": "E-commerce Platform",
    "estimate_budget": "$50,000 - $100,000",
    "maximum_time_for_project": "6-12 months",
    "required_skills": "React.js, Node.js, PostgreSQL",
    "message": "Additional notes or special requirements"
}
```

**Success Response (201)**:
```json
{
    "success": true,
    "message": "Quote form submitted successfully",
    "data": {
        "quote_form_submission": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "phone": "+1-234-567-8900",
            "email": "john.doe@example.com",
            "company_name": "Example Corp",
            "country": "United States",
            "project_type": "E-commerce Platform",
            "estimate_budget": "$50,000 - $100,000",
            "maximum_time_for_project": "6-12 months",
            "required_skills": "React.js, Node.js, PostgreSQL",
            "uploaded_files": null,
            "message": "Additional notes or special requirements",
            "is_read": false,
            "created_at": "2024-01-20T10:30:00.000000Z",
            "updated_at": "2024-01-20T10:30:00.000000Z"
        }
    }
}
```

**Error Response (422 - Validation Error)**:
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "first_name": ["The first name field is required."],
        "last_name": ["The last name field is required."],
        "email": ["The email field is required.", "The email must be a valid email address."]
    }
}
```

---

### Get Quote Form Submissions (Admin Only)

**Endpoint**: `GET /api/v1/quote-form-submissions`

**Postman URL**: `http://localhost:8000/api/v1/quote-form-submissions`

**Method**: `GET`

**Description**: Retrieves all quote form submissions with pagination, filtering, and search capabilities.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Query Parameters**:
```
is_read: true/false (optional, filter by read status)
search: "search term" (optional, search in first_name, last_name, email, phone, company_name, message)
per_page: 15 (optional, number of items per page, default: 15)
```

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 1,
                "first_name": "John",
                "last_name": "Doe",
                "email": "john.doe@example.com",
                "company_name": "Example Corp",
                "is_read": false,
                "created_at": "2024-01-20T10:30:00.000000Z"
            }
        ],
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 1,
        "from": 1,
        "to": 1
    }
}
```

---

### Get Quote Form Statistics (Admin Only)

**Endpoint**: `GET /api/v1/quote-form-submissions/stats`

**Postman URL**: `http://localhost:8000/api/v1/quote-form-submissions/stats`

**Method**: `GET`

**Description**: Retrieves statistics about quote form submissions.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "total_submissions": 25,
        "unread_submissions": 8,
        "read_submissions": 17,
        "recent_submissions": 12
    }
}
```

---

### Get Specific Quote Form Submission (Admin Only)

**Endpoint**: `GET /api/v1/quote-form-submissions/{id}`

**Postman URL**: `http://localhost:8000/api/v1/quote-form-submissions/1`

**Method**: `GET`

**Description**: Retrieves a specific quote form submission by ID with all details.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "phone": "+1-234-567-8900",
        "email": "john.doe@example.com",
        "company_name": "Example Corp",
        "country": "United States",
        "project_type": "E-commerce Platform",
        "estimate_budget": "$50,000 - $100,000",
        "maximum_time_for_project": "6-12 months",
        "required_skills": "React.js, Node.js, PostgreSQL",
        "uploaded_files": null,
        "message": "Additional notes or special requirements",
        "is_read": false,
        "created_at": "2024-01-20T10:30:00.000000Z",
        "updated_at": "2024-01-20T10:30:00.000000Z"
    }
}
```

---

### Mark Quote Form Submission as Read (Admin Only)

**Endpoint**: `POST /api/v1/quote-form-submissions/{id}/mark-read`

**Method**: `POST`

**Description**: Marks a quote form submission as read.

**Authentication**: Required (Bearer token)

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Quote form submission marked as read",
    "data": {
        "id": 1,
        "is_read": true,
        "updated_at": "2024-01-20T11:00:00.000000Z"
    }
}
```

---

### Mark Quote Form Submission as Unread (Admin Only)

**Endpoint**: `POST /api/v1/quote-form-submissions/{id}/mark-unread`

**Method**: `POST`

**Description**: Marks a quote form submission as unread.

**Authentication**: Required (Bearer token)

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Quote form submission marked as unread",
    "data": {
        "id": 1,
        "is_read": false,
        "updated_at": "2024-01-20T11:15:00.000000Z"
    }
}
```

---

### Delete Quote Form Submission (Admin Only)

**Endpoint**: `DELETE /api/v1/quote-form-submissions/{id}`

**Method**: `DELETE`

**Description**: Deletes a quote form submission and associated uploaded files.

**Authentication**: Required (Bearer token)

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Quote form submission deleted successfully"
}
```

---

## Available Fields for Quote Form Submissions

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `first_name` | string | Yes | 255 | Contact person's first name |
| `last_name` | string | Yes | 255 | Contact person's last name |
| `phone` | string | No | 255 | Phone number |
| `email` | string | Yes | 255 | Email address (must be valid email format) |
| `company_name` | string | No | 255 | Company or business name |
| `country` | string | No | 255 | Country |
| `project_type` | string | No | 255 | Project type |
| `estimate_budget` | string | No | 255 | Estimated budget range |
| `maximum_time_for_project` | string | No | 255 | Maximum time for the project |
| `required_skills` | text | No | 5000 | Required skills |
| `uploaded_files` | string | No | - | JSON array of uploaded file paths |
| `message` | text | No | 5000 | Additional message or notes |
| `is_read` | boolean | No | - | Whether submission has been read (default: false) |
| `created_at` | timestamp | No | - | When the form was submitted (auto-set) |
| `updated_at` | timestamp | No | - | When the record was last updated (auto-set) |

---

## Notes

- The quote form submission endpoint is publicly accessible (no authentication required)
- All management endpoints require admin authentication and proper authorization
- Submissions are automatically marked as unread when created
- The API supports both form-data (for file uploads) and JSON requests
- File uploads are stored in `storage/app/public/quote-form-files/` directory
- Maximum file size: 10MB per file
- Allowed file types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG, GIF, ZIP, RAR
- Multiple files can be uploaded as an array
- Search functionality searches across `first_name`, `last_name`, `email`, `phone`, `company_name`, and `message` fields
- Pagination is supported with customizable `per_page` parameter (default: 15)
- All timestamps are in UTC format
- Email field is validated as a proper email format
- First name, last name, and email fields are required
- All other fields are optional
- Uploaded files are automatically deleted when a submission is deleted

---

## Team Members Management Endpoints

### Overview
The Team Members API allows administrators to manage team member information displayed on the contact page. This includes team member names, roles, email addresses, and profile pictures.

### Get All Team Members (Public)

**Endpoint**: `GET /api/v1/team-members`

**Postman URL**: `http://localhost:8000/api/v1/team-members`

**Method**: `GET`

**Description**: Retrieves all team members ordered by their order field.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "team_members": [
            {
                "id": 1,
                "first_name": "John",
                "last_name": "Doe",
                "role": "CEO & Founder",
                "email": "john@mecarvi.com",
                "picture": "http://localhost:8000/storage/team-members/abc123.jpg",
                "order": 1,
                "created_at": "2024-01-20T10:30:00.000000Z",
                "updated_at": "2024-01-20T10:30:00.000000Z"
            }
        ]
    }
}
```

---

### Get Specific Team Member (Public)

**Endpoint**: `GET /api/v1/team-members/{id}`

**Postman URL**: `http://localhost:8000/api/v1/team-members/1`

**Method**: `GET`

**Description**: Retrieves a specific team member by ID.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the team member

**Success Response (200)**:
```json
{
    "success": true,
    "data": {
        "team_member": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "role": "CEO & Founder",
            "email": "john@mecarvi.com",
            "picture": "http://localhost:8000/storage/team-members/abc123.jpg",
            "order": 1,
            "created_at": "2024-01-20T10:30:00.000000Z",
            "updated_at": "2024-01-20T10:30:00.000000Z"
        }
    }
}
```

**Error Response (404 - Not Found)**:
```json
{
    "success": false,
    "message": "Team member not found."
}
```

---

### Create Team Member (Admin Only)

**Endpoint**: `POST /api/v1/team-members`

**Postman URL**: `http://localhost:8000/api/v1/team-members`

**Method**: `POST`

**Description**: Creates a new team member.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: multipart/form-data` (for file upload) or `application/json`
- `Accept: application/json`

**Form Data**:
```
first_name: "John" (required, string, max 255 chars)
last_name: "Doe" (required, string, max 255 chars)
role: "CEO & Founder" (required, string, max 255 chars)
email: "john@mecarvi.com" (required, email, max 255 chars)
picture: [file] (optional, image file: jpeg, png, jpg, gif, webp, max 2MB)
order: 1 (optional, integer, min 0)
```

**JSON Alternative** (without picture):
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "role": "CEO & Founder",
    "email": "john@mecarvi.com",
    "order": 1
}
```

**Success Response (201)**:
```json
{
    "success": true,
    "message": "Team member created successfully",
    "data": {
        "team_member": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "role": "CEO & Founder",
            "email": "john@mecarvi.com",
            "picture": "http://localhost:8000/storage/team-members/abc123.jpg",
            "order": 1,
            "created_at": "2024-01-20T10:30:00.000000Z",
            "updated_at": "2024-01-20T10:30:00.000000Z"
        }
    }
}
```

**Error Response (422 - Validation Error)**:
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "first_name": ["The first name field is required."],
        "last_name": ["The last name field is required."],
        "role": ["The role field is required."],
        "email": ["The email field is required.", "The email must be a valid email address."],
        "picture": ["The picture must be an image.", "The picture must not be greater than 2048 kilobytes."]
    }
}
```

**Error Response (403 - Unauthorized)**:
```json
{
    "success": false,
    "message": "Unauthorized. Only admins can create team members."
}
```

---

### Update Team Member (Admin Only)

**Endpoint**: `PUT /api/v1/team-members/{id}` or `POST /api/v1/team-members/{id}`

**Postman URL**: `http://localhost:8000/api/v1/team-members/1`

**Method**: `PUT` or `POST` (with `_method=PUT` for form data)

**Description**: Updates an existing team member.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: multipart/form-data` (for file upload) or `application/json`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the team member to update

**Form Data**:
```
first_name: "John" (optional, string, max 255 chars)
last_name: "Doe" (optional, string, max 255 chars)
role: "CEO & Founder" (optional, string, max 255 chars)
email: "john@mecarvi.com" (optional, email, max 255 chars)
picture: [file] (optional, image file: jpeg, png, jpg, gif, webp, max 2MB)
picture: "delete" or null (optional, to delete existing picture)
order: 1 (optional, integer, min 0)
```

**JSON Alternative**:
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "role": "CEO & Founder",
    "email": "john@mecarvi.com",
    "order": 1
}
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Team member updated successfully",
    "data": {
        "team_member": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "role": "CEO & Founder",
            "email": "john@mecarvi.com",
            "picture": "http://localhost:8000/storage/team-members/abc123.jpg",
            "order": 1,
            "created_at": "2024-01-20T10:30:00.000000Z",
            "updated_at": "2024-01-20T11:00:00.000000Z"
        }
    }
}
```

**Error Response (404 - Not Found)**:
```json
{
    "success": false,
    "message": "Team member not found."
}
```

---

### Delete Team Member Picture (Admin Only)

**Endpoint**: `DELETE /api/v1/team-members/{id}/field/{field}`

**Postman URL**: `http://localhost:8000/api/v1/team-members/1/field/picture`

**Method**: `DELETE`

**Description**: Deletes a specific field (picture) from a team member without deleting the entire member.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the team member
- `field` (string, required): The field to delete (currently only `picture` is allowed)

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Team member picture deleted successfully",
    "data": {
        "team_member": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "role": "CEO & Founder",
            "email": "john@mecarvi.com",
            "picture": null,
            "order": 1,
            "created_at": "2024-01-20T10:30:00.000000Z",
            "updated_at": "2024-01-20T11:15:00.000000Z"
        }
    }
}
```

**Error Response (400 - Invalid Field)**:
```json
{
    "success": false,
    "message": "Invalid field. Allowed fields: picture"
}
```

---

### Delete Team Member (Admin Only)

**Endpoint**: `DELETE /api/v1/team-members/{id}`

**Postman URL**: `http://localhost:8000/api/v1/team-members/1`

**Method**: `DELETE`

**Description**: Deletes a team member and its associated picture.

**Authentication**: Required (Bearer token)
**Authorization**: Super admin or editor roles

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the team member to delete

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Team member deleted successfully"
}
```

**Error Response (404 - Not Found)**:
```json
{
    "success": false,
    "message": "Team member not found."
}
```

---

## Available Fields for Team Members

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `first_name` | string | Yes | 255 | Team member's first name |
| `last_name` | string | Yes | 255 | Team member's last name |
| `role` | string | Yes | 255 | Team member's role or position |
| `email` | string | Yes | 255 | Team member's email address (must be valid email format) |
| `picture` | string | No | - | Team member's profile picture (image file path) |
| `order` | integer | No | - | Display order for sorting (default: auto-incremented) |
| `created_at` | timestamp | No | - | When the team member was created (auto-set) |
| `updated_at` | timestamp | No | - | When the record was last updated (auto-set) |

---

## Notes

- The team members GET endpoints are publicly accessible (no authentication required)
- All management endpoints (POST, PUT, DELETE) require admin authentication and proper authorization
- The API supports both form-data and JSON requests
- Picture files are stored in `storage/app/public/team-members/` directory
- Picture URL is automatically generated and returned in responses
- Supported picture formats: JPEG, PNG, JPG, GIF, WebP
- Maximum picture file size: 2MB (2048 KB)
- Picture files are automatically deleted when a team member is deleted or when picture is updated
- Team members are ordered by the `order` field (ascending)
- If `order` is not provided when creating, it will be auto-incremented from the maximum existing order
- All timestamps are in UTC format
- Email field is validated as a proper email format

---

## Contact Page Hero Section Management

### Overview
The Contact Page Hero Section API allows administrators to manage the hero section content displayed on the contact page. This includes the main heading, subheading, and descriptive text that appears at the top of the contact page.

### Endpoints

#### Get All Contact Page Hero Sections
```http
GET /api/v1/contact-page-hero-sections
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-hero-sections`

**Method**: `GET`

**Description**: Retrieves all active contact page hero sections.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Response**:
```json
{
    "success": true,
    "data": {
        "hero_sections": [
            {
                "id": 1,
                "heading": "Get in Touch With Us",
                "subheading": "We'd Love to Hear From You",
                "description": "Whether you have a question about our services, pricing, or anything else, our team is ready to answer all your questions.",
                "is_active": true,
                "created_at": "2024-01-20T21:55:54.000000Z",
                "updated_at": "2024-01-20T21:55:54.000000Z"
            }
        ]
    }
}
```

#### Get Specific Contact Page Hero Section
```http
GET /api/v1/contact-page-hero-sections/{id}
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-hero-sections/1`

**Method**: `GET`

**Description**: Retrieves a specific contact page hero section by ID.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the hero section

**Response**:
```json
{
    "success": true,
    "data": {
        "hero_section": {
            "id": 1,
            "heading": "Get in Touch With Us",
            "subheading": "We'd Love to Hear From You",
            "description": "Whether you have a question about our services, pricing, or anything else, our team is ready to answer all your questions.",
            "is_active": true,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Create Contact Page Hero Section
```http
POST /api/v1/contact-page-hero-sections
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-hero-sections`

**Method**: `POST`

**Description**: Creates a new contact page hero section.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: application/json`
- `Accept: application/json`

**Request Body**:
```json
{
    "heading": "Get in Touch With Us",
    "subheading": "We'd Love to Hear From You",
    "description": "Whether you have a question about our services, pricing, or anything else, our team is ready to answer all your questions.",
    "is_active": true
}
```

**Response**:
```json
{
    "success": true,
    "message": "Contact page hero section created successfully",
    "data": {
        "hero_section": {
            "id": 1,
            "heading": "Get in Touch With Us",
            "subheading": "We'd Love to Hear From You",
            "description": "Whether you have a question about our services, pricing, or anything else, our team is ready to answer all your questions.",
            "is_active": true,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Update Contact Page Hero Section
```http
PUT /api/v1/contact-page-hero-sections/{id}/update
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-hero-sections/1/update`

**Method**: `PUT`

**Description**: Updates an existing contact page hero section.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: application/json`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the hero section to update

**Request Body**:
```json
{
    "heading": "Contact Us Today",
    "subheading": "We're Here to Help",
    "description": "Our team is ready to assist you with any questions or concerns you may have.",
    "is_active": true
}
```

**Response**:
```json
{
    "success": true,
    "message": "Contact page hero section updated successfully",
    "data": {
        "hero_section": {
            "id": 1,
            "heading": "Contact Us Today",
            "subheading": "We're Here to Help",
            "description": "Our team is ready to assist you with any questions or concerns you may have.",
            "is_active": true,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T22:10:15.000000Z"
        }
    }
}
```

#### Delete Contact Page Hero Section
```http
DELETE /api/v1/contact-page-hero-sections/{id}
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-hero-sections/1`

**Method**: `DELETE`

**Description**: Deletes a contact page hero section.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the hero section to delete

**Response**:
```json
{
    "success": true,
    "message": "Contact page hero section deleted successfully"
}
```

### Data Schema

#### ContactPageHeroSection Model

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `id` | integer | No | - | Primary key (auto-increment) |
| `heading` | string | Yes | 255 | Main heading text for hero section |
| `subheading` | string | No | 500 | Subheading text below main heading |
| `description` | text | No | - | Detailed description text |
| `is_active` | boolean | No | - | Whether section is active (default: true) |
| `created_at` | timestamp | No | - | When the record was created (auto-set) |
| `updated_at` | timestamp | No | - | When the record was last updated (auto-set) |

### Validation Rules

#### Create/Update Validation
- `heading`: Required, string, max 255 characters
- `subheading`: Optional, string, max 500 characters  
- `description`: Optional, text
- `is_active`: Optional, boolean

### Error Responses

#### 403 Forbidden
```json
{
    "success": false,
    "message": "Unauthorized. Only admins can manage contact page hero sections."
}
```

#### 404 Not Found
```json
{
    "success": false,
    "message": "Contact page hero section not found."
}
```

#### 422 Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "heading": ["The heading field is required."]
    }
}
```

#### 500 Server Error
```json
{
    "success": false,
    "message": "Failed to create contact page hero section",
    "error": "Database error details"
}
```

### Notes

- Public endpoints (GET) do not require authentication
- Management endpoints (POST, PUT, DELETE) require admin authentication
- Only active sections are returned in the index endpoint
- The API supports real-time updates through WebSocket broadcasting
- All timestamps are in UTC format
- The `is_active` field allows for soft disabling of sections without deletion

---

## Contact Page Cards Management

### Overview
The Contact Page Cards API allows administrators to manage various contact information cards displayed on the contact page. This includes Call, Fax, Email, Visit, Store Hours, and Online Hours cards.

### Endpoints

#### Get All Contact Page Cards
```http
GET /api/v1/contact-page-cards
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-cards`

**Method**: `GET`

**Description**: Retrieves all active contact page cards.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Query Parameters**:
- `card_type` (optional): Filter by card type (`call`, `fax`, `email`, `visit`, `store_hours`, `online_hours`)
- `is_active` (optional): Filter by active status (default: true for public access)

**Response**:
```json
{
    "success": true,
    "data": {
        "contact_cards": [
            {
                "id": 1,
                "card_type": "call",
                "badge_title": "Call",
                "secondary_badge": null,
                "label": "Call Us",
                "phone_number_1": "+1 234 567 890",
                "phone_number_2": "+1 987 654 321",
                "fax_number": null,
                "email_address": null,
                "street_address": null,
                "state_postal_code": null,
                "country": null,
                "monday_friday_hours": null,
                "saturday_hours": null,
                "sunday_hours": null,
                "icon": "http://localhost:8000/storage/contact-cards/icon_phone.png",
                "is_active": true,
                "sort_order": 0,
                "created_at": "2024-01-20T21:55:54.000000Z",
                "updated_at": "2024-01-20T21:55:54.000000Z"
            },
            {
                "id": 2,
                "card_type": "email",
                "badge_title": "Email",
                "secondary_badge": "Team",
                "label": "Email Us",
                "phone_number_1": null,
                "phone_number_2": null,
                "fax_number": null,
                "email_address": "contact@mecarvi.com",
                "street_address": null,
                "state_postal_code": null,
                "country": null,
                "monday_friday_hours": null,
                "saturday_hours": null,
                "sunday_hours": null,
                "icon": "http://localhost:8000/storage/contact-cards/icon_email.png",
                "is_active": true,
                "sort_order": 1,
                "created_at": "2024-01-20T21:55:54.000000Z",
                "updated_at": "2024-01-20T21:55:54.000000Z"
            }
        ]
    }
}
```

#### Get Specific Contact Page Card
```http
GET /api/v1/contact-page-cards/{id}
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-cards/1`

**Method**: `GET`

**Description**: Retrieves a specific contact page card by ID.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the contact card

**Response**:
```json
{
    "success": true,
    "data": {
        "contact_card": {
            "id": 1,
            "card_type": "call",
            "badge_title": "Call",
            "label": "Call Us",
            "phone_number_1": "+1 234 567 890",
            "phone_number_2": "+1 987 654 321",
            "icon": "http://localhost:8000/storage/contact-cards/icon_phone.png",
            "is_active": true,
            "sort_order": 0,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Create Contact Page Card
```http
POST /api/v1/contact-page-cards
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-cards`

**Method**: `POST`

**Description**: Creates a new contact page card.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: application/json` (for JSON) or `multipart/form-data` (for file uploads)
- `Accept: application/json`

**Request Body** (Example for Call Card):
```json
{
    "card_type": "call",
    "badge_title": "Call",
    "label": "Call Us",
    "phone_number_1": "+1 234 567 890",
    "phone_number_2": "+1 987 654 321",
    "icon": "file_upload_or_base64_string",
    "is_active": true,
    "sort_order": 0
}
```

**Request Body** (Example for Email Card):
```json
{
    "card_type": "email",
    "badge_title": "Email",
    "secondary_badge": "Team",
    "label": "Email Us",
    "email_address": "contact@mecarvi.com",
    "icon": "file_upload_or_base64_string",
    "is_active": true,
    "sort_order": 1
}
```

**Request Body** (Example for Visit Card):
```json
{
    "card_type": "visit",
    "badge_title": "Visit",
    "label": "Address",
    "street_address": "123 Main St, City",
    "state_postal_code": "State 12345",
    "country": "Country",
    "icon": "file_upload_or_base64_string",
    "is_active": true,
    "sort_order": 2
}
```

**Request Body** (Example for Store Hours Card):
```json
{
    "card_type": "store_hours",
    "badge_title": "Store Hours",
    "label": "Hours of Operation",
    "monday_friday_hours": "9am - 6pm",
    "saturday_hours": "10am - 4pm",
    "sunday_hours": "Closed",
    "icon": "file_upload_or_base64_string",
    "is_active": true,
    "sort_order": 3
}
```

**Response**:
```json
{
    "success": true,
    "message": "Contact card created successfully",
    "data": {
        "contact_card": {
            "id": 1,
            "card_type": "call",
            "badge_title": "Call",
            "label": "Call Us",
            "phone_number_1": "+1 234 567 890",
            "phone_number_2": "+1 987 654 321",
            "icon": "http://localhost:8000/storage/contact-cards/icon_phone.png",
            "is_active": true,
            "sort_order": 0,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Update Contact Page Card
```http
PUT /api/v1/contact-page-cards/{id}
POST /api/v1/contact-page-cards/{id}/update
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-cards/1` (PUT) or `http://localhost:8000/api/v1/contact-page-cards/1/update` (POST)

**Method**: `PUT` or `POST`

**Description**: Updates an existing contact page card.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: application/json` (for JSON) or `multipart/form-data` (for file uploads)
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the contact card to update

**Request Body**: Same as create, but all fields are optional (use `sometimes` validation)

**Response**:
```json
{
    "success": true,
    "message": "Contact card updated successfully",
    "data": {
        "contact_card": {
            "id": 1,
            "card_type": "call",
            "badge_title": "Call",
            "label": "Call Us",
            "phone_number_1": "+1 234 567 890",
            "phone_number_2": "+1 987 654 321",
            "icon": "http://localhost:8000/storage/contact-cards/icon_phone.png",
            "is_active": true,
            "sort_order": 0,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T22:00:00.000000Z"
        }
    }
}
```

#### Delete Contact Page Card
```http
DELETE /api/v1/contact-page-cards/{id}
```

**Postman URL**: `http://localhost:8000/api/v1/contact-page-cards/1`

**Method**: `DELETE`

**Description**: Deletes a contact page card.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the contact card to delete

**Response**:
```json
{
    "success": true,
    "message": "Contact card deleted successfully"
}
```

### ContactCard Model

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `id` | integer | No | - | Primary key (auto-increment) |
| `card_type` | string | Yes | - | Card type: `call`, `fax`, `email`, `visit`, `store_hours`, `online_hours` |
| `badge_title` | string | No | 255 | Main badge/title text |
| `secondary_badge` | string | No | 255 | Secondary badge (for email card) |
| `label` | string | No | 255 | Label text |
| `phone_number_1` | string | No | 255 | First phone number (for call card) |
| `phone_number_2` | string | No | 255 | Second phone number (for call card) |
| `fax_number` | string | No | 255 | Fax number (for fax card) |
| `email_address` | string | No | 255 | Email address (for email card) |
| `street_address` | text | No | 500 | Street address (for visit card) |
| `state_postal_code` | string | No | 255 | State and postal code (for visit card) |
| `country` | string | No | 255 | Country (for visit card) |
| `monday_friday_hours` | string | No | 255 | Monday-Friday hours (for hours cards) |
| `saturday_hours` | string | No | 255 | Saturday hours (for hours cards) |
| `sunday_hours` | string | No | 255 | Sunday hours (for hours cards) |
| `icon` | string | No | - | Icon file path |
| `is_active` | boolean | No | - | Whether card is active (default: true) |
| `sort_order` | integer | No | - | Sort order for display (default: 0) |
| `created_at` | timestamp | No | - | When the record was created (auto-set) |
| `updated_at` | timestamp | No | - | When the record was last updated (auto-set) |

### Validation Rules

#### Create Validation
- `card_type`: Required, string, must be one of: `call`, `fax`, `email`, `visit`, `store_hours`, `online_hours`
- `badge_title`: Optional, string, max 255 characters
- `secondary_badge`: Optional, string, max 255 characters
- `label`: Optional, string, max 255 characters
- `icon`: Optional, image file (jpeg, png, jpg, gif, webp), max 2MB, or base64 string
- `is_active`: Optional, boolean
- `sort_order`: Optional, integer, min 0

**Type-Specific Fields**:
- **Call Card**: `phone_number_1` (optional, string, max 255), `phone_number_2` (optional, string, max 255)
- **Fax Card**: `fax_number` (optional, string, max 255)
- **Email Card**: `email_address` (optional, email format, max 255)
- **Visit Card**: `street_address` (optional, string, max 500), `state_postal_code` (optional, string, max 255), `country` (optional, string, max 255)
- **Store Hours / Online Hours Cards**: `monday_friday_hours` (optional, string, max 255), `saturday_hours` (optional, string, max 255), `sunday_hours` (optional, string, max 255)

#### Update Validation
- All fields are optional (use `sometimes` validation)
- Same validation rules as create

### Card Types

1. **Call Card** (`card_type: "call"`)
   - Fields: `badge_title`, `label`, `phone_number_1`, `phone_number_2`, `icon`

2. **Fax Card** (`card_type: "fax"`)
   - Fields: `badge_title`, `label`, `fax_number`, `icon`

3. **Email Card** (`card_type: "email"`)
   - Fields: `badge_title`, `secondary_badge`, `label`, `email_address`, `icon`

4. **Visit Card** (`card_type: "visit"`)
   - Fields: `badge_title`, `label`, `street_address`, `state_postal_code`, `country`, `icon`

5. **Store Hours Card** (`card_type: "store_hours"`)
   - Fields: `badge_title`, `label`, `monday_friday_hours`, `saturday_hours`, `sunday_hours`, `icon`

6. **Online Hours Card** (`card_type: "online_hours"`)
   - Fields: `badge_title`, `label`, `monday_friday_hours`, `saturday_hours`, `sunday_hours`, `icon`

### File Upload Details

- **Supported Formats**: JPEG, PNG, JPG, GIF, WebP
- **Maximum Size**: 2MB (2048 KB)
- **Storage Path**: `storage/app/public/contact-cards/`
- **Public URL**: `http://localhost:8000/storage/contact-cards/`
- **File Naming**: Automatically generated by Laravel
- **Base64 Support**: Icons can be uploaded as base64 encoded strings
- **Old icon files are automatically deleted when updated or card is deleted**

### Error Responses

#### 403 Forbidden
```json
{
    "success": false,
    "message": "Unauthorized. Only admins can manage contact cards."
}
```

#### 404 Not Found
```json
{
    "success": false,
    "message": "Contact card not found."
}
```

#### 422 Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "card_type": ["The card type field is required."],
        "email_address": ["The email address must be a valid email address."]
    }
}
```

#### 500 Server Error
```json
{
    "success": false,
    "message": "Contact card creation failed. Please try again.",
    "error": "Database error details"
}
```

### Notes

- Public endpoints (GET) do not require authentication
- Management endpoints (POST, PUT, DELETE) require admin authentication
- Only active cards are returned in the index endpoint by default (unless `is_active=false` is specified)
- Cards are ordered by `sort_order` (ascending), then by `created_at` (descending)
- The API supports filtering by `card_type` and `is_active` status
- Icon files can be uploaded as multipart/form-data files or base64 encoded strings
- The API supports real-time updates through WebSocket broadcasting
- All timestamps are in UTC format
- The `is_active` field allows for soft disabling of cards without deletion

---

## Hours of Operation Management

### Overview
The Hours of Operation API allows administrators to manage operational hours for different categories (Customer Care, Sales, Technical Support, etc.) displayed on the contact page.

### Endpoints

#### Get All Hours of Operation
```http
GET /api/v1/hours-of-operation
```

**Postman URL**: `http://localhost:8000/api/v1/hours-of-operation`

**Method**: `GET`

**Description**: Retrieves all active hours of operation categories.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Query Parameters**:
- `is_active` (optional): Filter by active status (default: true for public access)

**Response**:
```json
{
    "success": true,
    "data": {
        "section_title": "Hours of Operation",
        "hours_of_operation": [
            {
                "id": 1,
                "section_title": "Hours of Operation",
                "category_title": "Customer Care",
                "monday_friday_hours": "9am - 6pm",
                "saturday_hours": "10am - 4pm",
                "sunday_hours": null,
                "sunday_status": "Closed",
                "public_holidays_hours": null,
                "public_holidays_status": "Closed",
                "description_1": null,
                "description_2": null,
                "is_active": true,
                "sort_order": 0,
                "created_at": "2024-01-20T21:55:54.000000Z",
                "updated_at": "2024-01-20T21:55:54.000000Z"
            },
            {
                "id": 2,
                "section_title": "Hours of Operation",
                "category_title": "Sales",
                "monday_friday_hours": "9am - 6pm",
                "saturday_hours": "10am - 4pm",
                "sunday_hours": null,
                "sunday_status": "Closed",
                "public_holidays_hours": null,
                "public_holidays_status": "Closed",
                "description_1": null,
                "description_2": null,
                "is_active": true,
                "sort_order": 1,
                "created_at": "2024-01-20T21:55:54.000000Z",
                "updated_at": "2024-01-20T21:55:54.000000Z"
            }
        ]
    }
}
```

#### Get Specific Hours of Operation
```http
GET /api/v1/hours-of-operation/{id}
```

**Postman URL**: `http://localhost:8000/api/v1/hours-of-operation/1`

**Method**: `GET`

**Description**: Retrieves a specific hours of operation category by ID.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the hours of operation category

**Response**:
```json
{
    "success": true,
    "data": {
        "hours_of_operation": {
            "id": 1,
            "section_title": "Hours of Operation",
            "category_title": "Customer Care",
            "monday_friday_hours": "9am - 6pm",
            "saturday_hours": "10am - 4pm",
            "sunday_hours": null,
            "sunday_status": "Closed",
            "public_holidays_hours": null,
            "public_holidays_status": "Closed",
            "description_1": null,
            "description_2": null,
            "is_active": true,
            "sort_order": 0,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Create Hours of Operation
```http
POST /api/v1/hours-of-operation
```

**Postman URL**: `http://localhost:8000/api/v1/hours-of-operation`

**Method**: `POST`

**Description**: Creates a new hours of operation category.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: application/json`
- `Accept: application/json`

**Request Body**:
```json
{
    "section_title": "Hours of Operation",
    "category_title": "Customer Care",
    "monday_friday_hours": "9am - 6pm",
    "saturday_hours": "10am - 4pm",
    "sunday_hours": null,
    "sunday_status": "Closed",
    "public_holidays_hours": null,
    "public_holidays_status": "Closed",
    "description_1": null,
    "description_2": null,
    "is_active": true,
    "sort_order": 0
}
```

**Response**:
```json
{
    "success": true,
    "message": "Hours of operation created successfully",
    "data": {
        "hours_of_operation": {
            "id": 1,
            "section_title": "Hours of Operation",
            "category_title": "Customer Care",
            "monday_friday_hours": "9am - 6pm",
            "saturday_hours": "10am - 4pm",
            "sunday_hours": null,
            "sunday_status": "Closed",
            "public_holidays_hours": null,
            "public_holidays_status": "Closed",
            "description_1": null,
            "description_2": null,
            "is_active": true,
            "sort_order": 0,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Update Hours of Operation
```http
PUT /api/v1/hours-of-operation/{id}
POST /api/v1/hours-of-operation/{id}/update
```

**Postman URL**: `http://localhost:8000/api/v1/hours-of-operation/1` (PUT) or `http://localhost:8000/api/v1/hours-of-operation/1/update` (POST)

**Method**: `PUT` or `POST`

**Description**: Updates an existing hours of operation category.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: application/json`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the hours of operation category to update

**Request Body**: Same as create, but all fields are optional

**Response**:
```json
{
    "success": true,
    "message": "Hours of operation updated successfully",
    "data": {
        "hours_of_operation": {
            "id": 1,
            "section_title": "Hours of Operation",
            "category_title": "Customer Care",
            "monday_friday_hours": "9am - 6pm",
            "saturday_hours": "10am - 4pm",
            "sunday_hours": null,
            "sunday_status": "Closed",
            "public_holidays_hours": null,
            "public_holidays_status": "Closed",
            "description_1": null,
            "description_2": null,
            "is_active": true,
            "sort_order": 0,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T22:00:00.000000Z"
        }
    }
}
```

#### Delete Hours of Operation
```http
DELETE /api/v1/hours-of-operation/{id}
```

**Postman URL**: `http://localhost:8000/api/v1/hours-of-operation/1`

**Method**: `DELETE`

**Description**: Deletes a hours of operation category.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the hours of operation category to delete

**Response**:
```json
{
    "success": true,
    "message": "Hours of operation deleted successfully"
}
```

### HoursOfOperation Model

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `id` | integer | No | - | Primary key (auto-increment) |
| `section_title` | string | No | 255 | Main section title (e.g., "Hours of Operation") |
| `category_title` | string | Yes | 255 | Category name (e.g., "Customer Care", "Sales") |
| `monday_friday_hours` | string | No | 255 | Monday-Friday operating hours |
| `saturday_hours` | string | No | 255 | Saturday operating hours |
| `sunday_hours` | string | No | 255 | Sunday operating hours |
| `sunday_status` | string | No | 255 | Sunday status (e.g., "Closed") |
| `public_holidays_hours` | string | No | 255 | Public holidays operating hours |
| `public_holidays_status` | string | No | 255 | Public holidays status (e.g., "Closed") |
| `description_1` | text | No | - | Additional description field 1 |
| `description_2` | text | No | - | Additional description field 2 |
| `is_active` | boolean | No | - | Whether category is active (default: true) |
| `sort_order` | integer | No | - | Sort order for display (default: 0) |
| `created_at` | timestamp | No | - | When the record was created (auto-set) |
| `updated_at` | timestamp | No | - | When the record was last updated (auto-set) |

### Validation Rules

#### Create Validation
- `section_title`: Optional, string, max 255 characters
- `category_title`: Required, string, max 255 characters
- `monday_friday_hours`: Optional, string, max 255 characters
- `saturday_hours`: Optional, string, max 255 characters
- `sunday_hours`: Optional, string, max 255 characters
- `sunday_status`: Optional, string, max 255 characters
- `public_holidays_hours`: Optional, string, max 255 characters
- `public_holidays_status`: Optional, string, max 255 characters
- `description_1`: Optional, string
- `description_2`: Optional, string
- `is_active`: Optional, boolean
- `sort_order`: Optional, integer, min 0

#### Update Validation
- All fields are optional (use `sometimes` validation)
- Same validation rules as create

### Error Responses

#### 403 Forbidden
```json
{
    "success": false,
    "message": "Unauthorized. Only admins can manage hours of operation."
}
```

#### 404 Not Found
```json
{
    "success": false,
    "message": "Hours of operation not found."
}
```

#### 422 Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "category_title": ["The category title field is required."]
    }
}
```

#### 500 Server Error
```json
{
    "success": false,
    "message": "Hours of operation creation failed. Please try again.",
    "error": "Database error details"
}
```

### Notes

- Public endpoints (GET) do not require authentication
- Management endpoints (POST, PUT, DELETE) require admin authentication
- Only active categories are returned in the index endpoint by default (unless `is_active=false` is specified)
- Categories are ordered by `sort_order` (ascending), then by `created_at` (descending)
- The `section_title` is returned in the index response (taken from the first record)
- The API supports real-time updates through WebSocket broadcasting
- All timestamps are in UTC format
- The `is_active` field allows for soft disabling of categories without deletion
- Both `sunday_hours`/`sunday_status` and `public_holidays_hours`/`public_holidays_status` are available to support different display formats

---

## Social Media Management

### Overview
The Social Media API allows administrators to manage the social media section (heading and description) and individual social media links (Facebook, Instagram, Twitter, LinkedIn, YouTube, etc.) displayed on the contact page.

### Endpoints

#### Get Social Media Section and Links
```http
GET /api/v1/social-media
```

**Postman URL**: `http://localhost:8000/api/v1/social-media`

**Method**: `GET`

**Description**: Retrieves the social media section (heading and description) along with all active social links.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Query Parameters**:
- `is_active` (optional): Filter links by active status (default: true for public access)

**Response**:
```json
{
    "success": true,
    "data": {
        "social_media_section": {
            "id": 1,
            "heading": "Follow Us",
            "description": "Stay connected with us on social media platforms for the latest updates and news.",
            "is_active": true
        },
        "social_links": [
            {
                "id": 1,
                "platform_name": "Facebook",
                "platform_url": "https://facebook.com/example",
                "icon": "http://localhost:8000/storage/social-links/facebook_icon.png",
                "is_active": true,
                "sort_order": 0,
                "created_at": "2024-01-20T21:55:54.000000Z",
                "updated_at": "2024-01-20T21:55:54.000000Z"
            },
            {
                "id": 2,
                "platform_name": "Instagram",
                "platform_url": "https://instagram.com/example",
                "icon": "http://localhost:8000/storage/social-links/instagram_icon.png",
                "is_active": true,
                "sort_order": 1,
                "created_at": "2024-01-20T21:55:54.000000Z",
                "updated_at": "2024-01-20T21:55:54.000000Z"
            }
        ]
    }
}
```

#### Get Social Media Section Only
```http
GET /api/v1/social-media/section
```

**Postman URL**: `http://localhost:8000/api/v1/social-media/section`

**Method**: `GET`

**Description**: Retrieves only the social media section (heading and description).

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Response**:
```json
{
    "success": true,
    "data": {
        "social_media_section": {
            "id": 1,
            "heading": "Follow Us",
            "description": "Stay connected with us on social media platforms for the latest updates and news.",
            "is_active": true,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Get Specific Social Link
```http
GET /api/v1/social-links/{id}
```

**Postman URL**: `http://localhost:8000/api/v1/social-links/1`

**Method**: `GET`

**Description**: Retrieves a specific social link by ID.

**Authentication**: Not required (Public endpoint)

**Headers**:
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the social link

**Response**:
```json
{
    "success": true,
    "data": {
        "social_link": {
            "id": 1,
            "platform_name": "Facebook",
            "platform_url": "https://facebook.com/example",
            "icon": "http://localhost:8000/storage/social-links/facebook_icon.png",
            "is_active": true,
            "sort_order": 0,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Create or Update Social Media Section
```http
POST /api/v1/social-media/section
```

**Postman URL**: `http://localhost:8000/api/v1/social-media/section`

**Method**: `POST`

**Description**: Creates a new social media section or updates the existing one (only one section is allowed).

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: application/json`
- `Accept: application/json`

**Request Body**:
```json
{
    "heading": "Follow Us",
    "description": "Stay connected with us on social media platforms for the latest updates and news.",
    "is_active": true
}
```

**Response**:
```json
{
    "success": true,
    "message": "Social media section created successfully",
    "data": {
        "social_media_section": {
            "id": 1,
            "heading": "Follow Us",
            "description": "Stay connected with us on social media platforms for the latest updates and news.",
            "is_active": true,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Create Social Link
```http
POST /api/v1/social-links
```

**Postman URL**: `http://localhost:8000/api/v1/social-links`

**Method**: `POST`

**Description**: Creates a new social media link.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: application/json` (for JSON) or `multipart/form-data` (for file uploads)
- `Accept: application/json`

**Request Body**:
```json
{
    "platform_name": "Facebook",
    "platform_url": "https://facebook.com/example",
    "icon": "file_upload_or_base64_string",
    "is_active": true,
    "sort_order": 0
}
```

**Response**:
```json
{
    "success": true,
    "message": "Social link created successfully",
    "data": {
        "social_link": {
            "id": 1,
            "platform_name": "Facebook",
            "platform_url": "https://facebook.com/example",
            "icon": "http://localhost:8000/storage/social-links/facebook_icon.png",
            "is_active": true,
            "sort_order": 0,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T21:55:54.000000Z"
        }
    }
}
```

#### Update Social Link
```http
PUT /api/v1/social-links/{id}
POST /api/v1/social-links/{id}/update
```

**Postman URL**: `http://localhost:8000/api/v1/social-links/1` (PUT) or `http://localhost:8000/api/v1/social-links/1/update` (POST)

**Method**: `PUT` or `POST`

**Description**: Updates an existing social media link.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Content-Type: application/json` (for JSON) or `multipart/form-data` (for file uploads)
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the social link to update

**Request Body**: Same as create, but all fields are optional

**Response**:
```json
{
    "success": true,
    "message": "Social link updated successfully",
    "data": {
        "social_link": {
            "id": 1,
            "platform_name": "Facebook",
            "platform_url": "https://facebook.com/example",
            "icon": "http://localhost:8000/storage/social-links/facebook_icon.png",
            "is_active": true,
            "sort_order": 0,
            "created_at": "2024-01-20T21:55:54.000000Z",
            "updated_at": "2024-01-20T22:00:00.000000Z"
        }
    }
}
```

#### Delete Social Link
```http
DELETE /api/v1/social-links/{id}
```

**Postman URL**: `http://localhost:8000/api/v1/social-links/1`

**Method**: `DELETE`

**Description**: Deletes a social media link.

**Authentication**: Required (Admin only)

**Headers**:
- `Authorization: Bearer {token}`
- `Accept: application/json`

**Parameters**:
- `id` (integer, required): The ID of the social link to delete

**Response**:
```json
{
    "success": true,
    "message": "Social link deleted successfully"
}
```

### SocialMediaSection Model

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `id` | integer | No | - | Primary key (auto-increment) |
| `heading` | string | No | 255 | Section heading (e.g., "Follow Us") |
| `description` | text | No | - | Section description text |
| `is_active` | boolean | No | - | Whether section is active (default: true) |
| `created_at` | timestamp | No | - | When the record was created (auto-set) |
| `updated_at` | timestamp | No | - | When the record was last updated (auto-set) |

### SocialLink Model

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `id` | integer | No | - | Primary key (auto-increment) |
| `platform_name` | string | Yes | 255 | Platform name (e.g., "Facebook", "Instagram") |
| `platform_url` | string | Yes | 500 | Platform URL (must be valid URL) |
| `icon` | string | No | - | Icon file path |
| `is_active` | boolean | No | - | Whether link is active (default: true) |
| `sort_order` | integer | No | - | Sort order for display (default: 0) |
| `created_at` | timestamp | No | - | When the record was created (auto-set) |
| `updated_at` | timestamp | No | - | When the record was last updated (auto-set) |

### Validation Rules

#### Social Media Section
- `heading`: Optional, string, max 255 characters
- `description`: Optional, string
- `is_active`: Optional, boolean

#### Social Link - Create
- `platform_name`: Required, string, max 255 characters
- `platform_url`: Required, valid URL format, max 500 characters
- `icon`: Optional, image file (jpeg, png, jpg, gif, webp), max 2MB, or base64 string
- `is_active`: Optional, boolean
- `sort_order`: Optional, integer, min 0

#### Social Link - Update
- All fields are optional (use `sometimes` validation)
- Same validation rules as create

### File Upload Details

- **Supported Formats**: JPEG, PNG, JPG, GIF, WebP
- **Maximum Size**: 2MB (2048 KB)
- **Storage Path**: `storage/app/public/social-links/`
- **Public URL**: `http://localhost:8000/storage/social-links/`
- **File Naming**: Automatically generated by Laravel
- **Base64 Support**: Icons can be uploaded as base64 encoded strings
- **Old icon files are automatically deleted when updated or link is deleted**

### Error Responses

#### 403 Forbidden
```json
{
    "success": false,
    "message": "Unauthorized. Only admins can manage social media."
}
```

#### 404 Not Found
```json
{
    "success": false,
    "message": "Social link not found."
}
```

#### 422 Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "platform_name": ["The platform name field is required."],
        "platform_url": ["The platform url must be a valid URL."]
    }
}
```

#### 500 Server Error
```json
{
    "success": false,
    "message": "Social link creation failed. Please try again.",
    "error": "Database error details"
}
```

### Notes

- Public endpoints (GET) do not require authentication
- Management endpoints (POST, PUT, DELETE) require admin authentication
- Only active links are returned in the index endpoint by default (unless `is_active=false` is specified)
- Links are ordered by `sort_order` (ascending), then by `created_at` (descending)
- Only one social media section is allowed (create or update)
- Icon files can be uploaded as multipart/form-data files or base64 encoded strings
- The API supports real-time updates through WebSocket broadcasting
- All timestamps are in UTC format
- The `is_active` field allows for soft disabling of links without deletion

---
