import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export interface Domain {
    id: string;
    company_id: string;
    created_by: string | null;
    domain_name: string;
    registrar: string | null;
    expiry_date: string | null;
    created_at: string;
    company: {
        id: string;
        name: string;
    };
}

interface Filters {
    search: string;
    company_id: string | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Domains',
        href: '/domains',
    },
];

export default function DomainsIndex({
    domains,
    filters,
    companies,
}: {
    domains: Domain[];
    filters: Filters;
    companies: { id: string; name: string }[];
}) {
    const [search, setSearch] = useState(filters.search);
    const [companyId, setCompanyId] = useState(filters.company_id ?? '');

    const apply = (next: { search?: string; company_id?: string }) => {
        const query: Record<string, string> = {
            search: next.search ?? search,
            company_id: next.company_id ?? companyId,
        };

        Object.keys(query).forEach((key) => {
            if (!query[key]) {
                delete query[key];
            }
        });

        router.get(route('domains.index'), query, { preserveState: true, preserveScroll: true, replace: true });
    };

    useEffect(() => {
        if (search === filters.search) {
            return;
        }

        const id = setTimeout(() => apply({ search }), 300);

        return () => clearTimeout(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const isFiltered = search !== '' || companyId !== '';

    const clear = () => {
        setSearch('');
        setCompanyId('');
        router.get(route('domains.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Domains" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Domains" description="Manage domains and their registrations" />
                    <Button asChild>
                        <Link href={route('domains.create')}>Create domain</Link>
                    </Button>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row">
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search by domain or registrar…"
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

                    {isFiltered && (
                        <Button variant="ghost" onClick={clear}>
                            Clear
                        </Button>
                    )}
                </div>

                <Card>
                    <CardContent className="p-0">
                        {domains.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">
                                {isFiltered ? 'No domains match these filters.' : 'No domains yet. Create the first domain to get started.'}
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Domain</th>
                                            <th className="px-6 py-3 font-medium">Company</th>
                                            <th className="px-6 py-3 font-medium">Registrar</th>
                                            <th className="px-6 py-3 font-medium">Expiry date</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {domains.map((domain) => (
                                            <tr key={domain.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{domain.domain_name}</td>
                                                <td className="text-muted-foreground px-6 py-4">{domain.company.name}</td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {domain.registrar || <span className="italic">No registrar</span>}
                                                </td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {domain.expiry_date ? (
                                                        domain.expiry_date.slice(0, 10)
                                                    ) : (
                                                        <span className="italic">No expiry date</span>
                                                    )}
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
