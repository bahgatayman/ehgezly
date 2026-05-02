<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateProfileRequest;
use App\Http\Resources\Customer\CustomerProfileResource;
use App\Models\Customer;
use App\Traits\HandlesFileUpload;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use HandlesFileUpload;

    public function show(Request $request)
    {
        $user = $request->user();
        $customer = $user?->customer;

        if (!$user || !$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'data' => null,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'الملف الشخصي',
            'data' => new CustomerProfileResource($customer->load('user')),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $customer = $user?->customer;

        if (!$user || !$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Forbiddensssss.',
                'data' => null,
            ], 403);
        }

        $data = $request->validated();

        $updates = [];
        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $updates['name'] = $data['name'];
        }

        if (array_key_exists('phone', $data) && $data['phone'] !== null) {
            $updates['phone'] = $data['phone'];
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                $this->deleteFile($user->profile_image);
            }

            $updates['profile_image'] = $this->uploadFile(
                $request->file('profile_image'),
                "profiles/{$user->id}"
            );
        }

        if (!empty($updates)) {
            $user->fill($updates)->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'الملف الشخصي',
            'data' => new CustomerProfileResource($customer->load('user')),
        ]);
    }
}