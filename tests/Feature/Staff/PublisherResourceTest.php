<?php

use App\Filament\Staff\Resources\PublisherResource\Pages\CreatePublisher;
use App\Filament\Staff\Resources\PublisherResource\Pages\EditPublisher;
use App\Filament\Staff\Resources\PublisherResource\Pages\ListPublishers;
use App\Models\Publisher;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    asStaff();
    $this->publisher = Publisher::factory()->create();
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

    it('can create a publisher but cannot delete it', function () {
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
    });
    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new publisher', function () {
        $newPublisher = Publisher::factory()->make();

        $newLogo = UploadedFile::fake()->image('new_logo.jpg');

        $this->create
            ->fillForm([
                'name' => $newPublisher->name,
                'founded' => $newPublisher->founded,
                'logo' => $newLogo,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createPublisher = Publisher::where('name', $newPublisher->name)->first();

        assertTrue($createPublisher->hasMedia('publishers'));

        assertDatabaseHas('publishers', [
            'name' => $newPublisher->name,
            'founded' => $newPublisher->founded,
        ]);

        assertDatabaseHas('media', [
            'model_id' => $createPublisher->id,
            'model_type' => Publisher::class,
            'uuid' => $createPublisher->getFirstMedia('publishers')->uuid,
            'collection_name' => 'publishers',
        ]);
    });

    it('can validate form data on create', function (Publisher $newPublisher) {
        $this->create
            ->call('create')
            ->assertHasFormErrors();
        assertDatabaseMissing('publishers', [
            'name' => $newPublisher->name,
            'founded' => $newPublisher->founded,
        ]);
    })->with([
        [fn () => Publisher::factory()->state(['name' => null])->make(), 'Missing Name'],
        [fn () => Publisher::factory()->state(['founded' => null])->make(), 'Missing Founded'],
    ]);
});

describe('Publisher Edit Page', function () {
    beforeEach(function () {
        $this->edit = livewire(EditPublisher::class, [
            'record' => $this->publisher->getRouteKey(),
            'panel' => 'staff',
        ]);
    });

    it('can render the edit page', function () {
        $this->edit
            ->assertSuccessful();
    });

    it('can edit a publisher', function () {
        $publisher = $this->publisher;

        $updatePublisherData = Publisher::factory()
            ->state([
                'name' => fake()->name(),
                'founded' => fake()->dateTimeThisCentury(),
            ])
            ->create();

        $updateLogoPath = UploadedFile::fake()->image('update_logo.jpg');

        $this->edit
            ->fillForm([
                'name' => $updatePublisherData->name,
                'founded' => $updatePublisherData->founded,
                'logo' => $updateLogoPath,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedPublisher = $publisher->refresh();

        expect($updatedPublisher)
            ->name->toBe($updatedPublisher->name)
            ->founded->format('Y-m-d')->toBe($updatedPublisher->founded->format('Y-m-d'));

        expect($updatedPublisher->getFirstMedia('publishers'))->not->toBeNull();

        assertDatabaseHas('publishers', [
            'name' => $updatedPublisher->name,
            'founded' => $updatedPublisher->founded,
        ]);

        assertDatabaseHas('media', [
            'model_id' => $updatedPublisher->id,
            'model_type' => Publisher::class,
            'uuid' => $updatedPublisher->getFirstMedia('publishers')->uuid,
            'collection_name' => 'publishers',
        ]);
    });

    it('can validate form data on edit', function (Publisher $updatedPublisher) {
        $this->edit
            ->fillForm([
                'name' => $updatedPublisher->name,
                'founded' => $updatedPublisher->founded,
            ])
            ->call('save')
            ->assertHasFormErrors();
    })->with([
        [fn () => Publisher::factory()->state(['name' => null])->make(), 'Missing Name'],
        [fn () => Publisher::factory()->state(['founded' => null])->make(), 'Missing Founded'],
    ]);
});
