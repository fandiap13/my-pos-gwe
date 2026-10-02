<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

function adminUser(): User
{
    return User::factory()->admin()->create();
}

test('admin can view category list', function () {
    $admin = adminUser();
    Category::factory()->create(['name' => 'Makanan']);

    $response = $this->actingAs($admin)->get(route('admin.categories.index'));

    $response->assertOk();
});

test('admin can create a root category', function () {
    $admin = adminUser();

    $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
        'name' => 'Minuman',
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', [
        'name' => 'Minuman',
        'parent_id' => null,
        'slug' => 'minuman',
    ]);
});

test('admin can create a subcategory with a parent', function () {
    $admin = adminUser();
    $parent = Category::factory()->create(['name' => 'Makanan']);

    $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
        'name' => 'Makanan Ringan',
        'parent_id' => $parent->id,
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', [
        'name' => 'Makanan Ringan',
        'parent_id' => $parent->id,
    ]);
});

test('category name is required', function () {
    $admin = adminUser();

    $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
});

test('duplicate slug gets a unique suffix', function () {
    $admin = adminUser();
    Category::factory()->create(['name' => 'Snack', 'slug' => 'snack']);

    $this->actingAs($admin)->post(route('admin.categories.store'), [
        'name' => 'Snack',
    ]);

    $this->assertDatabaseHas('categories', ['name' => 'Snack', 'slug' => 'snack-1']);
});

test('admin can update a category', function () {
    $admin = adminUser();
    $category = Category::factory()->create(['name' => 'Lama']);

    $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
        'name' => 'Baru',
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    expect($category->fresh()->name)->toBe('Baru');
});

test('category cannot be its own parent', function () {
    $admin = adminUser();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
        'name' => $category->name,
        'parent_id' => $category->id,
    ]);

    $response->assertSessionHasErrors('parent_id');
});

test('category cannot have one of its descendants as parent', function () {
    $admin = adminUser();
    $parent = Category::factory()->create(['name' => 'Induk']);
    $child = Category::factory()->childOf($parent)->create(['name' => 'Anak']);

    $response = $this->actingAs($admin)->put(route('admin.categories.update', $parent), [
        'name' => $parent->name,
        'parent_id' => $child->id,
    ]);

    $response->assertSessionHasErrors('parent_id');
});

test('admin can delete a category without products or children', function () {
    $admin = adminUser();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertSoftDeleted($category);
});

test('category with products cannot be deleted', function () {
    $admin = adminUser();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

    $response->assertSessionHasErrors('category');
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
});

test('category with subcategories cannot be deleted', function () {
    $admin = adminUser();
    $parent = Category::factory()->create();
    Category::factory()->childOf($parent)->create();

    $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $parent));

    $response->assertSessionHasErrors('category');
    $this->assertDatabaseHas('categories', ['id' => $parent->id, 'deleted_at' => null]);
});

test('kasir cannot access category routes', function () {
    $kasir = User::factory()->kasir()->create();

    $response = $this->actingAs($kasir)->get(route('admin.categories.index'));

    $response->assertForbidden();
});

// Regression: resource yang dilempar mentah ke Inertia dibungkus jadi
// {data: {...}} oleh jalur Responsable (bukan lewat ->resolve()), sehingga
// halaman Edit membaca props.category.name = undefined. Lihat
// docs/DECISIONS.md.
test('category edit page receives flat category props', function () {
    $admin = adminUser();
    $category = Category::factory()->create(['name' => 'Snack']);

    $response = $this->actingAs($admin)->get(route('admin.categories.edit', $category));

    $response->assertInertia(
        fn ($page) => $page
            ->component('Admin/Categories/Edit')
            ->where('category.name', 'Snack')
            ->missing('category.data')
    );
});
