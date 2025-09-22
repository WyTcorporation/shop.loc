<?php

namespace App\Filament\Mine\Resources\Products\Pages\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

trait ValidatesCategoryAccess
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function ensureCategoryIsPermitted(array $data): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $permittedCategoryIds = $user->permittedCategoryIds();

        if ($permittedCategoryIds->isEmpty()) {
            return;
        }

        if (! array_key_exists('category_id', $data)) {
            return;
        }

        $categoryId = $data['category_id'];

        if ($categoryId === null) {
            return;
        }

        if (! $permittedCategoryIds->contains((int) $categoryId)) {
            throw ValidationException::withMessages([
                'category_id' => __('validation.in', [
                    'attribute' => __('shop.products.fields.category'),
                ]),
            ]);
        }
    }
}
