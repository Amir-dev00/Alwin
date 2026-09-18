<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_admin_login_is_reachable(): void
    {
        $this->get('/admin/login')->assertOk();
    }
}
