import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

export interface Company {
    id: string;
    name: string;
    address: string | null;
    phone: string | null;
    email: string | null;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Companies',
        href: '/companies',
    },
];

export default function CompaniesIndex({ companies }: { companies: Company[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Companies" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Companies" description="Manage companies and their contact details" />
                    <Button asChild>
                        <Link href={route('companies.create')}>Create company</Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {companies.length === 0 ? (
                            <p className="text-muted-foreground p-6 text-sm">No companies yet. Create the first company to get started.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Name</th>
                                            <th className="px-6 py-3 font-medium">Email</th>
                                            <th className="px-6 py-3 font-medium">Phone</th>
                                            <th className="px-6 py-3 font-medium">Address</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {companies.map((company) => (
                                            <tr key={company.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{company.name}</td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {company.email || <span className="italic">No email</span>}
                                                </td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {company.phone || <span className="italic">No phone</span>}
                                                </td>
                                                <td className="text-muted-foreground px-6 py-4">
                                                    {company.address || <span className="italic">No address</span>}
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
