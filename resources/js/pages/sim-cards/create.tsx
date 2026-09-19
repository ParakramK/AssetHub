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

interface Option {
    value: string;
    label: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'SIM Cards',
        href: '/sim-cards',
    },
    {
        title: 'Create',
        href: '/sim-cards/create',
    },
];

export default function SimCardsCreate({
    companies,
    providers,
    statuses,
}: {
    companies: CompanyOption[];
    providers: Option[];
    statuses: Option[];
}) {
    const { data, setData, post, processing, errors } = useForm({
        company_id: '',
        provider: '',
        number: '',
        present_status: 'assigned',
    });

    const submit: SubmitEventHandler = (e) => {
        e.preventDefault();

        post(route('sim-cards.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create SIM card" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Heading title="Create SIM card" description="Add a new SIM card to the system" />

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

                            <div className="grid gap-2 sm:grid-cols-2 sm:gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="provider">Provider</Label>

                                    <Select value={data.provider} onValueChange={(value) => setData('provider', value)}>
                                        <SelectTrigger id="provider">
                                            <SelectValue placeholder="Select a provider" />
                                        </SelectTrigger>

                                        <SelectContent>
                                            {providers.map((option) => (
                                                <SelectItem key={option.value} value={option.value}>
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    <InputError message={errors.provider} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="present_status">Present status</Label>

                                    <Select value={data.present_status} onValueChange={(value) => setData('present_status', value)}>
                                        <SelectTrigger id="present_status">
                                            <SelectValue placeholder="Select a status" />
                                        </SelectTrigger>

                                        <SelectContent>
                                            {statuses.map((option) => (
                                                <SelectItem key={option.value} value={option.value}>
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    <InputError message={errors.present_status} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="number">Number</Label>

                                <Input
                                    id="number"
                                    value={data.number}
                                    onChange={(e) => setData('number', e.target.value)}
                                    required
                                    maxLength={20}
                                    placeholder="e.g. +977-9812345678"
                                />

                                <InputError message={errors.number} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create SIM card
                                </Button>

                                <Button variant="ghost" asChild>
                                    <Link href={route('sim-cards.index')}>Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
