import { NavLink } from 'react-router-dom';
import { useLocale } from '../i18n/LocaleProvider';

export default function ProfileNavigation() {
    const { t } = useLocale();
    const links = [
        { to: '/profile', label: t('profile.navigation.overview'), end: true },
        { to: '/profile/orders', label: t('profile.navigation.orders') },
        { to: '/profile/addresses', label: t('profile.navigation.addresses') },
        { to: '/profile/points', label: t('profile.navigation.points') },
    ] as const;

    return (
        <nav className="mb-6 w-full overflow-x-auto">
            <ul className="flex gap-2 text-sm">
                {links.map((link) => (
                    <li key={link.to}>
                        <NavLink
                            to={link.to}
                            end={link.end}
                            className={({ isActive }) =>
                                [
                                    'inline-flex items-center rounded-full border px-3 py-1 transition-colors',
                                    isActive
                                        ? 'border-black bg-black text-white'
                                        : 'border-gray-200 text-gray-700 hover:border-black hover:text-black',
                                ].join(' ')
                            }
                        >
                            {link.label}
                        </NavLink>
                    </li>
                ))}
            </ul>
        </nav>
    );
}
