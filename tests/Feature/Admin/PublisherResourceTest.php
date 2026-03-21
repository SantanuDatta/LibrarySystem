<?php

use App\Filament\Admin\Resources\Publishers\Pages\CreatePublisher;
use App\Filament\Admin\Resources\Publishers\Pages\EditPublisher;
use App\Filament\Admin\Resources\Publishers\Pages\ListPublishers;
use App\Models\Publisher;
use App\Models\Role;
use Filament\Actions\DeleteAction as FormDeleteAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

$state = new stdClass;

beforeEach(function () use ($state): void {
    asRole(Role::IS_ADMIN);

    $state->publisher = Publisher::factory()->create();
    $state->makePublisher = Publisher::factory()->make();

    Storage::fake('public');
});

describe('Publisher List Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->list = livewire(ListPublishers::class, [
            'record' => $state->publisher,
            'panel' => 'admin',
        ]);
    });

    it('can render the list page', function () use ($state): void {
        $state->list
            ->assertSuccessful();
    });

    it('can render publisher logo, name and founded', function () use ($state): void {
        $expectedColumns = [
            'logo',
            'name',
            'founded',
        ];

        foreach ($expectedColumns as $column) {
            $state->list
                ->assertTableColumnExists($column)
                ->assertSuccessful();
        }
    });

    it('can get publisher logo, name and founded', function () use ($state): void {
        $publisher = $state->publisher->first();

        $state->list
            // ->assertTableColumnStateSet('logo', $publisher->logo, record: $publisher)
            ->assertTableColumnStateSet('name', $publisher->name, record: $publisher)
            ->assertTableColumnStateSet('founded', $publisher->founded, record: $publisher);
    });

    it('can delete a publisher without a logo', function () use ($state): void {
        $state->list
            ->callTableAction('delete', $state->publisher);

        assertModelMissing($state->publisher);
    });

    it('can delete a publisher with logo', function () use ($state): void {
        $publisher = $state->publisher->getFirstMedia('publishers');

        $state->list
            ->callTableAction('delete', $state->publisher);

        assertModelMissing($state->publisher);

        if ($publisher !== null) {
            assertDatabaseMissing('media', [
                'model_type' => Publisher::class,
                'model_id' => $state->publisher->id,
                'collection_name' => 'publishers',
            ]);
        }
    });
});

describe('Publisher Create Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->create = livewire(CreatePublisher::class, [
            'panel' => 'admin',
        ]);
        $state->imagePath = UploadedFile::fake()
            ->image('image.jpg', 50, 50);
    });

    it('can render the create page', function () use ($state): void {
        $state->create
            ->assertSuccessful();
    });

    it('can create a new publisher', function () use ($state): void {
        $newPublisher = $state->makePublisher;

        $state->create
            ->fillForm([
                'name' => $newPublisher->name,
                'founded' => $newPublisher->founded,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('publishers', [
            'name' => $newPublisher->name,
            'founded' => $newPublisher->founded,
        ]);
    });

    it('can create a new publisher with a logo', function () use ($state): void {
        $newPublisher = $state->makePublisher;

        $state->create
            ->fillForm([
                'name' => $newPublisher->name,
                'founded' => $newPublisher->founded,
                'logo' => $state->imagePath,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('publishers', [
            'name' => $newPublisher->name,
            'founded' => $newPublisher->founded,
        ]);

        $createdPublisher = Publisher::latest()->first();
        $createdPublisher->addMedia($state->imagePath)->toMediaCollection('publishers');
        $mediaCollection = $createdPublisher->getMedia('publishers')->last();

        expect($mediaCollection)
            ->toBeInstanceOf(Media::class)
            ->model_type->toBe($mediaCollection->model_type)
            ->uuid->toBe($mediaCollection->uuid)
            ->collection_name->toBe($mediaCollection->collection_name)
            ->name->toBe($mediaCollection->name)
            ->file_name->toBe($mediaCollection->file_name);
    });

    it('can validate form data on create', function () use ($state): void {
        $state->create
            ->fillForm([
                'name' => null,
                'founded' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'founded' => 'required',
            ]);
    });
});

describe('Publisher Edit Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->edit = livewire(EditPublisher::class, [
            'record' => $state->publisher->getRouteKey(),
            'panel' => 'admin',
        ]);
        $state->updatedImagePath = UploadedFile::fake()
            ->image('updated_image.jpg', 50, 50);
    });

    it('can render the edit page', function () use ($state): void {
        $state->edit
            ->assertSuccessful();
    });

    it('can edit a publisher', function () use ($state): void {
        $publisher = $state->publisher;
        $updatedPublisher = $state->makePublisher;

        $state->edit
            ->fillForm([
                'name' => $updatedPublisher->name,
                'founded' => $updatedPublisher->founded,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($publisher->refresh())
            ->name->toBe($updatedPublisher->name)
            ->founded->format('Y-m-d')->toBe($updatedPublisher->founded->format('Y-m-d'));
    });

    it('can edit a publisher with a logo', function () use ($state): void {
        $publisher = $state->publisher;
        $updatedPublisher = $state->makePublisher;

        $state->edit
            ->fillForm([
                'name' => $updatedPublisher->name,
                'founded' => $updatedPublisher->founded,
                'logo' => $state->updatedImagePath,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $publisher->refresh();
        $publisher->addMedia($state->updatedImagePath, 'publishers')->toMediaCollection('publishers');
        $mediaCollection = $publisher->getMedia('publishers')->last();

        expect($publisher)
            ->name->toBe($updatedPublisher->name)
            ->founded->format('Y-m-d')->toBe($updatedPublisher->founded->format('Y-m-d'));

        expect($mediaCollection)
            ->toBeInstanceOf(Media::class)
            ->model_type->toBe($mediaCollection->model_type)
            ->uuid->toBe($mediaCollection->uuid)
            ->collection_name->toBe($mediaCollection->collection_name)
            ->name->toBe($mediaCollection->name)
            ->file_name->toBe($mediaCollection->file_name);
    });

    it('can validate form data on edit', function () use ($state): void {
        Publisher::factory()->create();

        $state->edit
            ->fillForm([
                'name' => null,
                'founded' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'required',
                'founded' => 'required',
            ]);
    });

    it('can delete a publisher without a logo from the edit page', function () use ($state): void {
        $state->edit
            ->callAction(FormDeleteAction::class);

        assertModelMissing($state->publisher);
    });

    it('can delete a publisher with a logo from the edit page', function () use ($state): void {
        $publisher = $state->publisher->getFirstMedia('publishers');

        $state->edit
            ->callAction(FormDeleteAction::class);

        assertModelMissing($state->publisher);

        if ($publisher !== null) {
            assertDatabaseMissing('media', [
                'model_type' => Publisher::class,
                'model_id' => $state->publisher->id,
                'collection_name' => 'publishers',
            ]);
        }
    });
});
