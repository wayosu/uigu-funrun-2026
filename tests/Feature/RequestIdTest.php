<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('response includes request id header', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Request-Id');
});
