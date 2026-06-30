<?php

declare(strict_types=1);

it('responds to the health check endpoint', function (): void {
    $response = $this->get('/up');

    $response->assertOk();
});
