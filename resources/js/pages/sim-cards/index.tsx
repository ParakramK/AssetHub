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

export interface SimCard {
    id: string;
    company_id: string;
    provider: string;
    number: string;
    present_status: string;
    created_at: string;
    company: {
        id: string;
        name: string;
    };
    current_employee: {
        id: string;
        name: string;
    } | null;
}

interface Filters {
    search: string;
    company_id: string | null;
    provider: string | null;
    status: string | null;
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
];

const statusVariant = (status: string) => {
    switch (status) {
        case 'assigned':
            return 'default';
        case 'lost':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const statusLabel = (status: string, statuses: Option[]) => statuses.find((option) => option.value === status)?.label ?? status;

export default function SimCardsIndex({
    simCards,
    filters,
    companies,
    providers,
    statuses,
}: {
    simCards: SimCard[];
    filters: Filters;
    companies: { id: string; name: string }[];
    providers: Option[];
    statuses: Option[];
}) {
    const [search, setSearch] = useState(filters.search);
    const [companyId, setCompanyId] = useState(filters.company_id ?? '');
    const [provider, setProvider] = useState(filters.provider ?? '');
    const [status, setStatus] = useState(filters.status ?? '');

    const apply = (next: { search?: string; company_id?: string; provider?: string; status?: string }) => {
        const query: Record<string, string> = {
            search: next.search ?? search,
            company_id: next.company_id ?? companyId,
            provider: next.provider ?? provider,
            status: next.status ?? status,
        };

        Object.keys(query).forEach((key) => {
            if (!query[key]) {
                delete query[key];
            }
        });

        router.get(route('sim-cards.index'), query, { preserveState: true, preserveScroll: true, replace: true });
    };

    useEffect(() => {
        if (search === filters.search) {
            return;
        }

        const id = setTimeout(() => apply({ search }), 300);

        return () => clearTimeout(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const isFiltered = search !== '' || companyId !== '' || provider !== '' || status !== '';

    const clear = () => {
        setSearch('');
        setCompanyId('');
        setProvider('');
        setStatus('');
        router.get(route('sim-cards.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="SIM Cards" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title="SIM Cards" description="Manage SIM cards and their present status" />
                    <Button asChild>
                        <Link href={route('sim-cards.create')}>Create SIM card</Link>
                    </Button>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search by number…"
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
                        value={provider}
                        onValueChange={(value) => {
                            setProvider(value);
                            apply({ provider: value });
                        }}
                    >
                        <SelectTrigger className="sm:max-w-xs">
                            <SelectValue placeholder="All providers" />
                        </SelectTrigger>
                        <SelectContent>
                            {providers.map((option) => (
                                <SelectItem key={option.value} value={option.value}>
                                    {option.label}
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
                        {simCards.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">
                                {isFiltered ? 'No SIM cards match these filters.' : 'No SIM cards yet. Create the first one to get started.'}
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Number</th>
                                            <th className="px-6 py-3 font-medium">Provider</th>
                                            <th className="px-6 py-3 font-medium">Company</th>
                                            <th className="px-6 py-3 font-medium">Holder</th>
                                            <th className="px-6 py-3 font-medium">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {simCards.map((simCard) => (
                                            <tr key={simCard.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{simCard.number}</td>
                                                <td className="px-6 py-4">
                                                    <Badge variant="secondary" className="uppercase">
                                                        {simCard.provider}
                                                    </Badge>
                                                </td>
                                                <td className="text-muted-foreground px-6 py-4">{simCard.company.name}</td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {simCard.current_employee?.name ?? <span className="italic">Unassigned</span>}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <Badge variant={statusVariant(simCard.present_status)}>
                                                        {statusLabel(simCard.present_status, statuses)}
                                                    </Badge>
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
