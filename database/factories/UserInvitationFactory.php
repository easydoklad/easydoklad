<?php

namespace Database\Factories;

use App\Enums\UserAccountRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserInvitation>
 */
class UserInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => Str::lower(Str::random(30)),
            'email' => fake()->unique()->safeEmail(),
            'role' => UserAccountRole::User,
            'expires_at' => now()->addHours(config('app.invitation_expiration_hours')),
            'accepted_at' => null,
            'invited_by_id' => User::factory(),
        ];
    }

    /**
     * Set the invitation as accepted.
     */
    public function accepted(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'accepted_at' => now(),
            'accepted_by_id' => $user?->id ?? User::factory()->create()->id,
        ]);
    }

    /**
     * Set the invitation role.
     */
    public function withRole(UserAccountRole $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    /**
     * Set the invitation email.
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }
}
