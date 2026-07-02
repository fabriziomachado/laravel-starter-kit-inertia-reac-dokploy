<?php

declare(strict_types=1);

it('includes the container identifier on the welcome page', function (): void {
    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('welcome')
        ->where('containerId', gethostname())
        ->has('reverb')
    );
});
