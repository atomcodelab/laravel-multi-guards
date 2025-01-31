# Laravel Uwierzytelnianie Multi Guards

Jak uruchomić uwierzytelnianie multi guard w aplikacji Laravel. Oddzielne logowanie dla użytkownika i administratora aplikacji. <https://www.youtube.com/watch?v=8O2uo7zL5_I>

# Tworzenie projektu

```sh
cd /
mkdir www
cd www

composer create-project laravel/laravel wow.test

code wow.test
```

## Tworzenie prowidera

Zobacz czy provider dodał sie do pliku bootstrap/providers.php

```sh
php artisan make:provider MultiGuardProvider
```

## MultiGuardProvider

Dodaj w pliku app/Providers/MultiGuardProvider.php

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MultiGuardsProvider extends ServiceProvider
{
    /**
     * Register services.
    */
    public function register(): void
    {
        // Spatie multi guards admin guard and provider for Auth::guard('admin')->check();
        $this->app->config["auth.guards.admin"] = [
            'driver' => 'session',
            'provider' => 'admins',
        ];

        // Create Admin::class model first
        $this->app->config["auth.providers.admins"] = [
            'driver' => 'eloquent',
            'model' => \App\Models\Admin::class,
        ];
    }
}
```

## Utwórz klasę Admin

Odpowiednich zmian dokonaj dla klasy User zwykłego użytkownika.

```sh
# Model, migration, seeder, factory
php artisan make:model Admin -mfs
```

### Dodaj w klasie Admin

```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Model table.
    */
    protected $table = 'admins';

    /**
     * Auth guard.
    */
    protected $guard = 'admin';

    /**
     * Append user relations.
    */
    protected $with = [];

    /**
     * The attributes that are mass assignable.
    *
    * @var array<int, string>
    */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
    *
    * @var array<int, string>
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
    *
    * @var array<string, string>
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    protected function getDefaultGuardName(): string
    {
        return 'admin';
    }
}
```

### Dodaj w klasie User

```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Model table.
    */
    protected $table = 'users';

    /**
     * Auth guard.
    */
    protected $guard = 'web';

    /**
     * Append user relations.
    */
    protected $with = [];

    /**
     * The attributes that are mass assignable.
    *
    * @var array<int, string>
    */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
    *
    * @var array<int, string>
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
    *
    * @var array<string, string>
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    protected function getDefaultGuardName(): string
    {
        return 'web';
    }
}
```

## Utwórz migrację dla klasy Admin

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('admins', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('email')->unique();
			$table->timestamp('email_verified_at')->nullable();
			$table->string('password');
			$table->rememberToken();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('admins');
	}
};
```

## Dodaj w AdminFactory

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
	/**
	 * The current password being used by the factory.
	 */
	protected static ?string $password;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'name' => fake()->name(),
			'email' => fake()->unique()->safeEmail(),
			'email_verified_at' => now(),
			'password' => static::$password ??= Hash::make('password'),
			'remember_token' => Str::random(10),
		];
	}

	/**
	 * Indicate that the model's email address should be unverified.
	 */
	public function unverified(): static
	{
		return $this->state(fn(array $attributes) => [
			'email_verified_at' => null,
		]);
	}
}
```

## Dodaj w AdminSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		Admin::factory()->create([
			'name' => 'Test Admin',
			'email' => 'test@example.com',
		]);
	}
}
```

## Dodaj w DatabaseSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		// User::factory(10)->create();

		User::factory()->create([
			'name' => 'Test User',
			'email' => 'test@example.com',
		]);

		(new AdminSeeder())->run();
	}
}
```

## Dodaj routes do api

Dodaj w pliku routes/web.php

```php
<?php

use Illuminate\Support\Facades\Route;

// Import web.api routes
include 'webapi/admin.php';
include 'webapi/user.php';

// Page routes
Route::get('/', function () {
    return view('welcome');
});
```

## Dodaj routes Admina

Utwórz katalog i dodaj w routes/webapi/admin.php

```php
<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Admin routes
Route::prefix('web/api/admin')->name('web.api.admin')->middleware([
    'web'
])->group(function () {
    // Public
    # Route::post('/login', [AdminLoginController::class, 'index'])->name('login');
    Route::post('/login', function () {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::guard('admin')->attempt($credentials, request()->filled('remember'))) {
            request()->session()->regenerate();
            return response()->json([
                'message' => 'Admin logged',
                'guard' => 'admin',
            ]);
        } else {
            return response()->json([
                'message' => 'Admin invalid credentials',
                'guard' => 'admin',
            ], 422);
        }
    });

    // Private admin
    Route::middleware([
        'auth:admin',
    ])->group(function () {
        // Only logged admin
        Route::get('/logged', function () {
            return response()->json([
                'message' => 'Admin logged',
                'guard' => 'admin',
            ]);
        });
    });
});
```

## Dodaj routes Usera

Utwórz katalog i dodaj w routes/webapi/user.php

```php
<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// User routes
Route::prefix('web/api')->name('web.api')->middleware([
    'web'
])->group(function () {
    // Public
    # Route::post('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', function () {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::guard('web')->attempt($credentials, request()->filled('remember'))) {
            request()->session()->regenerate();
            return response()->json([
                'message' => 'User logged',
                'guard' => 'web',
            ]);
        } else {
            return response()->json([
                'message' => 'User invalid credentials',
                'guard' => 'web',
            ], 422);
        }
    });

    // Private user
    Route::middleware([
        'auth:web',
    ])->group(function () {
        // Only logged user
        Route::get('/logged', function () {
            return response()->json([
                'message' => 'User logged',
                'guard' => 'web',
            ]);
        });
    });
});
```

## Utwórz test

```sh
php artisan make:test MultiGuardsTest
```

### Dodaj w pliku testu

```php
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
```

### Uruchom migrację tabel

```sh
php artisan migrate:fresh --seed
```

### Uruchom test

```sh
php artisan test --filter MultiGuardsTest
```

## Uwierzytelnianie użytkownika

```php
// Czy użytkownik jest zalogowany jako admin lub user
Auth::guard('admin')->check()
Auth::guard('web')->check()

// Wyloguj użytkownika z guard
Auth::shouldUse('admin');
Auth::logout();

// Wyloguj użytkownika z guard
Auth::guard('admin')->logout();
Auth::guard('web')->logout();

// Ustaw zalogowanego użytkownika w testach
$this->actingAs($user, 'admin');
$this->actingAs($user, 'web');
```
