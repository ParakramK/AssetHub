import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { SubmitEventHandler } from 'react';

interface EmployeeData {
    id: string;
    name: string;
    email: string;
    mobile_no: string | null;
    company: { id: string; name: string };
}

interface CurrentSim {
    id: string;
    number: string;
    provider: string;
    assignment_id: string | null;
}

interface HistoryRow {
    id: string;
    assigned_at: string;
    returned_at: string | null;
    sim_card: { id: string; number: string };
}

interface AvailableSim {
    id: string;
    number: string;
    provider: string;
}

interface CurrentDevice {
    id: string;
    code: string;
    brand: string;
    model: string;
    assignment_id: string | null;
}

interface DeviceHistoryRow {
    id: string;
    assigned_at: string;
    returned_at: string | null;
    device: { id: string; code: string };
}

interface AvailableDevice {
    id: string;
    code: string;
    brand: string;
    model: string;
}

export default function EmployeesShow({
    employee,
    currentSims,
    history,
    availableSims,
    currentDevices,
    deviceHistory,
    availableDevices,
}: {
    employee: EmployeeData;
    currentSims: CurrentSim[];
    history: HistoryRow[];
    availableSims: AvailableSim[];
    currentDevices: CurrentDevice[];
    deviceHistory: DeviceHistoryRow[];
    availableDevices: AvailableDevice[];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Employees', href: '/employees' },
        { title: employee.name, href: `/employees/${employee.id}` },
    ];

    const { data, setData, post, processing, errors, reset } = useForm({
        sim_card_id: '',
    });

    const submit: SubmitEventHandler = (e) => {
        e.preventDefault();
        post(route('employees.sim-assignments.store', employee.id), {
            onSuccess: () => reset(),
        });
    };

    const returnSim = (id: string) => {
        if (window.confirm('Mark this SIM as returned to IT?')) {
            router.post(route('sim-assignments.return', id));
        }
    };

    const deviceForm = useForm({
        device_id: '',
    });

    const submitDevice: SubmitEventHandler = (e) => {
        e.preventDefault();
        deviceForm.post(route('employees.device-assignments.store', employee.id), {
            onSuccess: () => deviceForm.reset(),
        });
    };

    const returnDevice = (id: string) => {
        if (window.confirm('Mark this device as returned?')) {
            router.post(route('device-assignments.return', id));
        }
    };

    const formatDateTime = (value: string) => new Date(value).toLocaleString();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={employee.name} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Heading title={employee.name} description={employee.company.name} />

                <Card className="max-w-3xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-base">Details</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <span className="text-muted-foreground">Email: </span>
                            {employee.email}
                        </div>
                        <div>
                            <span className="text-muted-foreground">Mobile: </span>
                            {employee.mobile_no || <span className="italic">No mobile</span>}
                        </div>
                    </CardContent>
                </Card>

                <Card className="max-w-3xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-base">Current SIMs</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        {currentSims.length === 0 ? (
                            <p className="text-muted-foreground text-sm">No SIMs currently assigned.</p>
                        ) : (
                            <ul className="divide-y rounded-md border">
                                {currentSims.map((sim) => (
                                    <li key={sim.id} className="flex items-center justify-between px-4 py-2 text-sm">
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium">{sim.number}</span>
                                            <Badge variant="secondary" className="uppercase">
                                                {sim.provider}
                                            </Badge>
                                        </div>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => sim.assignment_id && returnSim(sim.assignment_id)}
                                            disabled={!sim.assignment_id}
                                        >
                                            Return
                                        </Button>
                                    </li>
                                ))}
                            </ul>
                        )}

                        {availableSims.length > 0 && (
                            <form onSubmit={submit} className="flex flex-col gap-2 sm:flex-row sm:items-end">
                                <div className="grid flex-1 gap-2">
                                    <Label htmlFor="sim_card_id">Assign a SIM</Label>
                                    <Select value={data.sim_card_id} onValueChange={(value) => setData('sim_card_id', value)}>
                                        <SelectTrigger id="sim_card_id">
                                            <SelectValue placeholder="Select an unassigned SIM" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {availableSims.map((sim) => (
                                                <SelectItem key={sim.id} value={sim.id}>
                                                    {sim.number} ({sim.provider.toUpperCase()})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.sim_card_id} />
                                </div>
                                <Button type="submit" disabled={processing}>
                                    Assign
                                </Button>
                            </form>
                        )}
                    </CardContent>
                </Card>

                <Card className="max-w-3xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-base">SIM history</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        {history.length === 0 ? (
                            <p className="text-muted-foreground p-6 pt-0 text-sm">No assignment history yet.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">SIM</th>
                                            <th className="px-6 py-3 font-medium">Assigned</th>
                                            <th className="px-6 py-3 font-medium">Returned</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {history.map((row) => (
                                            <tr key={row.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{row.sim_card.number}</td>
                                                <td className="text-muted-foreground px-6 py-4">{formatDateTime(row.assigned_at)}</td>
                                                <td className="px-6 py-4">
                                                    {row.returned_at ? (
                                                        <span className="text-muted-foreground">{formatDateTime(row.returned_at)}</span>
                                                    ) : (
                                                        <Badge>In use</Badge>
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

                <Card className="max-w-3xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-base">Current devices</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        {currentDevices.length === 0 ? (
                            <p className="text-muted-foreground text-sm">No devices currently assigned.</p>
                        ) : (
                            <ul className="divide-y rounded-md border">
                                {currentDevices.map((device) => (
                                    <li key={device.id} className="flex items-center justify-between px-4 py-2 text-sm">
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium">{device.code}</span>
                                            <span className="text-muted-foreground">
                                                {device.brand} {device.model}
                                            </span>
                                        </div>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => device.assignment_id && returnDevice(device.assignment_id)}
                                            disabled={!device.assignment_id}
                                        >
                                            Return
                                        </Button>
                                    </li>
                                ))}
                            </ul>
                        )}

                        {availableDevices.length > 0 && (
                            <form onSubmit={submitDevice} className="flex flex-col gap-2 sm:flex-row sm:items-end">
                                <div className="grid flex-1 gap-2">
                                    <Label htmlFor="device_id">Assign a device</Label>
                                    <Select value={deviceForm.data.device_id} onValueChange={(value) => deviceForm.setData('device_id', value)}>
                                        <SelectTrigger id="device_id">
                                            <SelectValue placeholder="Select an unassigned device" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {availableDevices.map((device) => (
                                                <SelectItem key={device.id} value={device.id}>
                                                    {device.code} ({device.brand} {device.model})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={deviceForm.errors.device_id} />
                                </div>
                                <Button type="submit" disabled={deviceForm.processing}>
                                    Assign
                                </Button>
                            </form>
                        )}
                    </CardContent>
                </Card>

                <Card className="max-w-3xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-base">Device history</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        {deviceHistory.length === 0 ? (
                            <p className="text-muted-foreground p-6 pt-0 text-sm">No device history yet.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="text-muted-foreground border-b text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Device</th>
                                            <th className="px-6 py-3 font-medium">Assigned</th>
                                            <th className="px-6 py-3 font-medium">Returned</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {deviceHistory.map((row) => (
                                            <tr key={row.id} className="hover:bg-muted/50">
                                                <td className="px-6 py-4 font-medium">{row.device.code}</td>
                                                <td className="text-muted-foreground px-6 py-4">{formatDateTime(row.assigned_at)}</td>
                                                <td className="px-6 py-4">
                                                    {row.returned_at ? (
                                                        <span className="text-muted-foreground">{formatDateTime(row.returned_at)}</span>
                                                    ) : (
                                                        <Badge>In use</Badge>
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

                <div>
                    <Button variant="ghost" asChild>
                        <Link href={route('employees.index')}>Back to employees</Link>
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
