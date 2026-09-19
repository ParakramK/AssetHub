import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { SubmitEventHandler } from 'react';

interface RoleOption {
    id: string;
    name: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: 'Create',
        href: '/users/create',
    },
];

export default function UsersCreate({ roles }: { roles: RoleOption[] }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        roles: [] as string[],
    });

    const submit: SubmitEventHandler = (e) => {
        e.preventDefault();

        post(route('users.store'));
    };

    const toggleRole = (id: string, checked: boolean) => {
        setData(
            'roles',
            checked ? [...data.roles, id] : data.roles.filter((roleId) => roleId !== id),
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create user" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Heading title="Create user" description="Add a new user and assign their roles" />

                <Card className="max-w-2xl">
                    <CardContent className="pt-6">
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>

                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                    maxLength={255}
                                    placeholder="e.g. Jane Doe"
                                />

                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>

                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                    maxLength={255}
                                    placeholder="jane@example.com"
                                />

                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2 sm:grid-cols-2 sm:gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="password">Password</Label>

                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        required
                                        placeholder="Minimum 8 characters"
                                    />

                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">Confirm password</Label>

                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        required
                                    />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label>Roles</Label>

                                {roles.length === 0 ? (
                                    <p className="text-muted-foreground text-sm">No roles available.</p>
                                ) : (
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        {roles.map((role) => (
                                            <div key={role.id} className="flex items-center gap-3">
                                                <Checkbox
                                                    id={`role-${role.id}`}
                                                    checked={data.roles.includes(role.id)}
                                                    onCheckedChange={(checked) => toggleRole(role.id, checked === true)}
                                                />

                                                <Label htmlFor={`role-${role.id}`} className="font-normal">
                                                    {role.name}
                                                </Label>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                <InputError message={errors.roles} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create user
                                </Button>

                                <Button variant="ghost" asChild>
                                    <Link href={route('users.index')}>Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
