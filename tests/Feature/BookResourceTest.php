<?php

it('has bookresource page', function () {
    $response = $this->get('/bookresource');

    $response->assertStatus(200);
});
