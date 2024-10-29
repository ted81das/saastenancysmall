<?php

namespace Database\Seeders;

use App\Constants\TenancyPermissionConstants;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::findOrCreate('create users');
        Permission::findOrCreate('update users');
        Permission::findOrCreate('delete users');
        Permission::findOrCreate('view users');

        Permission::findOrCreate('impersonate users');

        Permission::findOrCreate('create roles');
        Permission::findOrCreate('update roles');
        Permission::findOrCreate('delete roles');
        Permission::findOrCreate('view roles');

        Permission::findOrCreate('create products');
        Permission::findOrCreate('update products');
        Permission::findOrCreate('delete products');
        Permission::findOrCreate('view products');

        Permission::findOrCreate('create plans');
        Permission::findOrCreate('update plans');
        Permission::findOrCreate('delete plans');
        Permission::findOrCreate('view plans');

        Permission::findOrCreate('create subscriptions');
        Permission::findOrCreate('update subscriptions');
        Permission::findOrCreate('delete subscriptions');
        Permission::findOrCreate('view subscriptions');

        Permission::findOrCreate('create orders');
        Permission::findOrCreate('update orders');
        Permission::findOrCreate('delete orders');
        Permission::findOrCreate('view orders');

        Permission::findOrCreate('create one time products');
        Permission::findOrCreate('update one time products');
        Permission::findOrCreate('delete one time products');
        Permission::findOrCreate('view one time products');

        Permission::findOrCreate('create discounts');
        Permission::findOrCreate('update discounts');
        Permission::findOrCreate('delete discounts');
        Permission::findOrCreate('view discounts');

        Permission::findOrCreate('create blog posts');
        Permission::findOrCreate('update blog posts');
        Permission::findOrCreate('delete blog posts');
        Permission::findOrCreate('view blog posts');

        Permission::findOrCreate('create blog post categories');
        Permission::findOrCreate('update blog post categories');
        Permission::findOrCreate('delete blog post categories');
        Permission::findOrCreate('view blog post categories');

        Permission::findOrCreate('create roadmap items');
        Permission::findOrCreate('update roadmap items');
        Permission::findOrCreate('delete roadmap items');
        Permission::findOrCreate('view roadmap items');

        Permission::findOrCreate('view transactions');

        Permission::findOrCreate('update settings');

        Permission::findOrCreate('view stats');

        $role = Role::findOrCreate('admin');

        // give all permissions to admin that doesn't start with "tenancy:"
        $role->givePermissionTo(Permission::all()->filter(function ($permission) {
            return str_starts_with($permission->name, TenancyPermissionConstants::TENANCY_ROLE_PREFIX) === false;
        }));

        $this->multiTenancyRolesAndPermissions();
    }

    private function multiTenancyRolesAndPermissions()
    {
        $permissions = [
            TenancyPermissionConstants::PERMISSION_CREATE_SUBSCRIPTIONS,
            TenancyPermissionConstants::PERMISSION_UPDATE_SUBSCRIPTIONS,
            TenancyPermissionConstants::PERMISSION_DELETE_SUBSCRIPTIONS,
            TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS,
            TenancyPermissionConstants::PERMISSION_CREATE_ORDERS,
            TenancyPermissionConstants::PERMISSION_UPDATE_ORDERS,
            TenancyPermissionConstants::PERMISSION_DELETE_ORDERS,
            TenancyPermissionConstants::PERMISSION_VIEW_ORDERS,
            TenancyPermissionConstants::PERMISSION_VIEW_TRANSACTIONS,
            TenancyPermissionConstants::PERMISSION_INVITE_MEMBERS,
            TenancyPermissionConstants::PERMISSION_MANAGE_TEAM,
            TenancyPermissionConstants::PERMISSION_UPDATE_TENANT_SETTINGS,
        ];

        $tenancyPermissions = [];

        foreach ($permissions as $permission) {
            $tenancyPermissions[] = Permission::findOrCreate($permission);
        }

        $adminRole = Role::findOrCreate(TenancyPermissionConstants::ROLE_ADMIN);
        $adminRole->givePermissionTo($tenancyPermissions);

        $userRole = Role::findOrCreate(TenancyPermissionConstants::ROLE_USER);

        // assign any permissions that the user role should have here
    }
}
