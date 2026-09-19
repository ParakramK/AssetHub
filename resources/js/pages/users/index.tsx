import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import { type SharedData, type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

export interface User {
    id: string;
    name: string;
    email: string;
    created_at: string;
    created_servers_count: number;
    created_domains_count: number;
    roles: {
        id: string;
        name: string;
        is_super_admin: boolean;
    }[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

export default function UsersIndex({ users }: { users: User[] }) {
    const { auth } = usePage<SharedData>().props;
    const [target, setTarget] = useState<User | null>(null);

    const isSelf = (user: User) => String(auth.user.id) === user.id;

    const destroy = (user: User) => {
        router.delete(route('users.destroy', user.id), {
            onSuccess: () => setTarget(null),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Users" description="Manage users and their role assignments" />
                    <Button asChild>
                        <Link href={route('users.create')}>Create user</Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {users.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">No users yet. Create the first user to get started.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Name</th>
                                            <th className="px-6 py-3 font-medium">Email</th>
                                            <th className="px-6 py-3 font-medium">Roles</th>
                                            <th className="px-6 py-3 font-medium">
                                                <span className="sr-only">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {users.map((user) => (
                                            <tr key={user.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{user.name}</td>
                                                <td className="text-muted-foreground px-6 py-4">{user.email}</td>
                                                <td className="px-6 py-4">
                                                    {user.roles.length === 0 ? (
                                                        <span className="text-muted-foreground italic">No roles</span>
                                                    ) : (
                                                        <div className="flex flex-wrap gap-1">
                                                            {user.roles.map((role) =>
                                                                role.is_super_admin ? (
                                                                    <Badge key={role.id}>{role.name}</Badge>
                                                                ) : (
                                                                    <Badge key={role.id} variant="secondary">
                                                                        {role.name}
                                                                    </Badge>
                                                                ),
                                                            )}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    <Button variant="outline" size="sm" onClick={() => setTarget(user)}>
                                                        Delete
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

            <Dialog open={target !== null} onOpenChange={(open) => !open && setTarget(null)}>
                <DialogContent>
                    {target &&
                        (isSelf(target) ? (
                            <>
                                <DialogTitle>Cannot delete this user</DialogTitle>
                                <DialogDescription>You cannot delete your own account while signed in as it.</DialogDescription>
                                <DialogFooter>
                                    <DialogClose asChild>
                                        <Button variant="outline">Close</Button>
                                    </DialogClose>
                                </DialogFooter>
                            </>
                        ) : (
                            <>
                                <DialogTitle>Delete {target.name}?</DialogTitle>
                                <DialogDescription asChild>
                                    <div className="flex flex-col gap-2">
                                        <span>This will permanently delete {target.email}. The impact:</span>
                                        <ul className="list-disc pl-5">
                                            <li>
                                                {target.roles.length === 0
                                                    ? 'No roles to unassign.'
                                                    : `Unassign ${target.roles.length === 1 ? 'role' : 'roles'}: ${target.roles.map((role) => role.name).join(', ')}.`}
                                            </li>
                                            <li>
                                                {target.created_servers_count === 0
                                                    ? 'No servers created by this user.'
                                                    : `${target.created_servers_count} ${target.created_servers_count === 1 ? 'server' : 'servers'} created by this user will be kept, with the creator cleared.`}
                                            </li>
                                            <li>
                                                {target.created_domains_count === 0
                                                    ? 'No domains created by this user.'
                                                    : `${target.created_domains_count} ${target.created_domains_count === 1 ? 'domain' : 'domains'} created by this user will be kept, with the creator cleared.`}
                                            </li>
                                            <li>Any directly assigned permissions will be removed.</li>
                                        </ul>
                                    </div>
                                </DialogDescription>
                                <DialogFooter>
                                    <DialogClose asChild>
                                        <Button variant="outline">Cancel</Button>
                                    </DialogClose>
                                    <Button variant="destructive" onClick={() => destroy(target)}>
                                        Delete user
                                    </Button>
                                </DialogFooter>
                            </>
                        ))}
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
