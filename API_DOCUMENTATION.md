# Ehgezly API Documentation

Base URL: /api

All responses follow:
```json
{
  "success": true,
  "message": "...",
  "data": {}
}
```

---

## Auth

### POST /api/auth/register
- **Description:** Register a new user (customer or courtowner). Note: currently implemented as `POST /api/signup`.
- **Auth:** No Auth
- **Role:** none
- **Headers:**
  - Accept: application/json
  - Content-Type: multipart/form-data

- **Request Body:**
```json
{
  "name": "string - required - user full name",
  "email": "string(email) - required - unique",
  "phone": "string - required - unique",
  "password": "string - required - min 6",
  "role": "string - required - customer|courtowner",
  "ownership_proof_url": "file - required if role=courtowner"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Registered",
  "data": {
    "user": {},
    "token": "..."
  }
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### POST /api/auth/login
- **Description:** Login and get Sanctum token. Note: currently implemented as `POST /api/login`.
- **Auth:** No Auth
- **Role:** none
- **Headers:**
  - Accept: application/json
  - Content-Type: application/json

- **Request Body:**
```json
{
  "email": "string(email) - required",
  "password": "string - required"
}
```
- **Success Response (2xx):**
```json
{
  "message": "Login success",
  "user": {},
  "token": "..."
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### POST /api/auth/logout
- **Description:** Logout and revoke token. Note: not explicitly defined; if present, implement token deletion. 
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer / courtowner / admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Logged out",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Customer - Maincourts

### GET /api/customer/maincourts
- **Description:** List maincourts with optional filters (search, amenities, day, latitude+longitude).
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "...",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/customer/maincourts/{id}
- **Description:** Get maincourt details.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourt retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Customer - Courts

### GET /api/customer/maincourts/{maincourt_id}/courts
- **Description:** List courts in a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Courts retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/customer/maincourts/{maincourt_id}/courts/{id}
- **Description:** Get court details in a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Court retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Customer - Timeslots

### GET /api/customer/courts/{court_id}/timeslots
- **Description:** List court timeslots (optionally filtered by date).
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Timeslots retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Customer - Bookings

### POST /api/customer/bookings
- **Description:** Create a booking.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: multipart/form-data

- **Request Body:**
```json
{
  "court_id": "integer - required - court id",
  "timeslot_id": "integer - required - timeslot id",
  "payment_method_id": "integer - required - payment method id",
  "receipt_image": "file - required - image"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Booking created.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/customer/bookings
- **Description:** List customer bookings (optional status filter).
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Bookings retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/customer/bookings/{id}
- **Description:** Get booking details.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Booking retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### DELETE /api/customer/bookings/{id}
- **Description:** Cancel a pending booking.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Booking cancelled.",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Customer - Open Matches

### POST /api/customer/matches
- **Description:** Create a new open match on a timeslot.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "court_id": "integer - required - court id",
  "timeslot_id": "integer - required - timeslot id",
  "name": "string - required - match name",
  "description": "string - optional - match description",
  "required_players": "integer - required - min 2, max 22"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Match created.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/customer/matches
- **Description:** Browse active matches (waiting_players, ready_to_book).
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Matches retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/customer/matches/{id}
- **Description:** Get match details, joined players, and auth status.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Match retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### POST /api/customer/matches/{id}/join
- **Description:** Join an open match.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "...",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### DELETE /api/customer/matches/{id}/leave
- **Description:** Leave a match (not the creator).
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "...",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### DELETE /api/customer/matches/{id}
- **Description:** Cancel a match (creator only).
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "...",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### POST /api/customer/matches/{id}/pay
- **Description:** Pay and create booking for a ready match (creator only).
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: multipart/form-data

- **Request Body:**
```json
{
  "payment_method_id": "integer - required - payment method id",
  "receipt_image": "file - required - image"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Match booking created.",
  "data": {
    "booking": {},
    "match": {}
  }
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Customer - Notifications

### GET /api/customer/notifications
- **Description:** List customer notifications.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Notifications retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/customer/notifications/{id}/read
- **Description:** Mark one notification as read.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Notification marked as read.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/customer/notifications/read-all
- **Description:** Mark all notifications as read.
- **Auth:** Bearer Token (Sanctum)
- **Role:** customer
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Notifications marked as read.",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Maincourts

### POST /api/owner/maincourts
- **Description:** Create a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "name": "string - required",
  "description": "string - optional",
  "address": "string - required",
  "map_link": "string(url) - optional",
  "latitude": "number - optional",
  "longitude": "number - optional"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourt created.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/maincourts
- **Description:** List owner maincourts.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourts retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/maincourts/{id}
- **Description:** Get maincourt details.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourt retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/owner/maincourts/{id}
- **Description:** Update a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "name": "string - optional",
  "description": "string - optional",
  "address": "string - optional",
  "map_link": "string(url) - optional",
  "latitude": "number - optional",
  "longitude": "number - optional"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourt updated.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### DELETE /api/owner/maincourts/{id}
- **Description:** Delete a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourt deleted.",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Courts

### POST /api/owner/maincourts/{maincourt_id}/courts
- **Description:** Create a court under a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "name": "string - required",
  "description": "string - optional",
  "type": "string - required - FIVE_A_SIDE|SIX_A_SIDE|SEVEN_A_SIDE|ELEVEN_A_SIDE",
  "surface_type": "string - required - grass|artificial_turf|cement",
  "price_per_hour": "number - required"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Court created.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/maincourts/{maincourt_id}/courts
- **Description:** List courts under a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Courts retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/maincourts/{maincourt_id}/courts/{id}
- **Description:** Get a court under a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Court retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/owner/maincourts/{maincourt_id}/courts/{id}
- **Description:** Update a court.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "name": "string - optional",
  "description": "string - optional",
  "type": "string - optional - FIVE_A_SIDE|SIX_A_SIDE|SEVEN_A_SIDE|ELEVEN_A_SIDE",
  "surface_type": "string - optional - grass|artificial_turf|cement",
  "price_per_hour": "number - optional"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Court updated.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### DELETE /api/owner/maincourts/{maincourt_id}/courts/{id}
- **Description:** Delete a court.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Court deleted.",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Images

### POST /api/owner/maincourts/{id}/images
- **Description:** Upload images for a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: multipart/form-data

- **Request Body:**
```json
{
  "images": "array(file) - required - up to 10",
  "primary_index": "integer - optional - index of primary image"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Images uploaded.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### POST /api/owner/maincourts/{maincourt_id}/courts/{id}/images
- **Description:** Upload images for a court.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: multipart/form-data

- **Request Body:**
```json
{
  "images": "array(file) - required - up to 10",
  "primary_index": "integer - optional - index of primary image"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Images uploaded.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### DELETE /api/owner/images/{image_id}
- **Description:** Delete an image (maincourt or court).
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Image deleted.",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Amenities

### GET /api/owner/amenities
- **Description:** List amenities.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Amenities retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### POST /api/owner/maincourts/{id}/amenities
- **Description:** Sync amenities for a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "amenity_ids": "array(integer) - required - amenity ids"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Amenities synced.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Working Hours

### POST /api/owner/maincourts/{id}/working-hours
- **Description:** Set working hours for a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "hours": "array - required",
  "hours.*.day_of_week": "string - required - saturday..friday",
  "hours.*.is_open": "boolean - required",
  "hours.*.open_time": "string(HH:mm) - required if is_open=true",
  "hours.*.close_time": "string(HH:mm) - required if is_open=true"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Working hours created.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/maincourts/{id}/working-hours
- **Description:** List working hours for a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Working hours retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Payment Methods

### POST /api/owner/maincourts/{id}/payment-methods
- **Description:** Create a payment method for a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "type": "string - required - instapay|vodafone_cash|etisalat_cash|orange_cash|we_pay",
  "identifier": "string - required",
  "is_active": "boolean - optional"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment method created.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/maincourts/{id}/payment-methods
- **Description:** List payment methods for a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment methods retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/owner/payment-methods/{id}
- **Description:** Update a payment method.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "type": "string - optional - instapay|vodafone_cash|etisalat_cash|orange_cash|we_pay",
  "identifier": "string - optional",
  "is_active": "boolean - optional"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment method updated.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### DELETE /api/owner/payment-methods/{id}
- **Description:** Delete a payment method.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment method deleted.",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Bookings

### GET /api/owner/bookings
- **Description:** List bookings for owner courts (filters: status, court_id, maincourt_id, date).
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Bookings retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/bookings/{id}
- **Description:** Get booking details.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Booking retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/owner/bookings/{id}/confirm
- **Description:** Confirm a booking.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Booking confirmed.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/owner/bookings/{id}/reject
- **Description:** Reject a booking.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "rejection_reason": "string - required - min 10"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Booking rejected.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/owner/bookings/{id}/complete
- **Description:** Mark booking as completed.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Booking completed.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Financials

### GET /api/owner/financials
- **Description:** Get owner financial overview.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Financial overview retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/app-payment-info
- **Description:** Get static app payment info and due amount.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "...",
  "data": {
    "app_due_amount": 0,
    "payment_methods": []
  }
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### POST /api/owner/payments
- **Description:** Submit a payment to the app.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: multipart/form-data

- **Request Body:**
```json
{
  "amount": "number - required",
  "payment_type": "string - required - instapay|vodafone_cash|etisalat_cash|orange_cash|we_pay",
  "receipt_image": "file - required - image",
  "notes": "string - optional"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment submitted.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/payments
- **Description:** List owner payments (optional status filter).
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payments retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/owner/payments/{id}
- **Description:** Get payment details.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### DELETE /api/owner/payments/{id}
- **Description:** Delete a pending payment.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment deleted.",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Dashboard

### GET /api/owner/dashboard
- **Description:** Get owner dashboard stats.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Dashboard retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Owner - Notifications

### GET /api/owner/notifications
- **Description:** List owner notifications.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Notifications retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/owner/notifications/{id}/read
- **Description:** Mark one notification as read.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Notification marked as read.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/owner/notifications/read-all
- **Description:** Mark all notifications as read.
- **Auth:** Bearer Token (Sanctum)
- **Role:** courtowner
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Notifications marked as read.",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Admin - Dashboard

### GET /api/admin/dashboard
- **Description:** Get admin dashboard stats.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Dashboard retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Admin - Owners

### GET /api/admin/owners
- **Description:** List owners (optional status filter).
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Owners retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/admin/owners/{id}
- **Description:** Get owner details.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Owner retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/owners/{id}/approve
- **Description:** Approve owner registration.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Owner approved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/owners/{id}/reject
- **Description:** Reject owner registration.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "rejection_reason": "string - required - min 10"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Owner rejected.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/owners/{id}/suspend
- **Description:** Suspend an owner.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "suspension_reason": "string - required - min 10"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Owner suspended.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/owners/{id}/activate
- **Description:** Activate an owner.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Owner activated.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/owners/{id}/commission
- **Description:** Update owner commission percentage.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "commission_percentage": "number - required - 0..100"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Commission updated.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Admin - Maincourts

### GET /api/admin/maincourts
- **Description:** List maincourts (optional status/is_verified filters).
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourts retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/admin/maincourts/{id}
- **Description:** Get maincourt details.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourt retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/maincourts/{id}/verify
- **Description:** Verify a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourt verified.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/maincourts/{id}/suspend
- **Description:** Suspend a maincourt.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "suspension_reason": "string - required - min 10"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Maincourt suspended.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Admin - Owner Payments

### GET /api/admin/owner-payments
- **Description:** List owner payment submissions (optional status/owner_id filters).
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Owner payments retrieved.",
  "data": []
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### GET /api/admin/owner-payments/{id}
- **Description:** Get owner payment details.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/owner-payments/{id}/approve
- **Description:** Approve an owner payment.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment approved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/owner-payments/{id}/reject
- **Description:** Reject an owner payment.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}
  - Content-Type: application/json

- **Request Body:**
```json
{
  "rejection_reason": "string - required - min 10"
}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Payment rejected.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

## Admin - Notifications

### GET /api/admin/notifications
- **Description:** List admin notifications.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Notifications retrieved.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/notifications/{id}/read
- **Description:** Mark one admin notification as read.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Notification marked as read.",
  "data": {}
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---

### PUT /api/admin/notifications/read-all
- **Description:** Mark all admin notifications as read.
- **Auth:** Bearer Token (Sanctum)
- **Role:** admin
- **Headers:**
  - Accept: application/json
  - Authorization: Bearer {token}

- **Request Body:**
```json
{}
```
- **Success Response (2xx):**
```json
{
  "success": true,
  "message": "Notifications marked as read.",
  "data": null
}
```
- **Error Responses:**
  | Status | Reason |
  |--------|--------|
  | 422 | Validation error |
  | 403 | Unauthorized |
  | 404 | Not found |
  | 409 | Conflict |

---
