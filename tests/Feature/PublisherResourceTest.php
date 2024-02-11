<?php

it('has publisherresource page', function () {
    $response = $this->get('/publisherresource');

    $response->assertStatus(200);
});
