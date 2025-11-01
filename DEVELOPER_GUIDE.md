# Midas Portal - Developer Guide

## Local Development Setup

### Prerequisites
- PHP 8.2+ with extensions: `mbstring`, `xml`, `curl`, `zip`, `gd`, `mysql`
- Composer 2.x
- Node.js 18+ & NPM
- MySQL 8.0+ or MariaDB 10.5+
- XAMPP/WAMP/Laravel Herd (Windows) or MAMP (Mac)
- Git
- IDE: VSCode, PHPStorm, or Sublime Text

### Quick Start

#### 1. Clone and Install
```bash
git clone <repository-url> midas-portal
cd midas-portal

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 2. Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE midas_portal;
exit;

# Update .env
DB_DATABASE=midas_portal
DB_USERNAME=root
DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate
php artisan db:seed
```

#### 3. Start Development Server
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Asset compilation (watch mode)
npm run dev

# Terminal 3: Queue worker (optional)
php artisan queue:work
```

**Access Application**:
- Frontend: http://localhost:8000
- Customer Portal: http://localhost:8000/customer/login
- Log Viewer: http://localhost:8000/webmonks-log-viewer

---

## Project Structure

```
midas-portal/
├── app/
│   ├── Console/
│   │   ├── Commands/          # Artisan commands
│   │   └── Kernel.php         # Task scheduling
│   ├── Contracts/             # Interfaces
│   │   ├── Repositories/
│   │   └── Services/
│   ├── Events/                # Domain events
│   ├── Exceptions/
│   │   └── Handler.php
│   ├── Exports/               # Excel export classes
│   ├── Helpers/
│   │   └── SettingsHelper.php
│   ├── Http/
│   │   ├── Controllers/       # HTTP controllers
│   │   ├── Middleware/        # Custom middleware
│   │   ├── Requests/          # Form requests
│   │   └── Resources/         # API resources
│   ├── Listeners/             # Event listeners
│   ├── Models/                # Eloquent models
│   ├── Providers/             # Service providers
│   ├── Repositories/          # Data access layer
│   ├── Services/              # Business logic
│   ├── Traits/                # Reusable traits
│   └── helpers.php            # Global helper functions
├── config/                    # Configuration files
├── database/
│   ├── factories/             # Model factories
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── public/                    # Public assets
│   ├── admin/                 # Admin panel assets
│   └── customer/              # Customer portal assets
├── resources/
│   ├── js/                    # JavaScript files
│   ├── sass/                  # SCSS files
│   └── views/                 # Blade templates
├── routes/
│   ├── web.php                # Admin routes
│   ├── customer.php           # Customer portal routes
│   ├── api.php                # API routes
│   └── console.php            # Artisan commands
├── storage/                   # File storage
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/                     # Test suite
│   ├── Feature/               # Feature tests
│   ├── Integration/           # Integration tests
│   └── Unit/                  # Unit tests
├── vendor/                    # Composer dependencies
├── .env.example               # Environment template
├── artisan                    # Artisan CLI
├── composer.json              # PHP dependencies
├── package.json               # NPM dependencies
├── phpunit.xml                # PHPUnit config
└── webpack.mix.js             # Laravel Mix config
```

---

## Coding Standards

### PHP Standards (PSR-12)

**Use Laravel Pint for automatic formatting**:
```bash
# Format all files
composer fix

# Check for style issues
composer analyze
```

**Code Style Examples**:
```php
<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(
        private CustomerRepository $repository
    ) {}

    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = $this->repository->create($data);

            event(new CustomerRegistered($customer));

            return $customer;
        });
    }
}
```

### Naming Conventions

**Classes**:
- Controllers: `CustomerController`, `QuotationController`
- Services: `CustomerService`, `NotificationService`
- Repositories: `CustomerRepository`, `PolicyRepository`
- Models: `Customer`, `CustomerInsurance`
- Events: `CustomerRegistered`, `PolicyCreated`

**Methods**:
- CRUD: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
- Services: `createCustomer()`, `sendNotification()`, `calculatePremium()`

**Database**:
- Tables: `customers`, `customer_insurances`, `notification_logs`
- Columns: `customer_id`, `policy_no`, `created_at`
- Foreign Keys: `customer_id`, `insurance_company_id`

---

## Architecture Patterns

### Repository Pattern

**Interface** (`app/Contracts/Repositories/CustomerRepositoryInterface.php`):
```php
<?php

