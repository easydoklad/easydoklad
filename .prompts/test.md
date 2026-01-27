## Test Structure & Style

### 1. Test Setup
- Use `uses(RefreshDatabase::class);` at the top of the test file
- Always import classes at the top - **never use FQCN (Fully Qualified Class Names)** in test code
- Use `it('descriptive test name', function () { ... });` format for all tests

### 2. Authentication & Account Setup
- **Use `actingAsAccount()` helper** for web guard authentication:
  - When you need the user/account instances later: Create them explicitly, then pass to `actingAsAccount($user, $account, $role)`
  - When you don't need the instances: Use no parameters, or single named parameter `actingAsAccount(role: UserAccountRole::User)` to leverage internal factory logic
- **Treat `actingAsAccount()` as execution stage, not preparation** - chain HTTP calls directly after it
- Never use `auth()->user()` or `Accounts::current()` - always use previously prepared models
- Never use `$this` in tests - use Pest Laravel global functions instead

### 3. HTTP Requests & Assertions
- Chain HTTP calls after `actingAsAccount()`:
  ```php
  actingAsAccount($user, $account, UserAccountRole::Owner)
      ->get(route('users'))
      ->assertSuccessful()
  ```
- For PATCH/DELETE that return `back()`:
  - Use `->from(route('...'))` before `->followingRedirects()`
  - Always use `->followingRedirects()` and `->assertSuccessful()` after redirects
- Use Pest Laravel functions: `get()`, `post()`, `patch()`, `delete()` - never `$this->get()`
- Import HTTP helpers: `use function Pest\Laravel\get;` etc.

### 4. Inertia Testing
- Use `assertInertia()` for Inertia component testing
- Use arrow functions for single-call closures - avoid closures with `use` statements
- Always type closure parameters, but **omit return types**:
  ```php
  ->assertInertia(
      fn (AssertableInertia $page) => $page
          ->component('Settings/Users')
          ->has('users', 2)
          ->where('users', fn (Collection $users) => $users->contains('id', $uuid))
  )
  ```

### 5. Expectations & Assertions
- Chain property, relationship, and method access after `expect()` instead of nesting them inside:
  ```php
  // Good - property access chained after expect
  expect($user)->name->toBe('John Doe');
  
  // Good - simple preparation in expect(), then property access chained
  expect($account->fresh())
      ->users
      ->contains($targetUser)
      ->toBeFalse();
  
  // Bad - property access nested inside expect
  expect($user->name)->toBe('John Doe');
  
  // Bad - accessing inner values to assert on nested inside expect
  expect($account->fresh()->users->contains($targetUser))->toBeFalse();
  ```
- **Rule**: Only the base value/object (optionally with simple preparation calls like `fresh()`, `first()`, `value`) goes inside `expect()`. All property access, relationship access, or method calls used for assertions must be chained after `expect()`.
- Use specific assertions: `assertSuccessful()`, `assertForbidden()`, `assertNotFound()` instead of `assertStatus()`

### 6. Code Style
- **No docblocks for inferable types**: Don't add `/** @var User $user */` for factory-created models or functions with return types
- **Arrow functions for single calls**: Use `fn ($param) => ...` instead of `function ($param) use (...) { ... }`
- **Type all parameters**: Always type closure/arrow function parameters: `fn (AssertableInertia $page)`, `fn (Collection $users)`
- **No return types on closures**: Omit return types from closures/arrow functions
- **No `??=` operator**: Use `$var = $var ?: defaultValue()` instead

### 7. Test Coverage
- Test both success and error cases:
  - Success paths (200/redirect)
  - Authorization failures (403)
  - Not found cases (404)
  - Validation errors when applicable
- For controllers with `back()` redirects, always follow redirects and assert successful response
- Test edge cases (e.g., owner cannot remove themselves)

### 8. Controller Code Style (if modifying)
- Use `abort_if()` or `abort_unless()` for simple abort conditions instead of `if` statements
- When comparing Eloquent models, prefer calling `is()` on the model for comparison instead of comparing identifiers directly 

## Example Test Structure

```php
<?php

use App\Enums\UserAccountRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

uses(RefreshDatabase::class);

it('describes what the test verifies', function () {
    // Preparation: Only create what you need
    $user = User::factory()->create();
    $account = createAccount();
    
    // Or if you don't need instances:
    // actingAsAccount()
    // or:
    // actingAsAccount(role: UserAccountRole::User)
    
    // Execution: Chain after actingAsAccount
    actingAsAccount($user, $account, UserAccountRole::Owner)
        ->from(route('users'))
        ->followingRedirects()
        ->patch(route('users.update', $targetUser->uuid), [
            'role' => 'owner',
        ])
        ->assertSuccessful();
    
    // Assertions: Use chained expectations
    expect($account->fresh())
        ->users
        ->contains($targetUser)
        ->toBeTrue();
});
```

## Checklist
- [ ] All classes imported, no FQCN
- [ ] No `$this` usage - using Pest Laravel globals
- [ ] `actingAsAccount()` chained with HTTP calls
- [ ] Arrow functions for single-call closures
- [ ] All closure parameters typed, no return types
- [ ] No docblocks for inferable types
- [ ] Chained expectations instead of nested calls
- [ ] `followingRedirects()` + `assertSuccessful()` for redirect responses
- [ ] Tests cover success, 403, 404 cases
- [ ] Code formatted with Pint
- [ ] All tests passing
