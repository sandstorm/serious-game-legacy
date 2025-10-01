<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CourseResource\Pages;

use App\Filament\Admin\Resources\CourseResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class ShowCourse extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CourseResource::class;

    protected static string $view = 'filament.admin.resources.course-resource.pages.show-course';


    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Spiele erstellen')
                ->modalHeading('TODO'),
        ];
    }
}
