# 1. Project Overview
- Project name: Ehgezly (إهجزلي)
- Purpose: Online platform for booking sports courts in Egypt
- Type: REST API Backend (Laravel 11)
- Target users: Court Owners, Customers, Admins

---

# 2. Tech Stack
- PHP 8.3
- Laravel 11.51.0
- MySQL
- Laravel Sanctum (API Authentication)
- Laravel Mail (Email notifications)
- Storage: Local public disk

---

# 3. System Roles

## Admin
- Who: Platform management users
- What they can do: Approve/reject owners, verify maincourts, manage payments, set commission, view dashboard stats
- Register/Login: Admin accounts are created by system/admin only, then login via API

## Court Owner (مالك الملعب)
- Who: Court owners who list their maincourts and courts
- What they can do: Create maincourts, add courts, manage bookings, upload images, set amenities and working hours, submit app payments
- Register/Login: Register with ownership proof, admin approval required, then login via API

## Customer (عميل)
- Who: End users who book courts and create open matches
- What they can do: Browse courts, create bookings, join open matches, pay and manage bookings
- Register/Login: Register and login via API, active status by default

---

# 4. Database Design

## users
- Purpose: Store system users and their roles
- Columns:
  - id: bigint
  - name: string
  - email: string (unique)
  - phone: string (unique)
  - password: string
  - role: enum('admin','customer','courtowner')
  - status: enum('active','suspended','pending','rejected')
  - remember_token: string nullable
  - timestamps
- Relationships:
  - hasOne customers
  - hasOne courtowners
  - hasMany notifications

## customers
- Purpose: Customer profile linked to users
- Columns:
  - id: bigint
  - user_id: foreign key -> users
  - can_book: boolean
  - timestamps
- Relationships:
  - belongsTo users
  - hasMany bookings
  - hasMany createdMatches (open_matches)
  - hasMany matchPlayers

## courtowners
- Purpose: Court owner profile linked to users
- Columns:
  - id: bigint
  - user_id: foreign key -> users
  - ownership_proof_url: string nullable
  - commission_percentage: decimal(5,2)
  - total_revenue: decimal(10,2)
  - app_due_amount: decimal(10,2)
  - remaining_balance: decimal(10,2)
  - timestamps
- Relationships:
  - belongsTo users
  - hasMany maincourts
  - hasMany owner_payments

## maincourts
- Purpose: Main court entities (centers)
- Columns:
  - id: bigint
  - owner_id: foreign key -> courtowners
  - name: string
  - description: text nullable
  - address: string
  - map_link: string nullable
  - latitude: decimal(10,8) nullable
  - longitude: decimal(11,8) nullable
  - status: enum('active','inactive','suspended')
  - is_verified: boolean
  - timestamps
- Relationships:
  - belongsTo courtowners
  - hasMany courts
  - hasMany payment_methods
  - hasMany working_hours
  - belongsToMany amenities (maincourt_amenities)
  - morphMany images

## courts
- Purpose: Individual playable courts in a maincourt
- Columns:
  - id: bigint
  - maincourt_id: foreign key -> maincourts
  - name: string
  - description: text nullable
  - type: enum('FIVE_A_SIDE','SIX_A_SIDE','SEVEN_A_SIDE','ELEVEN_A_SIDE')
  - surface_type: enum('grass','artificial_turf','cement')
  - price_per_hour: decimal(10,2)
  - status: enum('open','closed','maintenance')
  - is_open: boolean
  - timestamps
- Relationships:
  - belongsTo maincourts
  - hasMany timeslots
  - hasMany bookings
  - hasMany open_matches
  - morphMany images

## amenities
- Purpose: Master list of amenities
- Columns:
  - id: bigint
  - name: string
  - icon: string nullable
  - timestamps
- Relationships:
  - belongsToMany maincourts

## maincourt_amenities
- Purpose: Pivot table between maincourts and amenities
- Columns:
  - maincourt_id: foreign key -> maincourts
  - amenity_id: foreign key -> amenities
- Relationships:
  - belongsTo maincourts
  - belongsTo amenities

## payment_methods
- Purpose: Payment methods for a maincourt
- Columns:
  - id: bigint
  - maincourt_id: foreign key -> maincourts
  - type: enum('instapay','vodafone_cash','etisalat_cash','orange_cash','we_pay')
  - identifier: string
  - is_active: boolean
  - timestamps
