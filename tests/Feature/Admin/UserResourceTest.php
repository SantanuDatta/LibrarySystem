<?php

it('has admin/userresource page', function () {
    $response = $this->get('/admin/userresource');

    $response->assertStatus(200);
});
