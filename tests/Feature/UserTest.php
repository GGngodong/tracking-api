<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'username' => 'test',
            'email' => 'test@testmail.com',
            'password' => 'Password@123',
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'email' => 'test@testmail.com',
                ]
            ]);
    }

    public function testRegisterFail()
    {
        $this->post('/api/users', [
            'username' => '',
            'email' => '',
            'password' => '',
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => [
                        'The username field is required.'
                    ],
                    'password' => [
                        'The password field is required.'
                    ],
                    'email' => [
                        'The email field is required.',
                    ],
                ]
            ]);
    }

    public function testRegisterUserAlreadyExists()
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'username' => 'test',
            'email' => 'test@testmail.com',
            'password' => 'Password@123',
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => [
                        'The username has already been taken.'
                    ],
                    'email' => [
                        'The email has already been taken.'
                    ],
                ]
            ]);
    }
}
