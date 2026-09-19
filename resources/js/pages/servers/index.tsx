import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Servers',
        href: '/servers',
    },
];

export default function ServersIndex({ servers }: { servers: Server[] }) {
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

                <Card>
                    <CardContent className="p-0">
                        {servers.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">No servers yet. Create the first server to get started.</p>
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