- Relationships:
  - belongsTo maincourts
  - hasMany bookings

## working_hours
- Purpose: Weekly working hours for a maincourt
- Columns:
  - id: bigint
  - maincourt_id: foreign key -> maincourts
  - day_of_week: enum('saturday','sunday','monday','tuesday','wednesday','thursday','friday')
  - open_time: time
  - close_time: time
  - is_open: boolean
  - timestamps
- Relationships:
  - belongsTo maincourts

## timeslots
- Purpose: Hourly booking slots per court
- Columns:
  - id: bigint
  - court_id: foreign key -> courts
  - date: date
  - start_time: time
  - end_time: time
  - status: enum('available','booked','blocked','pending_match')
  - timestamps
- Relationships:
  - belongsTo courts
  - hasOne bookings

## bookings
- Purpose: Customer bookings
- Columns:
  - id: bigint
  - customer_id: foreign key -> customers
  - court_id: foreign key -> courts
  - timeslot_id: foreign key -> timeslots
  - payment_method_id: foreign key -> payment_methods
  - total_price: decimal(10,2)
  - receipt_image_url: string
  - status: enum('pending','confirmed','rejected','cancelled','completed')
  - rejection_reason: text nullable
  - timestamps
- Relationships:
  - belongsTo customers
  - belongsTo courts
  - belongsTo timeslots
  - belongsTo payment_methods

## images
- Purpose: Images for maincourts and courts (polymorphic)
- Columns:
  - id: bigint
  - imageable_id: bigint
  - imageable_type: string
  - url: string
  - is_primary: boolean
  - timestamps
- Relationships:
  - morphTo imageable

## notifications
- Purpose: In-app notifications for all roles
- Columns:
  - id: bigint
  - user_id: foreign key -> users
  - title: string
  - message: text
  - type: string nullable
  - notifiable_id: bigint
  - notifiable_type: string
  - is_read: boolean
  - timestamps
- Relationships:
  - belongsTo users
  - morphTo notifiable

## owner_payments
- Purpose: Owner payments to the app
- Columns:
  - id: bigint
  - owner_id: foreign key -> courtowners
  - amount: decimal(10,2)
  - payment_type: enum('instapay','vodafone_cash','etisalat_cash','orange_cash','we_pay')
  - receipt_image_url: string
  - notes: text nullable
  - status: enum('pending','approved','rejected')
  - rejection_reason: text nullable
  - timestamps
- Relationships:
  - belongsTo courtowners

## open_matches
- Purpose: Open match sessions created by customers
- Columns:
  - id: bigint
  - court_id: foreign key -> courts
  - timeslot_id: foreign key -> timeslots
  - creator_id: foreign key -> customers
  - booking_id: foreign key -> bookings nullable
  - name: string
  - description: text nullable
  - required_players: integer
  - current_players: integer
  - status: enum('waiting_players','ready_to_book','booking_pending','confirmed','cancelled')
  - timestamps
- Relationships:
  - belongsTo courts
  - belongsTo timeslots
  - belongsTo customers (creator)
  - belongsTo bookings
  - hasMany match_players

## match_players
- Purpose: Players list for an open match
- Columns:
  - id: bigint
  - match_id: foreign key -> open_matches
  - customer_id: foreign key -> customers
  - status: enum('joined','waitlisted','left')
  - joined_at: timestamp nullable
  - timestamps
- Relationships:
  - belongsTo open_matches
  - belongsTo customers

## password_reset_tokens
- Purpose: Password reset tokens (Laravel built-in)
- Columns:
  - email: string (primary)
  - token: string
  - created_at: timestamp nullable
- Note: Implemented as `password_resets` table in current schema

## personal_access_tokens
- Purpose: Sanctum API tokens
- Columns:
  - id: bigint
  - tokenable_id: bigint
  - tokenable_type: string
  - name: string
  - token: string(64)
  - abilities: text nullable
  - last_used_at: timestamp nullable
  - expires_at: timestamp nullable
  - timestamps

---

# 5. System Features

## Feature 1: Authentication System
- Register (customer / courtowner)
- Login
- Logout
- Role-based access control
- Status-based access control (pending/active/suspended/rejected)

## Feature 2: Court Owner Registration & Approval
- Owner registers with ownership proof
- Admin receives notification
- Admin approves/rejects
- Email sent to owner with decision
- Owner can only access dashboard if approved

