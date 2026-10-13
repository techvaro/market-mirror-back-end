<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VendorOnboardingRequest;
use App\Http\Resources\VendorApplicationResource;
use App\Models\VendorApplication;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VendorOnboardingController extends Controller
{
    public function __construct(private readonly UploadService $uploadService) {}

    public function show(Request $request)
    {
        abort_unless($request->user()->role === 'vendor', 403);

        return response()->json([
            'success' => true,
            'data' => VendorApplicationResource::make(
                $request->user()->vendorApplication
            ),
        ]);
    }

    public function store(VendorOnboardingRequest $request)
    {
        $user = $request->user();
        abort_unless($user->role === 'vendor', 403);

        $application = $user->vendorApplication;

        if ($application?->status === 'Approved') {
            return response()->json([
                'success' => true,
                'data' => new VendorApplicationResource($application),
                'message' => 'Your vendor application has already been approved.',
            ]);
        }

        $data = $request->sanitized();
        unset($data['id_file'], $data['password'], $data['password_confirmation']);

        if ($request->hasFile('id_file')) {
            $data['id_document_path'] = $this->uploadService->store($request->file('id_file'), 'vendor-ids');
        }

        $application ??= new VendorApplication(['user_id' => $user->id]);
        $application->fill([
            ...$data,
            'user_id' => $user->id,
            'status' => 'Pending Review',
        ]);
        $application->save();

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->string('password')->toString())]);
        }
        $user->update(['status' => 'pending']);

        return response()->json([
            'success' => true,
            'data' => new VendorApplicationResource($application),
            'message' => 'Your vendor application was submitted for approval.',
        ], 201);
    }
}
