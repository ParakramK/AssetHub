import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { SubmitEventHandler } from 'react';

interface RoleData {
    id: string;
    name: string;
    description: string | null;
    is_super_admin: boolean;
}

interface ModulePermissions {
    name: string;
    label: string;
    permissions: {
        value: string;
        label: string;
    }[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: '/roles',
    },
    {
        title: 'Edit',
        href: '#',
    },
];

export default function RolesEdit({
    role,
    modules,
    rolePermissions,
}: {
    role: RoleData;
    modules: ModulePermissions[];
    rolePermissions: string[];
}) {
    const { data, setData, put, processing, errors } = useForm({
        permissions: rolePermissions,
    });

    const submit: SubmitEventHandler = (e) => {
        e.preventDefault();

        put(route('roles.update', role.id));
    };

    const toggle = (value: string, checked: boolean) => {
        setData(
            'permissions',
            checked ? [...data.permissions, value] : data.permissions.filter((permission) => permission !== value),
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${role.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title={`Edit ${role.name}`} description="Assign permissions to this role by module" />
                    {role.is_super_admin && <Badge>Super Admin</Badge>}
                </div>

                {role.is_super_admin && (
                    <p className="text-muted-foreground max-w-2xl text-sm">
                        This role has implicit access to every permission through the superadmin bypass. Stored permissions below have
                        no effect while the role is flagged as super admin.
                    </p>
                )}

                <form onSubmit={submit} className="flex max-w-3xl flex-col gap-4">
                    {modules.map((module) => (
                        <Card key={module.name}>
                            <CardHeader className="pb-3">
                                <CardTitle className="text-base">{module.label}</CardTitle>
                            </CardHeader>

                            <CardContent className="grid gap-3 sm:grid-cols-2">
                                {module.permissions.map((permission) => (
                                    <div key={permission.value} className="flex items-center gap-3">
                                        <Checkbox
                                            id={permission.value}
                                            checked={data.permissions.includes(permission.value)}
                                            onCheckedChange={(checked) => toggle(permission.value, checked === true)}
                                        />

                                        <Label htmlFor={permission.value} className="font-normal">
                                            {permission.label}
                                            <span className="text-muted-foreground ml-2 font-mono text-xs">{permission.value}</span>
                                        </Label>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    ))}

                    <InputError message={errors.permissions} />

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            Save permissions
                        </Button>

                        <Button variant="ghost" asChild>
                            <Link href={route('roles.index')}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
