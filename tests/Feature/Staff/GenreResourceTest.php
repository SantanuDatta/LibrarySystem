<?php

use App\Filament\Staff\Resources\Genres\Pages\CreateGenre;
use App\Filament\Staff\Resources\Genres\Pages\EditGenre;
use App\Filament\Staff\Resources\Genres\Pages\ListGenres;
use App\Filament\Staff\Resources\Genres\RelationManagers\BooksRelationManager;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Role;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

$state = new stdClass;

beforeEach(function () use ($state): void {
    asRole(Role::IS_STAFF);

    $state->genre = Genre::factory()->create();
    $state->makeGenre = Genre::factory()->make();
});

describe('Genre List Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->list = livewire(ListGenres::class, [
            'record' => $state->genre,
            'panel' => 'staff',
        ]);
    });

    it('can render the index', function () use ($state): void {
        $state->list
            ->assertSuccessful();
    });

    it('can render genre name, bg color and text color', function () use ($state): void {
        $expectedColumns = [
            'name',
            'bg_color',
            'text_color',
        ];

        foreach ($expectedColumns as $column) {
            $state->list
                ->assertTableColumnExists($column)
                ->assertSuccessful();
        }
    });

    it('can get genre name, bg color and text color', function () use ($state): void {
        $genre = $state->genre->first();

        $state->list
            ->assertTableColumnStateSet('name', $genre->name, record: $genre)
            ->assertTableColumnStateSet('bg_color', $genre->bg_color, record: $genre)
            ->assertTableColumnStateSet('text_color', $genre->text_color, record: $genre);
    });

    it('can create a genre but can not delete it', function () use ($state): void {
        $state->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $state->genre);
    });
});

describe('Genre Create Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->create = livewire(CreateGenre::class, [
            'panel' => 'staff',
        ]);
    });

    it('can render the create page', function () use ($state): void {
        $state->create
            ->assertSuccessful();
    });

    it('can create a new genre', function () use ($state): void {
        $newGenre = $state->makeGenre;

        $state->create
            ->fillForm([
                'name' => $newGenre->name,
                'bg_color' => $newGenre->bg_color,
                'text_color' => $newGenre->text_color,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('genres', [
            'name' => $newGenre->name,
            'bg_color' => $newGenre->bg_color,
            'text_color' => $newGenre->text_color,
        ]);
    });

    it('can validate form data on create', function () use ($state): void {
        $state->create
            ->fillForm([
                'name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);
    });
});

describe('Genre Edit Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->edit = livewire(EditGenre::class, [
            'record' => $state->genre->getRouteKey(),
            'panel' => 'staff',
        ]);
    });

    it('can render the edit page', function () use ($state): void {
        $state->edit
            ->assertSuccessful();
    });

    it('can update a genre', function () use ($state): void {
        $genre = $state->genre;
        $updatedGenre = $state->makeGenre;

        $state->edit
            ->fillForm([
                'name' => $updatedGenre->name,
                'bg_color' => $updatedGenre->bg_color,
                'text_color' => $updatedGenre->text_color,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($genre->refresh())
            ->name->toBe($updatedGenre->name)
            ->bg_color->toBe($updatedGenre->bg_color)
            ->text_color->toBe($updatedGenre->text_color);
    });

    it('can validate form data on update', function () use ($state): void {
        Genre::factory()->create();

        $state->edit
            ->fillForm([
                'name' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);
    });

    it('can render a relation manager with books', function (): void {
        $genre = Genre::factory()
            ->has(Book::factory()->count(10))
            ->create();

        livewire(BooksRelationManager::class, [
            'ownerRecord' => $genre,
            'pageClass' => EditGenre::class,
        ])->assertSuccessful();
    });

    it('can not delete a genre from the edit page', function () use ($state): void {
        $state->edit
            ->assertActionHidden(DeleteAction::class);
    });
});
