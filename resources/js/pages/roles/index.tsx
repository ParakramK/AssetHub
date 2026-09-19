import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

export interface Role {
    id: string;
    name: string;
    description: string | null;
    is_super_admin: boolean;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: '/roles',
    },
];

export default function RolesIndex({ roles }: { roles: Role[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Roles" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Roles" description="Manage roles and their access levels" />
                    <Button asChild>
                        <Link href={route('roles.create')}>Create role</Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {roles.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">No roles yet. Create the first role to get started.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Name</th>
                                            <th className="px-6 py-3 font-medium">Description</th>
                                            <th className="px-6 py-3 font-medium">Type</th>
                                            <th className="px-6 py-3 font-medium">
                                                <span className="sr-only">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {roles.map((role) => (
                                            <tr key={role.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{role.name}</td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {role.description || <span className="italic">No description</span>}
                                                </td>
                                                <td className="px-6 py-4">
                                                    {role.is_super_admin ? <Badge>Super Admin</Badge> : <Badge variant="secondary">Standard</Badge>}
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    <Button variant="outline" size="sm" asChild>
                                                        <Link href={route('roles.edit', role.id)}>Edit</Link>
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