namespace App\Contracts\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Customer;
    public function create(array $data): Customer;
    public function update(int $id, array $data): Customer;
    public function delete(int $id): bool;
    public function findByEmail(string $email): ?Customer;
}
```

**Implementation** (`app/Repositories/CustomerRepository.php`):
```php
<?php

namespace App\Repositories;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function find(int $id): ?Customer
    {
        return Customer::find($id);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    // ... other methods
}
```

**Binding** (`app/Providers/RepositoryServiceProvider.php`):
```php
public function register(): void
{
    $this->app->bind(
        CustomerRepositoryInterface::class,
        CustomerRepository::class
    );
}
```

### Service Layer Pattern

```php
<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Events\Customer\CustomerRegistered;
use App\Models\Customer;

class CustomerService
{
    public function __construct(
        private CustomerRepositoryInterface $repository,
        private NotificationService $notificationService
    ) {}

    public function register(array $data): Customer
    {
        DB::transaction(function () use ($data) {
            // 1. Create customer
            $customer = $this->repository->create($data);

            // 2. Generate default password
            $password = $customer->setDefaultPassword();

            // 3. Fire event
            event(new CustomerRegistered($customer, $password));

            // 4. Send welcome notification
            $this->notificationService->sendWelcome($customer, $password);

            return $customer;
        });
    }
}
```

### Event-Driven Architecture

**Event** (`app/Events/Customer/CustomerRegistered.php`):
```php
<?php

namespace App\Events\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public string $plainPassword
    ) {}
}
```

**Listener** (`app/Listeners/Customer/SendWelcomeNotification.php`):
```php
<?php

namespace App\Listeners\Customer;

use App\Events\Customer\CustomerRegistered;
use App\Services\NotificationService;

class SendWelcomeNotification
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(CustomerRegistered $event): void
    {
        $this->notificationService->sendWelcome(
            $event->customer,
            $event->plainPassword
        );
    }
}
```

**Registration** (`app/Providers/EventServiceProvider.php`):
```php
protected $listen = [
    CustomerRegistered::class => [
        SendWelcomeNotification::class,
        CreateAuditLog::class,
    ],
];
```

---

## Database Conventions

### Migrations

**Naming**: `YYYY_MM_DD_HHMMSS_create_table_name_table.php`

**Example**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('mobile_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->boolean('status')->default(true);

            // Foreign keys
            $table->foreignId('family_group_id')->nullable()
                  ->constrained('family_groups')
                  ->onDelete('set null');

            // Audit fields
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
```

### Models

**Example** (`app/Models/Customer.php`):
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'mobile_number',
        'date_of_birth',
        'family_group_id',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'status' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationships
    public function familyGroup()
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function insurance()
    {
        return $this->hasMany(CustomerInsurance::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    // Accessors
    protected function getDateOfBirthFormattedAttribute()
    {
        return $this->date_of_birth?->format('d/m/Y');
    }
}
```

---

## Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/Models/CustomerTest.php

# Run with coverage
php artisan test --coverage

# Run notification tests
composer test:notifications
```

### Writing Tests (Pest PHP)

**Unit Test** (`tests/Unit/Models/CustomerTest.php`):
```php
<?php

use App\Models\Customer;

test('it can create a customer', function () {
    $customer = Customer::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($customer->name)->toBe('John Doe')
        ->and($customer->email)->toBe('john@example.com');
});

test('it belongs to a family group', function () {
    $familyGroup = FamilyGroup::factory()->create();
    $customer = Customer::factory()->create([
        'family_group_id' => $familyGroup->id,
    ]);

    expect($customer->familyGroup)->toBeInstanceOf(FamilyGroup::class)
        ->and($customer->familyGroup->id)->toBe($familyGroup->id);
});
```

**Feature Test** (`tests/Feature/Controllers/CustomerControllerTest.php`):
```php
<?php

use App\Models\Customer;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('it can display customer list', function () {
    Customer::factory()->count(5)->create();

    $response = $this->get(route('customers.index'));

    $response->assertStatus(200)
        ->assertViewIs('customers.index')
        ->assertViewHas('customers');
});

test('it can create a customer', function () {
    $customerData = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'mobile_number' => '9876543210',
        'status' => true,
    ];

    $response = $this->post(route('customers.store'), $customerData);

    $response->assertRedirect(route('customers.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('customers', [
        'email' => 'jane@example.com',
    ]);
});
```

---

## Artisan Commands

### Create Custom Command

```bash
php artisan make:command SendBirthdayWishes
```

**Example** (`app/Console/Commands/SendBirthdayWishes.php`):
```php
<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendBirthdayWishes extends Command
{
    protected $signature = 'customers:send-birthday-wishes';
    protected $description = 'Send birthday wishes to customers';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Sending birthday wishes...');

        $customers = Customer::whereMonth('date_of_birth', now()->month)
            ->whereDay('date_of_birth', now()->day)
            ->get();

        $count = 0;
        foreach ($customers as $customer) {
            $this->notificationService->sendBirthdayWish($customer);
            $count++;
        }

        $this->info("Sent birthday wishes to {$count} customers.");

        return Command::SUCCESS;
    }
}
```

**Schedule Command** (`app/Console/Kernel.php`):
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('customers:send-birthday-wishes')
        ->dailyAt('08:00')
        ->timezone('Asia/Kolkata');
}
```

---

## Debugging Tools

### Laravel Debugbar

```bash
composer require barryvdh/laravel-debugbar --dev
```

**Access**: Bottom of page in development

### Tinker (REPL)

```bash
php artisan tinker

