<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login route shows the choice between client and practitioner spaces', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Bienvenue');
    $response->assertSee('Espace client');
    $response->assertSee('Espace praticien');
});

test('practitioner login route still shows the existing practitioner form', function () {
    $response = $this->get(route('login.practitioner'));

    $response->assertOk();
    $response->assertSee('Mot de passe');
    $response->assertSee('Se connecter');
});
