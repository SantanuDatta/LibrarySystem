<?php

use App\Filament\Staff\Resources\AuthorResource\Pages\CreateAuthor;
use App\Filament\Staff\Resources\AuthorResource\Pages\ListAuthors;
use App\Models\Author;
use App\Models\Publisher;

use function Pest\Livewire\livewire;

beforeEach(function () {
    asStaff();
    $this->author = Author::factory()
        ->has(Publisher::factory())
        ->create();
});

it('can render the list page with authors', function () {
    livewire(ListAuthors::class, ['record' => $this->author, 'panel' => 'staff'])
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateAuthor::class, ['panel' => 'staff'])
        ->assertSuccessful();
});

it('can render the edit page', function () {
    livewire(ListAuthors::class, ['record' => $this->author, 'panel' => 'staff'])
        ->assertSuccessful();
});

it('can create a new author but cannot delete an author', function () {
    livewire(ListAuthors::class, ['record' => $this->author, 'panel' => 'staff'])
        ->assertActionEnabled('create')
        ->assertTableActionDoesNotExist('Delete');
});
