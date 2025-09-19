<?php

use App\Models\User;
use Illuminate\Support\Facades\App;

beforeEach(function () {
    App::setLocale('uk');
});

it('returns a Ukrainian validation message when the password is too short', function () {
    App::setLocale('uk');

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Іван Тестовий',
        'email' => 'ivan@example.com',
        'password' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.password.0', __('validation.min.string', [
            'attribute' => __('validation.attributes.password'),
            'min' => 8,
        ]));
});

it('returns a Ukrainian validation message when the email is already taken', function () {
    App::setLocale('uk');

    User::factory()->create([
        'email' => 'jane@example.com',
    ]);

    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'jane@example.com',
        'password' => 'super-secret',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', __('validation.unique', [
            'attribute' => __('validation.attributes.email'),
        ]));
});

it('returns a Russian validation message when the password is too short', function () {
    App::setLocale('ru');

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Иван Тестовый',
        'email' => 'ivan-ru@example.com',
        'password' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.password.0', __('validation.min.string', [
            'attribute' => __('validation.attributes.password'),
            'min' => 8,
        ]));
});

it('returns a Portuguese validation message when the password is too short', function () {
    App::setLocale('pt');

    $response = $this->postJson('/api/auth/register', [
        'name' => 'João Teste',
        'email' => 'joao@example.com',
        'password' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.password.0', __('validation.min.string', [
            'attribute' => __('validation.attributes.password'),
            'min' => 8,
        ]));
});
