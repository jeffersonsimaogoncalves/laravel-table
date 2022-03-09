<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Livewire;
use Okipa\LaravelTable\Abstracts\AbstractTableConfiguration;
use Okipa\LaravelTable\Column;
use Okipa\LaravelTable\Table;
use Tests\Models\User;
use Tests\TestCase;

class TableQueryTest extends TestCase
{
    /** @test */
    public function it_can_set_query(): void
    {
        $users = User::factory()->count(2)->create();
        $config = new class extends AbstractTableConfiguration {
            public function __construct(protected int|null $userIdToExclude = null)
            {
                //
            }

            protected function table(): Table
            {
                return Table::make()->model(User::class)
                    ->query(fn(Builder $query) => $query->where('id', '!=', $this->userIdToExclude));
            }

            protected function columns(): array
            {
                return [
                    Column::make('Id'),
                ];
            }
        };
        Livewire::test(\Okipa\LaravelTable\Livewire\Table::class, [
            'config' => $config::class,
            'configParams' => ['userIdToExclude' => $users->first()->id],
        ])
            ->call('init')
            ->assertSeeHtml('<th class="align-middle" scope="row">' . $users->last()->id . '</th>')
            ->assertDontSeeHtml('<th class="align-middle" scope="row">' . $users->first()->id . '</th>');
    }
}