import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { SubmitEventHandler, useState } from 'react';

interface ServerData {
    id: string;
    name: string;
    type: string;
    ip_address: string;
    port: number | null;
    company: { id: string; name: string };
    creator: { id: string; name: string } | null;
}

interface CredentialData {
    id: string;
    username: string;
    created_at: string;
}

interface SshKeyData {
    id: string;
    name: string;
    public_key: string | null;
    created_at: string;
}

const textareaClassName =
    'border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:cursor-not-allowed disabled:opacity-50';

export default function ServersShow({
    server,
    credentials,
    sshKeys,
    can,
}: {
    server: ServerData;
    credentials: CredentialData[];
    sshKeys: SshKeyData[];
    can: {
        viewCredentials: boolean;
        createCredentials: boolean;
        deleteCredentials: boolean;
        viewSshKeys: boolean;
        createSshKeys: boolean;
        deleteSshKeys: boolean;
    };
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Servers', href: '/servers' },
        { title: server.name, href: `/servers/${server.id}` },
    ];

    const credentialForm = useForm({ username: '', password: '' });
    const keyForm = useForm({ name: '', public_key: '', private_key: '' });

    // Revealed secrets live only in component state: they are fetched
    // on demand, cleared on hide, and never persisted anywhere.
    const [revealedPasswords, setRevealedPasswords] = useState<Record<string, string>>({});
    const [revealedKeys, setRevealedKeys] = useState<Record<string, string>>({});
    const [loadingId, setLoadingId] = useState<string | null>(null);
    const [copiedId, setCopiedId] = useState<string | null>(null);

    const fetchSecret = async (url: string, id: string, store: (value: string) => void) => {
        setLoadingId(id);
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            store(data.password ?? data.private_key ?? '');
        } finally {
            setLoadingId(null);
        }
    };

    const forget = (id: string, setState: React.Dispatch<React.SetStateAction<Record<string, string>>>) => {
        setState((prev) => {
            const next = { ...prev };
            delete next[id];
            return next;
        });
    };

    const toggleCredential = (id: string) => {
        if (revealedPasswords[id]) {
            forget(id, setRevealedPasswords);
            return;
        }
        void fetchSecret(route('servers.credentials.reveal', [server.id, id]), id, (value) =>
            setRevealedPasswords((prev) => ({ ...prev, [id]: value })),
        );
    };

    const toggleKey = (id: string) => {
        if (revealedKeys[id]) {
            forget(id, setRevealedKeys);
            return;
        }
        void fetchSecret(route('servers.ssh-keys.reveal', [server.id, id]), id, (value) =>
            setRevealedKeys((prev) => ({ ...prev, [id]: value })),
        );
    };

    const copy = async (id: string, value: string) => {
        await navigator.clipboard.writeText(value);
        setCopiedId(id);
        setTimeout(() => setCopiedId((current) => (current === id ? null : current)), 1500);
    };

    const submitCredential: SubmitEventHandler = (e) => {
        e.preventDefault();
        credentialForm.post(route('servers.credentials.store', server.id), {
            onSuccess: () => credentialForm.reset(),
        });
    };

    const submitKey: SubmitEventHandler = (e) => {
        e.preventDefault();
        keyForm.post(route('servers.ssh-keys.store', server.id), {
            onSuccess: () => keyForm.reset(),
        });
    };

    const removeCredential = (id: string) => {
        if (window.confirm('Remove this credential?')) {
            router.delete(route('servers.credentials.destroy', [server.id, id]));
        }
    };

    const removeKey = (id: string) => {
        if (window.confirm('Remove this SSH key?')) {
            router.delete(route('servers.ssh-keys.destroy', [server.id, id]));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={server.name} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading title={server.name} description={`${server.ip_address} : ${server.port ?? 'default port'}`} />
                    <Badge variant="secondary" className="uppercase">
                        {server.type}
                    </Badge>
                </div>

                <Card className="max-w-3xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-base">Details</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <span className="text-muted-foreground">Company: </span>
                            {server.company.name}
                        </div>
                        <div>
                            <span className="text-muted-foreground">Created by: </span>
                            {server.creator?.name ?? <span className="italic">Unknown</span>}
                        </div>
                    </CardContent>
                </Card>

                <Card className="max-w-3xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-base">Credentials</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        {credentials.length === 0 ? (
                            <p className="text-muted-foreground text-sm">No credentials yet. Passwords are stored encrypted and never shown.</p>
                        ) : (
                            <ul className="divide-y rounded-md border">
                                {credentials.map((credential) => (
                                    <li key={credential.id} className="flex flex-col gap-2 px-4 py-2 text-sm">
                                        <div className="flex items-center justify-between">
                                            <span className="font-medium">{credential.username}</span>
                                            <div className="flex items-center gap-1">
                                                {can.viewCredentials && (
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => toggleCredential(credential.id)}
                                                        disabled={loadingId === credential.id}
                                                    >
                                                        {revealedPasswords[credential.id] ? 'Hide' : 'Show'}
                                                    </Button>
                                                )}
                                                {can.deleteCredentials && (
                                                    <Button variant="ghost" size="sm" onClick={() => removeCredential(credential.id)}>
                                                        Remove
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                        {revealedPasswords[credential.id] && (
                                            <div className="flex items-center gap-2">
                                                <Input readOnly value={revealedPasswords[credential.id]} className="font-mono" />
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => copy(credential.id, revealedPasswords[credential.id])}
                                                >
                                                    {copiedId === credential.id ? 'Copied' : 'Copy'}
                                                </Button>
                                            </div>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}

                        {can.createCredentials && (
                            <form onSubmit={submitCredential} className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="username">Username</Label>
                                <Input
                                    id="username"
                                    value={credentialForm.data.username}
                                    onChange={(e) => credentialForm.setData('username', e.target.value)}
                                    required
                                    maxLength={255}
                                    placeholder="e.g. deploy"
                                />
                                <InputError message={credentialForm.errors.username} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    value={credentialForm.data.password}
                                    onChange={(e) => credentialForm.setData('password', e.target.value)}
                                    required
                                    placeholder="Stored encrypted"
                                />
                                <InputError message={credentialForm.errors.password} />
                            </div>
                            <div className="sm:col-span-2">
                                <Button type="submit" disabled={credentialForm.processing}>
                                    Add credential
                                </Button>
                            </div>
                            </form>
                        )}
                    </CardContent>
                </Card>

                <Card className="max-w-3xl">
                    <CardHeader className="pb-3">
                        <CardTitle className="text-base">SSH keys</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        {sshKeys.length === 0 ? (
                            <p className="text-muted-foreground text-sm">No SSH keys yet. Private keys are stored encrypted and never shown.</p>
                        ) : (
                            <ul className="divide-y rounded-md border">
                                {sshKeys.map((key) => (
                                    <li key={key.id} className="flex flex-col gap-2 px-4 py-2 text-sm">
                                        <div className="flex items-center justify-between">
                                            <div className="flex min-w-0 flex-col">
                                                <span className="font-medium">{key.name}</span>
                                                {key.public_key && (
                                                    <span className="text-muted-foreground truncate font-mono text-xs">{key.public_key}</span>
                                                )}
                                            </div>
                                            <div className="flex shrink-0 items-center gap-1">
                                                {can.viewSshKeys && (
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => toggleKey(key.id)}
                                                        disabled={loadingId === key.id}
                                                    >
                                                        {revealedKeys[key.id] ? 'Hide' : 'Show'}
                                                    </Button>
                                                )}
                                                {can.deleteSshKeys && (
                                                    <Button variant="ghost" size="sm" onClick={() => removeKey(key.id)}>
                                                        Remove
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                        {revealedKeys[key.id] && (
                                            <div className="flex items-center gap-2">
                                                <Input readOnly value={revealedKeys[key.id]} className="font-mono" />
                                                <Button variant="outline" size="sm" onClick={() => copy(key.id, revealedKeys[key.id])}>
                                                    {copiedId === key.id ? 'Copied' : 'Copy'}
                                                </Button>
                                            </div>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}

                        {can.createSshKeys && (
                            <form onSubmit={submitKey} className="grid gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="key-name">Name</Label>
                                <Input
                                    id="key-name"
                                    value={keyForm.data.name}
                                    onChange={(e) => keyForm.setData('name', e.target.value)}
                                    required
                                    maxLength={255}
                                    placeholder="e.g. Deploy key"
                                />
                                <InputError message={keyForm.errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="public_key">Public key</Label>
                                <textarea
                                    id="public_key"
                                    className={textareaClassName}
                                    value={keyForm.data.public_key}
                                    onChange={(e) => keyForm.setData('public_key', e.target.value)}
                                    placeholder="ssh-ed25519 AAAA..."
                                />
                                <InputError message={keyForm.errors.public_key} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="private_key">Private key</Label>
                                <textarea
                                    id="private_key"
                                    className={textareaClassName}
                                    value={keyForm.data.private_key}
                                    onChange={(e) => keyForm.setData('private_key', e.target.value)}
                                    required
                                    placeholder="Paste OpenSSH private key here"
                                />
                                <InputError message={keyForm.errors.private_key} />
                            </div>
                            <div>
                                <Button type="submit" disabled={keyForm.processing}>
                                    Add SSH key
                                </Button>
                            </div>
                            </form>
                        )}
                    </CardContent>
                </Card>

                <div>
                    <Button variant="ghost" asChild>
                        <Link href={route('servers.index')}>Back to servers</Link>
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
