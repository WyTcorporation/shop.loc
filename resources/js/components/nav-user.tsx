import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { UserInfo } from '@/components/user-info';
import { useIsMobile } from '@/hooks/use-mobile';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import { type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { ChevronsUpDown, LogOut, MapPin, Package, Sparkles, User as UserIcon } from 'lucide-react';

const profileLinks = [
    { href: '/profile', label: 'Профіль', icon: UserIcon },
    { href: '/profile/orders', label: 'Мої замовлення', icon: Package },
    { href: '/profile/addresses', label: 'Збережені адреси', icon: MapPin },
    { href: '/profile/points', label: 'Бонусні бали', icon: Sparkles },
] as const;

export function NavUser() {
    const { auth } = usePage<SharedData>().props;
    const { state } = useSidebar();
    const isMobile = useIsMobile();
    const cleanup = useMobileNavigation();

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton size="lg" className="group text-sidebar-accent-foreground data-[state=open]:bg-sidebar-accent">
                            <UserInfo user={auth.user} />
                            <ChevronsUpDown className="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="end"
                        side={isMobile ? 'bottom' : state === 'collapsed' ? 'left' : 'bottom'}
                    >
                        <DropdownMenuLabel className="p-0 font-normal">
                            <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <UserInfo user={auth.user} showEmail={true} />
                            </div>
                        </DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuGroup>
                            {profileLinks.map((item) => (
                                <DropdownMenuItem asChild key={item.href}>
                                    <a
                                        href={item.href}
                                        onClick={cleanup}
                                        className="flex items-center gap-2 px-1 py-1 text-sm text-foreground hover:no-underline"
                                    >
                                        <item.icon className="h-4 w-4" />
                                        {item.label}
                                    </a>
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuGroup>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <Link className="flex items-center gap-2" href={logout()} as="button" onClick={handleLogout}>
                                <LogOut className="h-4 w-4" />
                                Вийти
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
