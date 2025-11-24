<?php

namespace App\Filament\Admin\Resources\CPT\Pages;

use App\Filament\Admin\Resources\CPT\CPTResource;
use App\Filament\Admin\Resources\CPT\Schemas\CPTForm;
use App\Models\Category;
use App\Services\CurrentCPT;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCPT extends CreateRecord
{
    protected static string $resource = CPTResource::class;

    public function mount(): void
    {
        // Resolve once from route or query string
        $postType = request()->route('post_type')
            ?? request()->query('post_type');

        // Store in service
        if (isset($postType)) {
            CurrentCPT::set($postType);
        }
    }

    /**
     * Ensure defaults for post_type, created_by, and category_id.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set post_type from URL, fallback to 'post'
        $data['post_type'] = CurrentCPT::get();

        // Created By (fallback to current user)
        $data['created_by'] ??= auth()->id();

        // Ensure category exists
        $data['category_id'] ??= $this->resolveDefaultCategory();

        $data['content'] ??= 'oda';


        return $data;
    }


    /**
     * Actually creates the record + handles meta_data saving.
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Separate meta_data from main record fields
        $meta = $data['meta_data'] ?? [];
        unset($data['meta_data']);

        $post = static::getModel()::create($data);

        // Save custom meta fields (if any)
        foreach ($meta as $key => $value) {
            $post->setMeta($key, $value);
        }

        return $post;
    }

    /**
     * Resolve or create a 'General' fallback category.
     */
    private function resolveDefaultCategory(): int
    {
        $cat = Category::first();

        if ($cat) {
            return $cat->id;
        }

        // Auto-create default category if system is empty
        return Category::create([
            'name' => 'General',
            'slug' => 'general',
            'description' => 'Default category for posts',
        ])->id;
    }
}
