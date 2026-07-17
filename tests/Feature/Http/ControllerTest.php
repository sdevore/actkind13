<?php

use App\Http\Controllers\Controller;
use App\Models\User;

test('controller auth helpers return null and false for guests', function () {
    $controller = new class extends Controller
    {
        public function authUser(): ?User
        {
            return $this->getAuthUser();
        }

        public function isAuthenticated(): bool
        {
            return $this->isAuth();
        }
    };

    expect($controller->authUser())->toBeNull();
    expect($controller->isAuthenticated())->toBeFalse();
});

test('controller auth helpers return the authenticated user', function () {
    $user = User::factory()->create();

    $controller = new class extends Controller
    {
        public function authUser(): ?User
        {
            return $this->getAuthUser();
        }

        public function isAuthenticated(): bool
        {
            return $this->isAuth();
        }
    };

    $this->actingAs($user);

    expect($controller->authUser()?->is($user))->toBeTrue();
    expect($controller->isAuthenticated())->toBeTrue();
});
