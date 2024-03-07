<?php

use App\Filament\Admin\Resources\GenreResource\Pages\CreateGenre;
use App\Filament\Admin\Resources\GenreResource\Pages\EditGenre;
use App\Filament\Admin\Resources\GenreResource\Pages\ListGenres;
use App\Filament\Admin\Resources\GenreResource\RelationManagers\BooksRelationManager;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Role;
use Filament\Actions\DeleteAction as FormDeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    asRole(Role::IS_ADMIN);

    $this->genre = Genre::factory()->create();

    $this->makeGenre = Genre::factory()->make();
});

describe('Genre List Page', function () {
    beforeEach(function () {
        $this->list = livewire(ListGenres::class, [
            'record' => $this->genre,
            'panel' => 'admin',
        ]);
    });
    it('can render the index', function () {
        $this->list
            ->assertSuccessful();
    });

    it('can render genre name, bg color and text color', function () {
        $expectedColumns = [
            'name',
            'bg_color',
            'text_color',
        ];

        foreach ($expectedColumns as $column) {
            $this->list
                ->assertTableColumnExists($column)
                ->assertSuccessful();
        }
    });

    it('can get genre name, bg color and text color', function () {
        $genres = $this->genre;
        $genre = $genres->first();

        $this->list
            ->assertTableColumnStateSet('name', $genre->name, record: $genre)
            ->assertTableColumnStateSet('bg_color', $genre->bg_color, record: $genre)
            ->assertTableColumnStateSet('text_color', $genre->text_color, record: $genre);
    });

    it('can delete a genre', function () {
        $this->list
            ->callTableAction(TableDeleteAction::class, $this->genre);
        assertModelMissing($this->genre);
    });
});

describe('Genre Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreateGenre::class, [
            'panel' => 'admin',
        ]);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new genre', function () {
        $newGenre = $this->makeGenre;
        $this->create
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

    it('can validate form data on create', function () {
        $this->create
            ->fillForm([
                'name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);
    });
});

describe('Genre Edit Page', function () {
    beforeEach(function () {
        $this->edit = livewire(EditGenre::class, [
            'record' => $this->genre->getRouteKey(),
            'panel' => 'admin',
        ]);
    });

    it('can render the edit page', function () {
        $this->edit
            ->assertSuccessful();
    });

    it('can update a genre', function () {
        $genre = $this->genre;
        $updatedGenre = $this->makeGenre;

        $this->edit
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

    it('can validate form data on update', function () {
        Genre::factory()
            ->create();

        $this->edit
            ->fillForm([
                'name' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);
    });

    it('can render a relation manager with books', function () {
        $author = Genre::factory()
            ->has(Book::factory()->count(10))
            ->create();

        livewire(BooksRelationManager::class, [
            'ownerRecord' => $author,
            'pageClass' => EditGenre::class,
        ])->assertSuccessful();
    });

    it('can delete a genre from the edit page', function () {
        $this->genre;

        $this->edit
            ->callAction(FormDeleteAction::class);

        assertModelMissing($this->genre);
    });
});
