import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Building2, Contact, Globe, Laptop, LayoutGrid, Monitor, ScrollText, Server, ShieldCheck, Smartphone, Users } from 'lucide-react';
import AppLogo from './app-logo';

const allNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Companies',
        url: '/companies',
        icon: Building2,
        permissionPrefix: 'companies',
    },
    {
        title: 'Domains',
        url: '/domains',
        icon: Globe,
        permissionPrefix: 'domains',
    },
    {
        title: 'Servers',
        url: '/servers',
        icon: Server,
        permissionPrefix: 'servers',
    },
    {
        title: 'Employees',
        url: '/employees',
        icon: Contact,
        permissionPrefix: 'employees',
    },
    {
        title: 'Device Types',
        url: '/device-types',
        icon: Monitor,
        permissionPrefix: 'device-types',
    },
    {
        title: 'Devices',
        url: '/devices',
        icon: Laptop,
        permissionPrefix: 'devices',
    },
    {
        title: 'SIM Cards',
        url: '/sim-cards',
        icon: Smartphone,
        permissionPrefix: 'sim-cards',
    },
    {
        title: 'Users',
        url: '/users',
        icon: Users,
        permissionPrefix: 'users',
    },
    {
        title: 'Roles',
        url: '/roles',
        icon: ShieldCheck,
        permissionPrefix: 'roles',
    },
    {
        title: 'Audit Logs',
        url: '/audit-logs',
        icon: ScrollText,
        permissionPrefix: 'audit-logs',
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Repository',
    //     url: 'https://github.com/laravel/react-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     url: 'https://laravel.com/docs/starter-kits',
    //     icon: BookOpen,
    // },
];

function hasModuleAccess(permissions: string[], prefix: string): boolean {
    return permissions.some((permission) => permission.startsWith(`${prefix}.`));
}

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const permissions = auth.permissions ?? [];

    const items = allNavItems
        .filter((item) => !item.permissionPrefix || auth.is_super_admin || hasModuleAccess(permissions, item.permissionPrefix))
        .map((item) => {
            // Users who can create but not view (e.g. only `devices.create`)
            // land on the create page instead of a forbidden index page.
            if (
                item.permissionPrefix &&
                !auth.is_super_admin &&
                !permissions.includes(`${item.permissionPrefix}.view`) &&
                permissions.includes(`${item.permissionPrefix}.create`)
            ) {
                return { ...item, url: `${item.url}/create` };
            }

            return item;
        });

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={items} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
