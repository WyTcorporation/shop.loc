<?php

namespace App\Filament\Mine\Resources\Users\Pages;

use App\Filament\Mine\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
