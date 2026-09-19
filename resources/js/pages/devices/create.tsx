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
    id: string;
    name: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Devices',
        href: '/devices',
    },
    {
        title: 'Create',
        href: '/devices/create',
    },
];

export default function DevicesCreate({ companies, types }: { companies: CompanyOption[]; types: TypeOption[] }) {
    const { data, setData, post, processing, errors } = useForm({
        company_id: '',
        device_type_id: '',
        brand: '',
        model: '',
        imei: '',
        mac_address: '',
        serial_no: '',
    });

    const submit: SubmitEventHandler = (e) => {
        e.preventDefault();

        post(route('devices.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create device" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Heading title="Create device" description="Add a new device to the system" />

                <Card className="max-w-2xl">
                    <CardContent className="pt-6">
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid gap-2 sm:grid-cols-2 sm:gap-4">
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
                                    <Label htmlFor="device_type_id">Type</Label>

                                    <Select value={data.device_type_id} onValueChange={(value) => setData('device_type_id', value)}>
                                        <SelectTrigger id="device_type_id">
                                            <SelectValue placeholder="Select a type" />
                                        </SelectTrigger>

                                        <SelectContent>
                                            {types.map((type) => (
                                                <SelectItem key={type.id} value={type.id}>
                                                    {type.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    <InputError message={errors.device_type_id} />
                                </div>
                            </div>

                            <div className="grid gap-2 sm:grid-cols-2 sm:gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="brand">Brand</Label>

                                    <Input
                                        id="brand"
                                        value={data.brand}
                                        onChange={(e) => setData('brand', e.target.value)}
                                        required
                                        maxLength={255}
                                        placeholder="e.g. Dell"
                                    />

                                    <InputError message={errors.brand} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="model">Model</Label>

                                    <Input
                                        id="model"
                                        value={data.model}
                                        onChange={(e) => setData('model', e.target.value)}
                                        required
                                        maxLength={255}
                                        placeholder="e.g. Latitude 5440"
                                    />

                                    <InputError message={errors.model} />
                                </div>
                            </div>

                            <div className="grid gap-2 sm:grid-cols-2 sm:gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="imei">IMEI</Label>

                                    <Input
                                        id="imei"
                                        value={data.imei}
                                        onChange={(e) => setData('imei', e.target.value)}
                                        maxLength={30}
                                        placeholder="Optional"
                                    />

                                    <InputError message={errors.imei} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="mac_address">MAC address</Label>

                                    <Input
                                        id="mac_address"
                                        value={data.mac_address}
                                        onChange={(e) => setData('mac_address', e.target.value)}
                                        maxLength={30}
                                        placeholder="Optional"
                                    />

                                    <InputError message={errors.mac_address} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="serial_no">Serial number</Label>

                                <Input
                                    id="serial_no"
                                    value={data.serial_no}
                                    onChange={(e) => setData('serial_no', e.target.value)}
                                    maxLength={100}
                                    placeholder="Optional"
                                />

                                <InputError message={errors.serial_no} />
                            </div>

                            <p className="text-muted-foreground text-sm">An asset code is generated automatically on creation.</p>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    Create device
                                </Button>

                                <Button variant="ghost" asChild>
                                    <Link href={route('devices.index')}>Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
