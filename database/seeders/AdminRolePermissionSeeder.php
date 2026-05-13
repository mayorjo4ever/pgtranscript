<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use function app;

class AdminRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // =====================
        // PERMISSIONS
        // =====================
        $permissions = [
            'manage-users',
            'manage-roles',
            'manage-permissions', 
            'view-total-sent-transcript-widget',
            'view-total-transcript-request-widget',
            'view-new-transcript-request-widget',
            'view-completed-transcript-request-widget',                      
            'view-id-card-request-widget',   
            'view-transcript-request-analysis-widget',
            'view-completed-transcript-request-analysis-widget',
            'view-admin-menu','create-admin','view-admin',
            'view-role','create-role',
            'view-online-admins','view-offline-admins',
            'import-new-transcript-request',
            'delete-certificate-data',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',                
            ]);
        }        
        
        // =====================
        // ROLES
        // =====================
        $superAdmin = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'admin',
        ]);
        
        $moderator1 = Role::firstOrCreate([
            'name' => 'transcript-officer',
            'guard_name' => 'admin',
        ]);
        
        $moderator2 = Role::firstOrCreate([
            'name' => 'transcript-operator',
            'guard_name' => 'admin',
        ]);
        
        // =====================
        // ASSIGN PERMISSIONS
        // =====================
        $superAdmin->givePermissionTo(Permission::where('guard_name','admin')->get());

//        $moderator1->givePermissionTo([
//            'manage chat',
//        ]);        
//        
    }
}
