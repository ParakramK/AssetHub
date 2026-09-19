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
        title: 'Domains',
        href: '/domains',
    },
    {
        title: 'Create',
        href: '/domains/create',
    },
];

export default function DomainsCreate({ companies }: { companies: CompanyOption[] }) {
    const { data, setData, post, processing, errors } = useForm({
        company_id: '',
        domain_name: '',
        registrar: '',
        expiry_date: '',
    });

    const submit: SubmitEventHandler = (e) => {
        e.preventDefault();

        post(route('domains.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create domain" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Heading title="Create domain" description="Add a new domain to the system" />

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
                                <Label htmlFor="domain_name">Domain name</Label>

                                <Input
                                    id="domain_name"
                                    value={data.domain_name}
                                    onChange={(e) => setData('domain_name', e.target.value)}
                                    required
                                    maxLength={255}
                                    placeholder="e.g. example.com"
                                />

                                <InputError message={errors.domain_name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="registrar">Registrar</Label>

                                <Input
                                    id="registrar"
                                    value={data.registrar}
                                    onChange={(e) => setData('registrar', e.target.value)}
                                    maxLength={255}
                                    placeholder="e.g. Cloudflare"
                                />

                                <InputError message={errors.registrar} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="expiry_date">Expiry date</Label>

                                <Input
                                    id="expiry_date"
                                    type="date"
                                    value={data.expiry_date}
                                    onChange={(e) => setData('expiry_date', e.target.value)}
                                />

                                <InputError message={errors.expiry_date} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create domain
                                </Button>

                                <Button variant="ghost" asChild>
                                    <Link href={route('domains.index')}>Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
