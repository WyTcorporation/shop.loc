<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->latest('id')
            ->get();

        return response()->json($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);

        $address = $request->user()->addresses()->create($data);

        return response()->json($address, 201);
    }

    public function show(Request $request, Address $address): JsonResponse
    {
        $address = $this->resolveAddress($request, $address);

        return response()->json($address);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $address = $this->resolveAddress($request, $address);

        $data = $this->validateData($request, partial: true);

        if (empty($data)) {
            return response()->json($address);
        }

        $address->update($data);

        return response()->json($address);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $address = $this->resolveAddress($request, $address);

        $address->delete();

        return response()->json(null, 204);
    }

    protected function resolveAddress(Request $request, Address $address): Address
    {
        if ($address->user_id !== $request->user()->id) {
            abort(404);
        }

        return $address;
    }

    protected function validateData(Request $request, bool $partial = false): array
    {
        $rules = [
            'name' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'city' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'addr' => [$partial ? 'sometimes' : 'required', 'string', 'max:500'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:32'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
        ];

        $data = $request->validate($rules);

        if ($partial) {
            $data = array_filter($data, fn ($value) => $value !== null);
        }

        return $data;
    }
}
