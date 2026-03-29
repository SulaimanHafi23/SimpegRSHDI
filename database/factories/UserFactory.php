<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $department = Department::firstOrCreate(
            ['code' => 'TST'],
            ['name' => 'Test Department', 'description' => 'Factory generated', 'is_active' => true]
        );

        $workerName = fake()->name();
        $worker = Worker::create([
            'nip' => 'TST' . fake()->unique()->numerify('######'),
            'name' => $workerName,
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => '08' . fake()->unique()->numerify('##########'),
            'address' => fake()->address(),
            'birth_date' => fake()->dateTimeBetween('-45 years', '-20 years')->format('Y-m-d'),
            'birth_place' => fake()->city(),
            'gender' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'religion' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'department_id' => $department->id,
            'hire_date' => fake()->dateTimeBetween('-10 years', '-1 years')->format('Y-m-d'),
            'employment_status' => 'contract',
            'status' => 'active',
        ]);

        return [
            'worker_id' => $worker->id,
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
