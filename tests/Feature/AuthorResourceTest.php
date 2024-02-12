<?php

use App\Filament\Staff\Resources\AuthorResource\Pages\CreateAuthor;
use App\Filament\Staff\Resources\AuthorResource\Pages\EditAuthor;
use App\Filament\Staff\Resources\AuthorResource\Pages\ListAuthors;
use App\Models\Author;
use App\Models\Publisher;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    asStaff();
    $this->author = Author::factory()
        ->has(Publisher::factory())
        ->create();
});

describe('Author List Page', function () {
    it('can render the list page', function () {
        livewire(ListAuthors::class, ['record' => $this->author, 'panel' => 'staff'])
            ->assertSuccessful();
    });
    
    it('can render author name, publisher and date of birth columns', function () {
        livewire(ListAuthors::class, ['record' => $this->author, 'panel' => 'staff'])
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('publisher.name')
            ->assertCanRenderTableColumn('date_of_birth')
            ->assertSuccessful();
    });
    
    it('can get authors name, publisher and date of birth', function () {
        $authors = $this->author;
        $author = $authors->first();
    
        livewire(ListAuthors::class, ['record' => $author, 'panel' => 'staff'])
            ->assertTableColumnStateSet('name', $author->name, record: $author)
            ->assertTableColumnStateSet('publisher.name', $author->publisher->name, record: $author)
            ->assertTableColumnStateSet('date_of_birth', $author->date_of_birth, record: $author);
    });

});

describe('Author Create Page', function () {
    it('can render the create page', function () {
        livewire(CreateAuthor::class, ['panel' => 'staff'])
            ->assertSuccessful();
    });
    
    it('can create a new author', function () {
        $newAuthor = Author::factory()
            ->has(Publisher::factory(), relationship: 'publisher')
            ->make();
    
        livewire(CreateAuthor::class)
            ->fillForm([
                'name' => $newAuthor->name,
                'publisher_id' => $newAuthor->publisher_id,
                'date_of_birth' => $newAuthor->date_of_birth,
                'bio' => $newAuthor->bio,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    
        assertDatabaseHas('authors', [
            'name' => $newAuthor->name,
            'publisher_id' => $newAuthor->publisher_id,
            'date_of_birth' => $newAuthor->date_of_birth,
            'bio' => $newAuthor->bio,
        ]);
    });

    it('can create a new author but cannot delete an author', function () {
        livewire(ListAuthors::class, ['record' => $this->author, 'panel' => 'staff'])
            ->assertActionEnabled('create')
            ->assertTableActionDoesNotExist('Delete');
    });
});

describe('Author Edit Page', function () {
    it('can render the edit page', function () {
        livewire(ListAuthors::class, ['record' => $this->author, 'panel' => 'staff'])
            ->assertSuccessful();
    });
    
    it('can update an author', function () {
        $author = $this->author;
        $updateAuthorData = Author::factory()
            ->has(Publisher::factory(), relationship: 'publisher')
            ->state([
                'name' => fake()->name,
                'publisher_id' => Publisher::factory(),
                'date_of_birth' => fake()->dateTimeThisCentury(),
                'bio' => fake()->realText(500),
            ])
            ->create();
    
        livewire(EditAuthor::class, [
            'record' => $author->getRouteKey(),
        ])
            ->fillForm([
                'name' => $updateAuthorData->name,
                'publisher_id' => $updateAuthorData->publisher_id,
                'date_of_birth' => $updateAuthorData->date_of_birth,
                'bio' => $updateAuthorData->bio,
            ])
            ->call('save')
            ->assertHasNoFormErrors();
    
        expect($author->refresh())
            ->name->toBe($updateAuthorData->name)
            ->publisher_id->toBe($updateAuthorData->publisher_id)
            ->date_of_birth->format('Y-m-d')->toBe($updateAuthorData->date_of_birth->format('Y-m-d'))
            ->bio->toBe($updateAuthorData->bio);
    
        assertDatabaseHas('authors', [
            'name' => $updateAuthorData->name,
            'publisher_id' => $updateAuthorData->publisher_id,
            'date_of_birth' => $updateAuthorData->date_of_birth,
            'bio' => $updateAuthorData->bio,
        ]);
    });
});