>>> $customer = Customer::find(1);
>>> $customer->name;
>>> $customer->insurance()->count();
```

### Query Logging

```php
DB::enableQueryLog();

// Run queries
$customers = Customer::with('insurance')->get();

// View queries
dd(DB::getQueryLog());
```

### Telescope (Optional)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Access**: http://localhost:8000/telescope

---

## Git Workflow

### Branching Strategy

```
main (production)
  ├── develop (staging)
  │   ├── feature/customer-portal
  │   ├── feature/notification-system
  │   └── bugfix/email-sending
```

### Commit Messages

**Format**: `type(scope): message`

**Types**:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style (formatting)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance

**Examples**:
```bash
git commit -m "feat(customer): add family group management"
git commit -m "fix(notification): resolve WhatsApp API timeout"
git commit -m "docs(api): update customer endpoints documentation"
```

### Pull Request Checklist

- [ ] Code follows PSR-12 standards (`composer analyze`)
- [ ] All tests pass (`php artisan test`)
- [ ] New features have tests
- [ ] Documentation updated
- [ ] No merge conflicts
- [ ] Reviewed by at least one team member

---

## Useful Commands

### Development
```bash
# Clear all caches
php artisan optimize:clear

# Regenerate IDE helper
php artisan ide-helper:generate
php artisan ide-helper:models

# Run code formatter
composer fix

# Run static analysis
composer analyze

# Run refactoring checks
composer refactor
```

### Database
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Seed specific seeder
php artisan db:seed --class=CustomerSeeder

# Generate migration from existing database
php artisan migrate:generate
```

### Queue
```bash
# Work on default queue
php artisan queue:work

# Work on specific queue
php artisan queue:work --queue=notifications,emails

# List failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all
```

---

## Environment Variables Guide

### Local Development (.env.local)
```env
APP_ENV=local
APP_DEBUG=true
MAIL_MAILER=log  # Email to log files
QUEUE_CONNECTION=sync  # Synchronous queue
CACHE_DRIVER=file
SESSION_DRIVER=file
```

### Staging (.env.staging)
```env
APP_ENV=staging
APP_DEBUG=true
MAIL_MAILER=smtp  # Real email
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Production (.env.production)
```env
APP_ENV=production
APP_DEBUG=false
MAIL_MAILER=smtp
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
CSP_ENABLED=true
```

---

## Troubleshooting

### Common Development Issues

**1. Class not found**
```bash
composer dump-autoload
```

**2. View not updating**
```bash
php artisan view:clear
```

**3. Route not found**
```bash
php artisan route:clear
php artisan route:list
```

**4. Migration error**
```bash
# Drop all tables and re-migrate
php artisan migrate:fresh

# Check migration status
php artisan migrate:status
```

**5. Permission denied (storage)**
```bash
chmod -R 775 storage bootstrap/cache
```

---

## Resources

### Documentation
- [Laravel 10 Docs](https://laravel.com/docs/10.x)
- [Pest PHP](https://pestphp.com/)
- [Spatie Packages](https://spatie.be/docs/laravel-permission)

### Tools
- [Laravel Herd](https://herd.laravel.com/) - Local development
- [PHPStorm Laravel Plugin](https://plugins.jetbrains.com/plugin/7532-laravel)
- [Postman Collection](https://www.postman.com/)

---

**Next Steps**:
1. Set up your local environment
2. Run `composer install && npm install`
3. Configure `.env` file
4. Run migrations and seeders
5. Start coding! 🚀
