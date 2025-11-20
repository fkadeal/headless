<?php

namespace App\Filament\Admin\Resources\CustomPostTypes\Pages;

use App\Filament\Admin\Resources\CustomPostTypes\CustomPostTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomPostType extends CreateRecord
{
    protected static string $resource = CustomPostTypeResource::class;
}
