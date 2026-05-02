---

## File Upload URL Normalization - May 2, 2026

### What was built:
- Centralized file upload/delete helpers to return absolute asset URLs.
- Forced HTTPS scheme when running behind proxies like ngrok.
- Updated upload and delete flows to use absolute URLs across API responses.

### Files Created:
- app/Traits/HandlesFileUpload.php -> Helper trait for upload and delete with absolute asset URLs.

### Files Modified:
- app/Providers/AppServiceProvider.php -> Force HTTPS scheme when in production or behind a proxy.
- app/Http/Controllers/Api/AuthController.php -> Use helper for ownership proof upload URLs.
- app/Http/Controllers/Api/Owner/ImageController.php -> Use helper for image upload and delete URLs.
- app/Http/Controllers/Api/Customer/BookingController.php -> Use helper for receipt upload URLs.
- app/Http/Controllers/Api/Owner/OwnerPaymentController.php -> Use helper for receipt upload and delete URLs.

### Database Changes:
- None.

### Problems Solved:
- Relative file URLs broke with ngrok -> Switched to absolute URLs using `asset()`.
- Mixed HTTP/HTTPS URLs behind proxy -> Forced HTTPS when proxy headers are present.

### How to test:
- POST /api/signup with ownership_proof_url and verify returned URL is absolute.
- POST /api/owner/maincourts/{id}/images and /api/owner/maincourts/{maincourt_id}/courts/{id}/images and verify image URLs are absolute.
- POST /api/customer/bookings with receipt_image and verify receipt_image_url is absolute.
- POST /api/owner/payments with receipt_image and verify receipt_image_url is absolute.
- If testing via ngrok, set APP_URL to the ngrok base and restart the server.

---

## Chunk G - Booking Count - May 2, 2026

### What was built:
- Customer profile endpoint with booking statistics.

### Files Created:
- app/Http/Controllers/Api/Customer/ProfileController.php -> profile endpoint.
- app/Http/Resources/Customer/CustomerProfileResource.php -> profile resource.

### Files Modified:
- app/Models/Customer.php -> added booking count helper methods.
- routes/api.php -> added GET /api/customer/profile route.

### Database Changes:
- None.

### Problems Solved:
- None.

### How to test:
- GET /api/customer/profile with customer token.
- Should return booking stats.

## Chunk G - Profile + Booking Count - May 2, 2026

### What was built:
- Customer profile endpoint (view + update).
- Profile image upload (optional).
- Booking stats added to profile and booking list.

### Files Created:
- database/migrations/2026_05_02_000001_add_profile_image_to_users_table.php -> adds profile_image column.
- app/Http/Controllers/Api/Customer/ProfileController.php -> profile endpoint.
- app/Http/Requests/Customer/UpdateProfileRequest.php -> profile update validation.
- app/Http/Resources/Customer/CustomerProfileResource.php -> profile resource.

### Files Modified:
- app/Models/User.php -> added profile_image to fillable.
- app/Models/Customer.php -> added booking count helper methods.
- app/Http/Controllers/Api/Customer/BookingController.php -> added booking stats to index response.
- routes/api.php -> added profile routes.

### Database Changes:
- Added profile_image column to users table (nullable).

### Problems Solved:
- None.

### How to test:
- GET /api/customer/profile -> should return user data + stats.
- PUT /api/customer/profile with name/phone/image -> should update.
- GET /api/customer/bookings -> should include stats in response.

## Chunk H - Rating System - May 2, 2026

### What was built:
- Rating system for maincourts.
- Customer can rate maincourt (1-5 stars) + comment.
- Customer can update or delete their rating.
- Ratings visible on maincourt details.

### Files Created:
- database/migrations/2026_05_02_000002_create_maincourt_ratings_table.php -> maincourt ratings table.
- app/Models/MaincourtRating.php -> rating model.
- app/Http/Controllers/Api/Customer/RatingController.php -> rating endpoints.
- app/Http/Requests/Customer/StoreRatingRequest.php -> rating validation.
- app/Http/Resources/Customer/RatingResource.php -> rating resource.
- app/Http/Resources/Customer/MaincourtRatingsResource.php -> ratings list resource.

### Files Modified:
- app/Models/Maincourt.php -> added ratings relationship + averageRating + ratingsCount.
- app/Models/Customer.php -> added ratings relationship + hasRatedMaincourt + hasCompletedBookingAt.
- app/Http/Controllers/Api/Customer/MaincourtController.php -> added rating data to responses.
- app/Http/Resources/Customer/MaincourtResource.php -> added average_rating + ratings_count.
- routes/api.php -> added rating routes.

### Database Changes:
- Created maincourt_ratings table.
- UNIQUE constraint on (maincourt_id, customer_id).

### Problems Solved:
- None.

### How to test:
- POST /api/customer/maincourts/{id}/rate with rating + comment.
- GET /api/customer/maincourts/{id}/ratings.
- DELETE /api/customer/maincourts/{id}/rate.
- GET /api/customer/maincourts -> should show average_rating.
- GET /api/customer/maincourts/{id} -> should show ratings details.
