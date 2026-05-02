<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreRatingRequest;
use App\Http\Resources\Customer\MaincourtRatingsResource;
use App\Http\Resources\Customer\RatingResource;
use App\Models\Customer;
use App\Models\Maincourt;
use App\Models\MaincourtRating;
use Illuminate\Http\JsonResponse;

class RatingController extends Controller
{
    public function index(int $id): JsonResponse
    {
        $maincourt = Maincourt::where('status', 'active')
            ->where('is_verified', true)
            ->find($id);

        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        $customer = Customer::where('user_id', auth()->id())->first();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $myRating = $customer->ratings()
            ->where('maincourt_id', $maincourt->id)
            ->first();

        $ratings = MaincourtRating::where('maincourt_id', $maincourt->id)
            ->with('customer.user')
            ->orderByDesc('created_at')
            ->paginate(10);

        $data = new MaincourtRatingsResource([
            'average_rating' => $maincourt->averageRating(),
            'ratings_count' => $maincourt->ratingsCount(),
            'my_rating' => $myRating ? [
                'rating' => $myRating->rating,
                'comment' => $myRating->comment,
                'created_at' => $myRating->created_at?->toDateString(),
            ] : null,
            'ratings' => RatingResource::collection($ratings->items()),
            'pagination' => [
                'current_page' => $ratings->currentPage(),
                'last_page' => $ratings->lastPage(),
                'per_page' => $ratings->perPage(),
                'total' => $ratings->total(),
            ],
        ]);

        return $this->successResponse('تقييمات الملعب', $data);
    }

    public function store(StoreRatingRequest $request, int $id): JsonResponse
    {
        $maincourt = Maincourt::where('status', 'active')
            ->where('is_verified', true)
            ->find($id);

        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        $customer = Customer::where('user_id', auth()->id())->first();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if (!$customer->hasCompletedBookingAt($maincourt->id)) {
            return $this->errorResponse('يجب أن تكون قد أكملت حجزاً في هذا الملعب لتتمكن من التقييم', 403);
        }

        $rating = MaincourtRating::updateOrCreate(
            [
                'maincourt_id' => $maincourt->id,
                'customer_id' => $customer->id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        $message = $rating->wasRecentlyCreated ? 'تم إضافة تقييمك بنجاح' : 'تم تحديث تقييمك بنجاح';

        return $this->successResponse($message, [
            'rating' => $rating->rating,
            'comment' => $rating->comment,
            'created_at' => $rating->created_at?->toDateString(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $maincourt = Maincourt::where('status', 'active')
            ->where('is_verified', true)
            ->find($id);

        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        $customer = Customer::where('user_id', auth()->id())->first();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $rating = MaincourtRating::where('maincourt_id', $maincourt->id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$rating) {
            return $this->errorResponse('لم تقم بتقييم هذا الملعب بعد', 404);
        }

        $rating->delete();

        return $this->successResponse('تم حذف تقييمك بنجاح');
    }

    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $status);
    }
}
