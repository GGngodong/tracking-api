<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use function PHPUnit\Framework\assertNotNull;

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

    public function testLoginSuccess()
    {

        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'email' => 'test@testmail.com',
            'password' => 'Password@123',
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'email' => 'test@testmail.com',
                ]
            ]);

        $user = User::where('email', 'test@testmail.com')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailEmailNotFound()
    {
        $this->post('/api/users/login', [
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
        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'email' => 'test@testmail.com',
                ]
            ]);
    }

    public function testGetUnauthorizedUser()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => 'Unauthorized.'

                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current', [
            'Authorization' => 'wrong-token'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => 'Unauthorized.'
                ]
            ]);
    }

    public function testUpdateUsernameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();
        $this->patch('/api/users/current',
            [
                'password' => 'Password@123'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                ]
            ]);
        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();
        $this->patch('/api/users/current',
            [
                'password' => 'NewPassword@123'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                ]
            ]);
        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();
        $this->patch('/api/users/current',
            [
                'username' => 'TestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTestTest'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => [
                        'The username field must not be greater than 100 characters.',
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->delete('/api/users/logout', [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    true
                ]
            ]);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->delete('/api/users/logout', [
            'Authorization' => 'test'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'unauthorized'
                    ]
                ]
            ]);
    }
}
