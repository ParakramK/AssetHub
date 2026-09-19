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

export interface Server {
    id: string;
    company_id: string;
    created_by: string | null;
    name: string;
    type: string;
    ip_address: string;
    port: number | null;
    created_at: string;
    company: {
        id: string;
        name: string;
    };
    creator: {
        id: string;
        name: string;
    } | null;
}

interface Filters {
    search: string;
    company_id: string | null;
    type: string | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Servers',
        href: '/servers',
    },
];

export default function ServersIndex({
    servers,
    filters,
    companies,
    types,
}: {
    servers: Server[];
    filters: Filters;
    companies: { id: string; name: string }[];
    types: { value: string; label: string }[];
}) {
    const [search, setSearch] = useState(filters.search);
    const [companyId, setCompanyId] = useState(filters.company_id ?? '');
    const [type, setType] = useState(filters.type ?? '');

    const apply = (next: { search?: string; company_id?: string; type?: string }) => {
        const query: Record<string, string> = {
            search: next.search ?? search,
            company_id: next.company_id ?? companyId,
            type: next.type ?? type,
        };

        Object.keys(query).forEach((key) => {
            if (!query[key]) {
                delete query[key];
            }
        });

        router.get(route('servers.index'), query, { preserveState: true, preserveScroll: true, replace: true });
    };

    useEffect(() => {
        if (search === filters.search) {
            return;
        }

        const id = setTimeout(() => apply({ search }), 300);

        return () => clearTimeout(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const isFiltered = search !== '' || companyId !== '' || type !== '';

    const clear = () => {
        setSearch('');
        setCompanyId('');
        setType('');
        router.get(route('servers.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Servers" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Servers" description="Manage servers and their access details" />
                    <Button asChild>
                        <Link href={route('servers.create')}>Create server</Link>
                    </Button>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row">
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search by name or IP address…"
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
                        value={type}
                        onValueChange={(value) => {
                            setType(value);
                            apply({ type: value });
                        }}
                    >
                        <SelectTrigger className="sm:max-w-xs">
                            <SelectValue placeholder="All types" />
                        </SelectTrigger>
                        <SelectContent>
                            {types.map((option) => (
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
                        {servers.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">
                                {isFiltered ? 'No servers match these filters.' : 'No servers yet. Create the first server to get started.'}
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Name</th>
                                            <th className="px-6 py-3 font-medium">IP address</th>
                                            <th className="px-6 py-3 font-medium">Type</th>
                                            <th className="px-6 py-3 font-medium">Company</th>
                                            <th className="px-6 py-3 font-medium">Port</th>
                                            <th className="px-6 py-3 font-medium">Created by</th>
                                            <th className="px-6 py-3 font-medium">
                                                <span className="sr-only">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {servers.map((server) => (
                                            <tr key={server.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{server.name}</td>
                                                <td className="text-muted-foreground px-6 py-4">{server.ip_address}</td>
                                                <td className="px-6 py-4">
                                                    <Badge variant="secondary" className="uppercase">
                                                        {server.type}
                                                    </Badge>
                                                </td>
                                                <td className="text-muted-foreground px-6 py-4">{server.company.name}</td>
                                                <td className="text-muted-foreground px-6 py-4">{server.port ?? '—'}</td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {server.creator?.name ?? <span className="italic">Unknown</span>}
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    <Button variant="outline" size="sm" asChild>
                                                        <Link href={route('servers.show', server.id)}>View</Link>
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
