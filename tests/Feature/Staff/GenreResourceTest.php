<?php

use App\Filament\Staff\Resources\GenreResource\Pages\CreateGenre;
use App\Filament\Staff\Resources\GenreResource\Pages\EditGenre;
use App\Filament\Staff\Resources\GenreResource\Pages\ListGenres;
use App\Filament\Staff\Resources\GenreResource\RelationManagers\BooksRelationManager;
use App\Models\Book;
use App\Models\Genre;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    asStaff();
    $this->genre = Genre::factory()->create();
});

describe('Genre List Page', function () {
    beforeEach(function () {
        $this->list = livewire(ListGenres::class, [
            'record' => $this->genre,
            'panel' => 'staff',
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

    it('can create a genre but cannot delete it', function () {
        $this->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $this->genre);
    });
});

describe('Genre Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreateGenre::class, [
            'panel' => 'staff',
        ]);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new genre', function () {
        $newGenre = Genre::factory()->make();
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

    it('can validate form data on create', function (Genre $newGenre) {
        $this->create
            ->call('create')
            ->assertHasFormErrors();
        assertDatabaseMissing('genres', [
            'name' => $newGenre->name,
        ]);
    })->with([
        [fn () => Genre::factory()->state(['name' => null])->make(), 'Missing Name'],
    ]);
});

describe('Genre Edit Page', function () {
    beforeEach(function () {
        $this->edit = livewire(EditGenre::class, [
            'record' => $this->genre->getRouteKey(),
            'panel' => 'staff',
        ]);
    });

    it('can render the edit page', function () {
        $this->edit
            ->assertSuccessful();
    });

    it('can update a genre', function () {
        $genre = $this->genre;

        $updatedGenre = Genre::factory()
            ->state([
                'name' => fake()->name(),
                'bg_color' => fake()->hexColor(),
                'text_color' => fake()->hexColor(),
            ])
            ->create();

        $this->edit
            ->fillForm([
                'name' => $updatedGenre->name,
                'bg_color' => $updatedGenre->bg_color,
                'text_color' => $updatedGenre->text_color,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($this->genre->refresh())
            ->name->toBe($updatedGenre->name)
            ->bg_color->toBe($updatedGenre->bg_color)
            ->text_color->toBe($updatedGenre->text_color);

        assertDatabaseHas('genres', [
            'name' => $updatedGenre->name,
            'bg_color' => $updatedGenre->bg_color,
            'text_color' => $updatedGenre->text_color,
        ]);
    });

    it('can validate form data on update', function (Genre $updatedGenre) {
        $this->edit
            ->fillForm([
                'name' => $updatedGenre->name,
            ])
            ->call('save')
            ->assertHasFormErrors();
    })->with([
        [fn () => Genre::factory()->state(['name' => null])->make(), 'Missing Name'],
    ]);

    it('can render a relation manager with books', function () {
        $author = Genre::factory()
            ->has(Book::factory()->count(10))
            ->create();

        livewire(BooksRelationManager::class, [
            'ownerRecord' => $author,
            'pageClass' => EditAuthor::class,
        ])->assertSuccessful();
    });
});
