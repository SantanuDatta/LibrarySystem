<?php

use App\Filament\Staff\Resources\PublisherResource\Pages\CreatePublisher;
use App\Filament\Staff\Resources\PublisherResource\Pages\EditPublisher;
use App\Filament\Staff\Resources\PublisherResource\Pages\ListPublishers;
use App\Models\Publisher;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    asStaff();
    $this->publisher = Publisher::factory()->create();
});

describe('Publisher List Page', function () {
    it('can render the list page', function () {
        livewire(ListPublishers::class, ['record' => $this->publisher, 'panel' => 'staff'])
            ->assertSuccessful();
    });

    it('can render publisher logo, name and founded', function () {
        livewire(ListPublishers::class, ['record' => $this->publisher, 'panel' => 'staff'])
            ->assertCanRenderTableColumn('logo')
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('founded');
    });

    it('can get publisher logo, name and founded', function () {
        $publishers = $this->publisher;
        $publisher = $publishers->first();

        livewire(ListPublishers::class, ['record' => $publisher, 'panel' => 'staff'])
            ->assertTableColumnStateSet('logo', $publisher->logo, record: $publisher)
            ->assertTableColumnStateSet('name', $publisher->name, record: $publisher)
            ->assertTableColumnStateSet('founded', $publisher->founded, record: $publisher);
    });
});

describe('Publisher Create Page', function () {
    it('can render the create page', function () {
        livewire(CreatePublisher::class, ['panel' => 'staff'])
            ->assertSuccessful();
    });

    it('can create a new publisher', function () {
        $newPublisher = Publisher::factory()->make();

        $newLogo = UploadedFile::fake()->image('new_logo.jpg');

        livewire(CreatePublisher::class)
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
        livewire(CreatePublisher::class)
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

    it('can create a publisher but cannot delete it', function () {
        livewire(ListPublishers::class, ['record' => $this->publisher, 'panel' => 'staff'])
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $this->publisher);
    });

    describe('Publisher Edit Page', function () {
        it('can render the edit page', function () {
            livewire(EditPublisher::class, ['record' => $this->publisher->getRouteKey(), 'panel' => 'staff'])
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

            livewire(EditPublisher::class, [
                'record' => $publisher->getRouteKey(),
            ])
                ->fillForm([
                    'name' => $updatePublisherData->name,
                    'founded' => $updatePublisherData->founded,
                    'logo' => $updateLogoPath,
                ])
                ->call('save')
                ->assertHasNoFormErrors();

            $updatedPublisher = $publisher->refresh();

            expect($updatedPublisher)
                ->name->toBe($updatePublisherData->name)
                ->founded->format('Y-m-d')->toBe($updatePublisherData->founded->format('Y-m-d'));
            assertNotNull($updatedPublisher->getFirstMedia('publishers'));

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
            $publisher = $this->publisher;

            livewire(EditPublisher::class, [
                'record' => $publisher->getRouteKey(),
            ])
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
});
