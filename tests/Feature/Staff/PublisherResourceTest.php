<?php

use App\Filament\Staff\Resources\PublisherResource\Pages\CreatePublisher;
use App\Filament\Staff\Resources\PublisherResource\Pages\EditPublisher;
use App\Filament\Staff\Resources\PublisherResource\Pages\ListPublishers;
use App\Models\Publisher;
use App\Models\Role;
use Filament\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    asRole(Role::IS_STAFF);

    $this->publisher = Publisher::factory()->create();

    $this->makePublisher = Publisher::factory()->make();

    Storage::fake('public');
});

describe('Publisher List Page', function () {
    beforeEach(function () {
        $this->list = livewire(ListPublishers::class, [
            'record' => $this->publisher,
            'panel' => 'staff',
        ]);
    });

    it('can render the list page', function () {
        $this->list
            ->assertSuccessful();
    });

    it('can render publisher logo, name and founded', function () {
        $expectedColumns = [
            'logo',
            'name',
            'founded',
        ];

        foreach ($expectedColumns as $column) {
            $this->list
                ->assertTableColumnExists($column)
                ->assertSuccessful();
        }
    });

    it('can get publisher logo, name and founded', function () {
        $publishers = $this->publisher;
        $publisher = $publishers->first();

        $this->list
            ->assertTableColumnStateSet('logo', $publisher->logo, record: $publisher)
            ->assertTableColumnStateSet('name', $publisher->name, record: $publisher)
            ->assertTableColumnStateSet('founded', $publisher->founded, record: $publisher);
    });

    it('can create a publisher but can not delete it', function () {
        $this->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $this->publisher);
    });
});

describe('Publisher Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreatePublisher::class, [
            'panel' => 'staff',
        ]);
        $this->imagePath = UploadedFile::fake()
            ->image('image.jpg', 50, 50);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new publisher', function () {
        $newPublisher = $this->makePublisher;

        $this->create
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

    it('can create a new publisher with a logo', function () {
        $newPublisher = $this->makePublisher;

        $this->create
            ->fillForm([
                'name' => $newPublisher->name,
                'founded' => $newPublisher->founded,
                'logo' => $this->imagePath,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('publishers', [
            'name' => $newPublisher->name,
            'founded' => $newPublisher->founded,
        ]);

        $createPublisher = Publisher::latest()->first();
        $createPublisher->addMedia($this->imagePath)->toMediaCollection('publishers');
        $mediaCollection = $createPublisher->getMedia('publishers')->last();

        expect($mediaCollection)
            ->toBeInstanceOf(Media::class)
            ->model_type->toBe($mediaCollection->model_type)
            ->uuid->toBe($mediaCollection->uuid)
            ->collection_name->toBe($mediaCollection->collection_name)
            ->name->toBe($mediaCollection->name)
            ->file_name->toBe($mediaCollection->file_name);
    });

    it('can validate form data on create', function () {
        $this->create
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

describe('Publisher Edit Page', function () {
    beforeEach(function () {
        $this->edit = livewire(EditPublisher::class, [
            'record' => $this->publisher->getRouteKey(),
            'panel' => 'staff',
        ]);
        $this->updatedImagePath = UploadedFile::fake()
            ->image('updated_image.jpg', 50, 50);
    });

    it('can render the edit page', function () {
        $this->edit
            ->assertSuccessful();
    });

    it('can edit a publisher', function () {
        $publisher = $this->publisher;
        $updatedPublisher = $this->makePublisher;

        $this->edit
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

    it('can edit a publisher with a logo', function () {
        $publisher = $this->publisher;
        $updatedPublisher = $this->makePublisher;

        $this->edit
            ->fillForm([
                'name' => $updatedPublisher->name,
                'founded' => $updatedPublisher->founded,
                'logo' => $this->updatedImagePath,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $publisher->refresh();
        $publisher->addMedia($this->updatedImagePath, 'publishers')->toMediaCollection('publishers');
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

    it('can validate form data on edit', function () {
        Publisher::factory()
            ->create();
        $this->edit
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

    it('can not delete a publisher from the edit page', function () {
        $this->publisher;

        $this->edit
            ->assertActionHidden(DeleteAction::class);
    });
});
