<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login route shows the choice between client and practitioner spaces', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Choisissez votre espace de connexion');
    $response->assertSee('Je suis client');
    $response->assertSee('Je suis praticien');
});

test('practitioner login route still shows the existing practitioner form', function () {
    $response = $this->get(route('login.practitioner'));

    $response->assertOk();
    $response->assertSee('Mot de passe');
    $response->assertSee('Se connecter');
});
