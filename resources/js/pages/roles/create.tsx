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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: '/roles',
    },
    {
        title: 'Create',
        href: '/roles/create',
    },
];

export default function RolesCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        is_super_admin: false,
    });

    const submit: SubmitEventHandler = (e) => {
        e.preventDefault();

        post(route('roles.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create role" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Heading title="Create role" description="Add a new role to the system" />

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
                                    placeholder="e.g. Asset Manager"
                                />

                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">Description</Label>

                                <Input
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    maxLength={1000}
                                    placeholder="What is this role for?"
                                />

                                <InputError message={errors.description} />
                            </div>

                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="is_super_admin"
                                    checked={data.is_super_admin}
                                    onCheckedChange={(checked) => setData('is_super_admin', checked === true)}
                                />

                                <div className="grid gap-1">
                                    <Label htmlFor="is_super_admin">Super Admin</Label>
                                    <p className="text-muted-foreground text-sm">Grant full access to all superadmin routes.</p>
                                </div>
                            </div>

                            <InputError message={errors.is_super_admin} />

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create role
                                </Button>

                                <Button variant="ghost" asChild>
                                    <Link href={route('roles.index')}>Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
