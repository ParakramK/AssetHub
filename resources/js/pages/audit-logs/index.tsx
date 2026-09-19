import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export interface AuditLog {
    id: string;
    user_id: string | null;
    action: string;
    auditable_type: string;
    auditable_id: string;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip_address: string | null;
    created_at: string;
    actor: {
        id: string;
        name: string;
        email: string;
    } | null;
}

interface Filters {
    search: string;
    action: string | null;
    model: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Audit Logs',
        href: '/audit-logs',
    },
];

function shortModel(type: string): string {
    const parts = type.split('\\');

    return parts[parts.length - 1] ?? type;
}

export default function AuditLogsIndex({ logs, filters, actions }: { logs: AuditLog[]; filters: Filters; actions: string[] }) {
    const [search, setSearch] = useState(filters.search);
    const [action, setAction] = useState(filters.action ?? '');
    const [model, setModel] = useState(filters.model);

    const pruneForm = useForm({ days: 90 });

    const apply = (next: { search?: string; action?: string; model?: string }) => {
        const query: Record<string, string> = {
            search: next.search ?? search,
            action: next.action ?? action,
            model: next.model ?? model,
        };

        Object.keys(query).forEach((key) => {
            if (!query[key]) {
                delete query[key];
            }
        });

        router.get(route('audit-logs.index'), query, { preserveState: true, preserveScroll: true, replace: true });
    };

    useEffect(() => {
        if (search === filters.search) {
            return;
        }

        const id = setTimeout(() => apply({ search }), 300);

        return () => clearTimeout(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const isFiltered = search !== '' || action !== '' || model !== '';

    const clear = () => {
        setSearch('');
        setAction('');
        setModel('');
        router.get(route('audit-logs.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    const destroy = (id: string) => {
        if (window.confirm('Delete this audit log entry? This cannot be undone.')) {
            router.delete(route('audit-logs.destroy', id), { preserveScroll: true });
        }
    };

    const prune = (e: React.FormEvent) => {
        e.preventDefault();

        if (window.confirm(`Delete all entries older than ${pruneForm.data.days} days? This cannot be undone.`)) {
            pruneForm.post(route('audit-logs.prune'), { preserveScroll: true });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Audit Logs" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Audit Logs" description="Who changed what, and who revealed a secret" />
                    <form onSubmit={prune} className="flex items-center gap-2">
                        <Input
                            type="number"
                            min={1}
                            max={3650}
                            value={pruneForm.data.days}
                            onChange={(e) => pruneForm.setData('days', Number(e.target.value))}
                            className="w-24"
                            title="Prune entries older than this many days"
                        />
                        <Button type="submit" variant="outline" disabled={pruneForm.processing}>
                            Prune older
                        </Button>
                    </form>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row">
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search by record ID, action, or model…"
                        className="sm:max-w-xs"
                    />

                    <Select
                        value={action}
                        onValueChange={(value) => {
                            setAction(value);
                            apply({ action: value });
                        }}
                    >
                        <SelectTrigger className="sm:max-w-xs">
                            <SelectValue placeholder="All actions" />
                        </SelectTrigger>
                        <SelectContent>
                            {actions.map((option) => (
                                <SelectItem key={option} value={option}>
                                    {option}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Input
                        value={model}
                        onChange={(e) => {
                            setModel(e.target.value);
                            apply({ model: e.target.value });
                        }}
                        placeholder="Filter by model…"
                        className="sm:max-w-xs"
                    />

                    {isFiltered && (
                        <Button variant="ghost" onClick={clear}>
                            Clear
                        </Button>
                    )}
                </div>

                <Card>
                    <CardContent className="p-0">
                        {logs.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">
                                {isFiltered ? 'No audit entries match these filters.' : 'No audit entries yet.'}
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">When</th>
                                            <th className="px-6 py-3 font-medium">Actor</th>
                                            <th className="px-6 py-3 font-medium">Action</th>
                                            <th className="px-6 py-3 font-medium">Record</th>
                                            <th className="px-6 py-3 font-medium">Changes</th>
                                            <th className="px-6 py-3 font-medium">
                                                <span className="sr-only">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {logs.map((log) => (
                                            <tr key={log.id} className="hover:bg-muted/50">
                                                <td className="text-muted-foreground px-6 py-4 whitespace-nowrap">
                                                    {new Date(log.created_at).toLocaleString()}
                                                </td>
                                                <td className="px-6 py-4">{log.actor?.name ?? <span className="italic">System</span>}</td>
                                                <td className="px-6 py-4">
                                                    <Badge variant="secondary">{log.action}</Badge>
                                                </td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {shortModel(log.auditable_type)}{' '}
                                                    <span className="font-mono text-xs">{log.auditable_id.slice(0, 8)}</span>
                                                </td>
                                                <td className="max-w-xs px-6 py-4">
                                                    <details>
                                                        <summary className="cursor-pointer text-xs underline">View</summary>
                                                        <pre className="bg-muted mt-2 overflow-x-auto rounded p-2 text-xs">
                                                            {JSON.stringify({ old: log.old_values, new: log.new_values }, null, 2)}
                                                        </pre>
                                                    </details>
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    <Button variant="outline" size="sm" onClick={() => destroy(log.id)}>
                                                        Delete
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
