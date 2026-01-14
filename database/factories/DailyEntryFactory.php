<?php

namespace Database\Factories;

use App\Models\DailyEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class DailyEntryFactory extends Factory
{
    protected $model = DailyEntry::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'jour' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'heures_theoriques' => $this->faker->randomFloat(2, 4, 9),
            'heures_reelles' => $this->faker->randomFloat(2, 0, 10),
            'commentaire' => $this->faker->optional()->sentence(),
            'statut' => $this->faker->randomElement(['brouillon', 'soumis', 'validé', 'refusé']),
            'valide_par' => $this->faker->optional()->randomElement(User::pluck('id')->toArray()),
            'valide_le' => function (array $attributes) {
                return $attributes['statut'] === 'validé'
                    ? $this->faker->dateTimeBetween($attributes['jour'], '+3 days')
                    : null;
            },
            'motif_refus' => function (array $attributes) {
                return $attributes['statut'] === 'refusé'
                    ? $this->faker->sentence()
                    : null;
            },
        ];
    }
}
