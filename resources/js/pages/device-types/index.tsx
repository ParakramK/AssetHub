import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export interface DeviceType {
    id: string;
    name: string;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Device Types',
        href: '/device-types',
    },
];

export default function DeviceTypesIndex({ types, filters }: { types: DeviceType[]; filters: { search: string } }) {
    const [search, setSearch] = useState(filters.search);

    useEffect(() => {
        if (search === filters.search) {
            return;
        }

        const id = setTimeout(() => {
            router.get(route('device-types.index'), search !== '' ? { search } : {}, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const clear = () => {
        setSearch('');
        router.get(route('device-types.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Device Types" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Device Types" description="Categories of devices tracked in the system" />
                    <Button asChild>
                        <Link href={route('device-types.create')}>Create device type</Link>
                    </Button>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row">
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search by name…"
                        className="sm:max-w-xs"
                    />

                    {search !== '' && (
                        <Button variant="ghost" onClick={clear}>
                            Clear
                        </Button>
                    )}
                </div>

                <Card>
                    <CardContent className="p-0">
                        {types.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">
                                {search !== '' ? 'No device types match this search.' : 'No device types yet. Create the first one to get started.'}
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Name</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {types.map((type) => (
                                            <tr key={type.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{type.name}</td>
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
