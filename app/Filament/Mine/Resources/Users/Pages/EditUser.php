<?php

namespace App\Filament\Mine\Resources\Users\Pages;

use App\Filament\Mine\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
}
