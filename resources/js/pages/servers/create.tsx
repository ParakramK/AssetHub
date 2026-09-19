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

interface TypeOption {
    value: string;
    label: string;
    defaultPort: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Servers',
        href: '/servers',
    },
    {
        title: 'Create',
        href: '/servers/create',
    },
];

export default function ServersCreate({ companies, types }: { companies: CompanyOption[]; types: TypeOption[] }) {
    const { data, setData, post, processing, errors } = useForm({
        company_id: '',
        name: '',
        type: '',
        ip_address: '',
        port: '',
    });

    const submit: SubmitEventHandler = (e) => {
        e.preventDefault();

        post(route('servers.store'));
    };

    const selectedType = types.find((type) => type.value === data.type);

    const changeType = (value: string) => {
        setData({
            ...data,
            type: value,
            port: data.port === '' ? String(types.find((type) => type.value === value)?.defaultPort ?? '') : data.port,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create server" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Heading title="Create server" description="Add a new server to the system" />

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
                                <Label htmlFor="name">Server name</Label>

                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                    maxLength={255}
                                    placeholder="e.g. Production Web 01"
                                />

                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="type">Type</Label>

                                <Select value={data.type} onValueChange={changeType}>
                                    <SelectTrigger id="type">
                                        <SelectValue placeholder="Select a connection type" />
                                    </SelectTrigger>

                                    <SelectContent>
                                        {types.map((type) => (
                                            <SelectItem key={type.value} value={type.value}>
                                                {type.label} (port {type.defaultPort})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <InputError message={errors.type} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="ip_address">IP address</Label>

                                <Input
                                    id="ip_address"
                                    value={data.ip_address}
                                    onChange={(e) => setData('ip_address', e.target.value)}
                                    required
                                    maxLength={255}
                                    placeholder="e.g. 192.168.1.10"
                                />

                                <InputError message={errors.ip_address} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="port">Port</Label>

                                <Input
                                    id="port"
                                    type="number"
                                    min={1}
                                    max={65535}
                                    value={data.port}
                                    onChange={(e) => setData('port', e.target.value)}
                                    placeholder={selectedType ? `Default: ${selectedType.defaultPort}` : 'Select a type first'}
                                />

                                <InputError message={errors.port} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create server
                                </Button>

                                <Button variant="ghost" asChild>
                                    <Link href={route('servers.index')}>Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
