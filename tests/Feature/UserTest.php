<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/dev/users', [
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
        $this->post('/api/dev/users', [
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
        $this->post('/api/dev/users', [
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

    public function testAdminLoginSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/dev/users/login', [
            'username' => 'admin',
            'email' => 'admin@testmail.com',
            'password' => 'Admin@123',
        ])->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'username',
                    'email',
                    'token',
                ],
            ])
            ->assertJson([
                'data' => [
                    'username' => 'admin',
                    'email' => 'admin@testmail.com',
                ]
            ]);

        $admin = User::where('email', 'admin@testmail.com')->first();
        self::assertNotNull($admin->token);
    }
    public function testUserLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/dev/users/login', [
            'username' => 'user',
            'email' => 'user@testmail.com',
            'password' => 'User@123',
        ])->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'username',
                    'email',
                    'token',
                ],
            ])
            ->assertJson([
                'data' => [
                    'username' => 'user',
                    'email' => 'user@testmail.com',
                ]
            ]);

        $user = User::where('email', 'user@testmail.com')->first();
        self::assertNotNull($user->token); // Ensure the token is not null
    }




    public function testLoginFailEmailNotFound()
    {
        $this->post('/api/dev/users/login', [
            'username' => 'test',
            'email' => 'test@test.com',
            'password' => 'Password@123',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'The email or password is incorrect.'
                    ]
                ]
            ]);
    }

    public function testGetUserSuccess()
    {
        $this->seed([UserSeeder::class]);
        $user = User::first();
        $this->get('/api/dev/users/current', [
            'Authorization' => 'Bearer ' . $user->token,
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => $user->username,
                    'email' => $user->email,
                ]
            ]);
    }

    public function testGetUnauthorizedUser()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/dev/users/current')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => 'Unauthorized. Missing or invalid token format.'
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/dev/users/current', [
            'Authorization' => 'Bearer wrong-token',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => 'Unauthorized. Invalid token.'
                ]
            ]);
    }

    public function testUpdateUsernameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $user = User::first();
        $this->patch('/api/dev/users/current',
            [
                'username' => 'newtest',
            ],
            [
                'Authorization' => 'Bearer ' . $user->token,
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'newtest',
                ]
            ]);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $user = User::first();
        $this->patch('/api/dev/users/current',
            [
                'password' => 'NewPassword@123',
            ],
            [
                'Authorization' => 'Bearer ' . $user->token,
            ]
        )->assertStatus(200);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);
        $user = User::first();
        $this->delete('/api/dev/users/logout', [], [
            'Authorization' => 'Bearer ' . $user->token,
        ])->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);
    }

    public function testLogoutFailed()
    {
        $this->delete('/api/dev/users/logout', [], [
            'Authorization' => 'Bearer invalidToken',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => 'Unauthorized. Invalid token.'
                ]
            ]);
    }
}
