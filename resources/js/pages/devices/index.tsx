import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export interface Device {
    id: string;
    company_id: string;
    brand: string;
    model: string;
    code: string;
    status: string;
    created_at: string;
    company: { id: string; name: string };
    type: { id: string; name: string };
    current_employee: { id: string; name: string } | null;
}

interface Filters {
    search: string;
    company_id: string | null;
    type_id: string | null;
    status: string | null;
}

interface Option {
    value: string;
    label: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Devices',
        href: '/devices',
    },
];

const statusVariant = (status: string) => {
    switch (status) {
        case 'assigned':
            return 'default';
        case 'maintenance':
            return 'destructive';
        case 'retired':
            return 'outline';
        default:
            return 'secondary';
    }
};

export default function DevicesIndex({
    devices,
    filters,
    companies,
    types,
    statuses,
}: {
    devices: Device[];
    filters: Filters;
    companies: { id: string; name: string }[];
    types: { id: string; name: string }[];
    statuses: Option[];
}) {
    const [search, setSearch] = useState(filters.search);
    const [companyId, setCompanyId] = useState(filters.company_id ?? '');
    const [typeId, setTypeId] = useState(filters.type_id ?? '');
    const [status, setStatus] = useState(filters.status ?? '');

    const apply = (next: { search?: string; company_id?: string; type_id?: string; status?: string }) => {
        const query: Record<string, string> = {
            search: next.search ?? search,
            company_id: next.company_id ?? companyId,
            type_id: next.type_id ?? typeId,
            status: next.status ?? status,
        };

        Object.keys(query).forEach((key) => {
            if (!query[key]) {
                delete query[key];
            }
        });

        router.get(route('devices.index'), query, { preserveState: true, preserveScroll: true, replace: true });
    };

    useEffect(() => {
        if (search === filters.search) {
            return;
        }

        const id = setTimeout(() => apply({ search }), 300);

        return () => clearTimeout(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const isFiltered = search !== '' || companyId !== '' || typeId !== '' || status !== '';

    const clear = () => {
        setSearch('');
        setCompanyId('');
        setTypeId('');
        setStatus('');
        router.get(route('devices.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Devices" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Devices" description="Manage devices and their assignments" />
                    <Button asChild>
                        <Link href={route('devices.create')}>Create device</Link>
                    </Button>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search by code, brand, model, serial…"
                        className="sm:max-w-xs"
                    />

                    <Select
                        value={companyId}
                        onValueChange={(value) => {
                            setCompanyId(value);
                            apply({ company_id: value });
                        }}
                    >
                        <SelectTrigger className="sm:max-w-xs">
                            <SelectValue placeholder="All companies" />
                        </SelectTrigger>
                        <SelectContent>
                            {companies.map((company) => (
                                <SelectItem key={company.id} value={company.id}>
                                    {company.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={typeId}
                        onValueChange={(value) => {
                            setTypeId(value);
                            apply({ type_id: value });
                        }}
                    >
                        <SelectTrigger className="sm:max-w-xs">
                            <SelectValue placeholder="All types" />
                        </SelectTrigger>
                        <SelectContent>
                            {types.map((type) => (
                                <SelectItem key={type.id} value={type.id}>
                                    {type.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={status}
                        onValueChange={(value) => {
                            setStatus(value);
                            apply({ status: value });
                        }}
                    >
                        <SelectTrigger className="sm:max-w-xs">
                            <SelectValue placeholder="All statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            {statuses.map((option) => (
                                <SelectItem key={option.value} value={option.value}>
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    {isFiltered && (
                        <Button variant="ghost" onClick={clear}>
                            Clear
                        </Button>
                    )}
                </div>

                <Card>
                    <CardContent className="p-0">
                        {devices.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">
                                {isFiltered ? 'No devices match these filters.' : 'No devices yet. Create the first one to get started.'}
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Code</th>
                                            <th className="px-6 py-3 font-medium">Device</th>
                                            <th className="px-6 py-3 font-medium">Type</th>
                                            <th className="px-6 py-3 font-medium">Company</th>
                                            <th className="px-6 py-3 font-medium">Holder</th>
                                            <th className="px-6 py-3 font-medium">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {devices.map((device) => (
                                            <tr key={device.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{device.code}</td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {device.brand} {device.model}
                                                </td>
                                                <td className="text-muted-foreground px-6 py-4">{device.type.name}</td>
                                                <td className="text-muted-foreground px-6 py-4">{device.company.name}</td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {device.current_employee?.name ?? <span className="italic">Unassigned</span>}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <Badge variant={statusVariant(device.status)}>{device.status}</Badge>
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
