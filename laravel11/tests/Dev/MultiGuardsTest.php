<?php

namespace Tests\Dev;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MultiGuardsTest extends TestCase
{
    /**
     * Only logged users
     */
    public function test_user_logged_error(): void
    {
        $response = $this->getJson('/web/api/logged');

        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        // Login user error
        $response = $this->postJson('/web/api/login', [
            'email' => 'invalid@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_logged_success(): void
    {
        // Login user
        $response = $this->postJson('/web/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        // User logged
        $response = $this->getJson('/web/api/logged');

        $response->assertStatus(200)->assertJson([
            'message' => 'User logged',
            'guard' => 'web',
        ]);

        // Admin not logged
        $response = $this->getJson('/web/api/admin/logged');

        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * Only logged admins
     */
    public function test_admin_logged_error(): void
    {
        $response = $this->getJson('/web/api/admin/logged');

        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        // Login admin error
        $response = $this->postJson('/web/api/admin/login', [
            'email' => 'invalid@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_logged_success(): void
    {
        // Login admin
        $response = $this->postJson('/web/api/admin/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        // Admin logged
        $response = $this->getJson('/web/api/admin/logged');

        $response->assertStatus(200)->assertJson([
            'message' => 'Admin logged',
            'guard' => 'admin',
        ]);

        // User not logged
        $response = $this->getJson('/web/api/logged');

        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