## Feature 3: Maincourt Management (Owner)
- Add maincourt with details
- Upload multiple images
- Set amenities from fixed list
- Set working hours per day
- Add payment methods
- Admin verifies maincourt before it appears to customers

## Feature 4: Court Management (Owner)
- Add courts inside maincourt
- Court types: 5-a-side, 6-a-side, 7-a-side, 11-a-side
- Surface types: grass, artificial turf, cement
- Upload court images
- Set price per hour
- Open/close court

## Feature 5: Timeslot System
- Auto-generated based on working hours
- Generated on first customer request for that date
- Each slot = 1 hour
- Statuses: available, booked, blocked, pending_match

## Feature 6: Booking System
- Customer browses courts
- Selects timeslot
- Views payment methods
- Uploads receipt screenshot
- Owner approves/rejects
- Notifications at each step
- Booking statuses: pending, confirmed, rejected, cancelled, completed

## Feature 7: Location-Based Search
- Customer shares location (optional)
- API sorts courts by distance using Haversine Formula
- Shows distance in km or meters
- Works without location too (no sorting)

## Feature 8: Open Match System
- Customer creates open match on a court/timeslot
- Sets required players count
- Other customers can join or join waiting list
- When full -> creator gets notified to pay
- Creator pays -> booking sent to owner
- If timeslot booked by someone else -> match auto-cancelled
- If time passes without full players -> match auto-cancelled
- Waiting list system (auto-promote on leave)
- Scheduled command runs every 30 minutes

## Feature 9: Owner Financial System
- Commission percentage per owner (set by admin)
- Auto-calculated on each confirmed booking
- Owner views due amount
- Owner pays app via screenshot
- Admin approves/rejects payment
- Amount deducted from due balance

## Feature 10: Notification System
- In-app notifications for all roles
- Polymorphic (linked to any model)
- Mark as read / read all
- Unread count
- Triggered by: bookings, matches, payments, approvals

## Feature 11: Admin Dashboard
- Full platform statistics
- Manage court owners (approve/reject/suspend)
- Verify maincourts
- Manage owner payments
- Set commission per owner

## Feature 12: Owner Dashboard
- Statistics (bookings, revenue, due amount)
- Manage maincourts and courts
- Handle booking requests
- View financial details

---

# 6. API Architecture
- RESTful API design
- Prefix structure:
  - /api/auth -> public
  - /api/customer -> role:customer
  - /api/owner -> role:courtowner
  - /api/admin -> role:admin
- Response format:
```json
{
  "success": true,
  "message": "...",
  "data": {}
}
```
- HTTP status codes used:
  - 200 OK: Successful GET/PUT/DELETE
  - 201 Created: Successful POST
  - 401 Unauthorized: Missing/invalid token
  - 403 Forbidden: Role/status restriction
  - 404 Not Found: Resource not found
  - 409 Conflict: Business rule conflict
  - 422 Unprocessable Entity: Validation error

---

# 7. Security
- Laravel Sanctum token authentication
- Role-based middleware
- Status-based checks (pending/suspended/rejected blocked)
- Resource ownership verification
- Form Request validation on all inputs
- File upload validation (type, size)

---

# 8. Design Patterns Used
- Resource classes for API responses
- Form Request classes for validation
- Polymorphic relationships (images, notifications)
- Scheduled commands for background cleanup

---

# 9. Relationships Diagram (Text-based)
- User -> hasOne -> Courtowner -> hasMany -> Maincourt -> hasMany -> Court -> hasMany -> Timeslot -> hasOne -> Booking
- User -> hasOne -> Customer -> hasMany -> Booking
- User -> hasMany -> Notification
- Maincourt -> belongsToMany -> Amenity
- Maincourt -> hasMany -> PaymentMethod
- Maincourt -> hasMany -> WorkingHour
- Maincourt/Court -> morphMany -> Image
- Booking/OpenMatch -> morphMany -> Notification
- Courtowner -> hasMany -> OwnerPayment
- Court -> hasMany -> OpenMatch -> hasMany -> MatchPlayer

---

# 10. Possible Future Improvements
- Real-time notifications (Laravel Echo + Pusher)
- Online payment gateway integration (Fawry, Paymob)
- Mobile app (Flutter/React Native)
- Rating and review system
- Advanced search and filtering
- Recurring bookings
- Tournament system
