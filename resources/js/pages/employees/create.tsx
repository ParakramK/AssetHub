import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { SubmitEventHandler } from 'react';

interface CompanyOption {
    id: string;
    name: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Employees',
        href: '/employees',
    },
    {
        title: 'Create',
        href: '/employees/create',
    },
];

export default function EmployeesCreate({ companies }: { companies: CompanyOption[] }) {
    const { data, setData, post, processing, errors } = useForm({
        company_id: '',
        name: '',
        email: '',
        mobile_no: '',
    });

    const submit: SubmitEventHandler = (e) => {
        e.preventDefault();

        post(route('employees.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create employee" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Heading title="Create employee" description="Add a new employee to the system" />

                <Card className="max-w-2xl">
                    <CardContent className="pt-6">
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="company_id">Company</Label>

                                <Select value={data.company_id} onValueChange={(value) => setData('company_id', value)}>
                                    <SelectTrigger id="company_id">
                                        <SelectValue placeholder="Select a company" />
                                    </SelectTrigger>

                                    <SelectContent>
                                        {companies.map((company) => (
                                            <SelectItem key={company.id} value={company.id}>
                                                {company.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <InputError message={errors.company_id} />
                            </div>

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

                            <div className="grid gap-2">
                                <Label htmlFor="mobile_no">Mobile number</Label>

                                <Input
                                    id="mobile_no"
                                    value={data.mobile_no}
                                    onChange={(e) => setData('mobile_no', e.target.value)}
                                    maxLength={20}
                                    placeholder="+977-98XXXXXXXX"
                                />

                                <InputError message={errors.mobile_no} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create employee
                                </Button>

                                <Button variant="ghost" asChild>
                                    <Link href={route('employees.index')}>Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
