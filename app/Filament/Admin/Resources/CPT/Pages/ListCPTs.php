<?php

namespace App\Filament\Admin\Resources\CPT\Pages;

use App\Filament\Admin\Resources\CPT\CPTResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListCPTs extends ListRecords
{
    protected static string $resource = CPTResource::class;

    public string $postType = '';

    public function mount(): void
    {
        parent::mount();

        // Capture the post_type from the query string
        $this->postType = request()->query('post_type', '');
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->postType) {
            $query->where('post_type', $this->postType);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        $createAction = CreateAction::make();

        if ($this->postType) {
            // Append post_type as query so the Create page knows which type to prefill
            $createAction->url(
                fn() => CPTResource::getUrl('create', ['post_type' => $this->postType])
            );
        }

        return [
            $createAction,
        ];
    }

    // protected function getHeaderActions(): array
    // {
    //     // Get the post_type from the query string to preserve it when creating
    //     $postType = request()->query('post_type');

    //     $createAction = CreateAction::make();

    //     // Only modify the action if there's a post_type in the query
    //     if ($postType) {
    //         $createAction->url(
    //             fn() => route('filament.admin.resources.cpt.create', ['post_type' => $postType])
    //         );
    //     }

    //     return [
    //         $createAction,
    //     ];
    // }
}
