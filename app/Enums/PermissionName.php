<?php

namespace App\Enums;

use Illuminate\Support\Str;

/**
 * The single source of truth for permission names.
 *
 * Values flow into Spatie as plain strings (routes, middleware, seeders,
 * validation via Rule::enum). The part before the dot is the module, which
 * drives the grouped assignment UI. To add a permission: add a case here,
 * re-run the PermissionSeeder, and reference `PermissionName::X->value`.
 */
enum PermissionName: string
{
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';

    case RolesView = 'roles.view';
    case RolesCreate = 'roles.create';
    case RolesUpdate = 'roles.update';
    case RolesDelete = 'roles.delete';

    case CompaniesView = 'companies.view';
    case CompaniesCreate = 'companies.create';
    case CompaniesUpdate = 'companies.update';
    case CompaniesDelete = 'companies.delete';

    case DomainsView = 'domains.view';
    case DomainsCreate = 'domains.create';
    case DomainsUpdate = 'domains.update';
    case DomainsDelete = 'domains.delete';

    case ServersView = 'servers.view';
    case ServersCreate = 'servers.create';
    case ServersUpdate = 'servers.update';
    case ServersDelete = 'servers.delete';

    case CredentialsView = 'credentials.view';
    case CredentialsCreate = 'credentials.create';
    case CredentialsDelete = 'credentials.delete';

    case SshKeysView = 'ssh-keys.view';
    case SshKeysCreate = 'ssh-keys.create';
    case SshKeysDelete = 'ssh-keys.delete';

    case EmployeesView = 'employees.view';
    case EmployeesCreate = 'employees.create';
    case EmployeesUpdate = 'employees.update';
    case EmployeesDelete = 'employees.delete';

    public function module(): string
    {
        return Str::before($this->value, '.');
    }

    public function action(): string
    {
        return Str::after($this->value, '.');
    }

    public function label(): string
    {
        return Str::headline($this->action());
    }

    public static function moduleLabel(string $module): string
    {
        return Str::headline($module);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Cases grouped by module, in declaration order, for the assignment UI.
     *
     * @return array<int, array{name: string, label: string, permissions: array<int, array{value: string, label: string}>}>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (self::cases() as $case) {
            $grouped[$case->module()][] = ['value' => $case->value, 'label' => $case->label()];
        }

        return collect($grouped)
            ->map(fn (array $permissions, string $module) => [
                'name' => $module,
                'label' => self::moduleLabel($module),
                'permissions' => $permissions,
            ])
            ->values()
            ->all();
    }
}
