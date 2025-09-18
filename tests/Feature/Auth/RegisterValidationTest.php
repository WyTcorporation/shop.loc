<?php

use App\Models\User;

it('returns a Ukrainian validation message when the password is too short', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Іван Тестовий',
        'email' => 'ivan@example.com',
        'password' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.password.0', 'Пароль має містити щонайменше 8 символів.');
});

it('returns a Ukrainian validation message when the email is already taken', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
    ]);

    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'jane@example.com',
        'password' => 'super-secret',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'Електронна адреса вже використовується.');
});
