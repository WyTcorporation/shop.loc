import { NavLink } from 'react-router-dom';

const links = [
    { to: '/profile', label: 'Профіль', end: true },
    { to: '/profile/orders', label: 'Мої замовлення' },
    { to: '/profile/addresses', label: 'Збережені адреси' },
    { to: '/profile/points', label: 'Бонусні бали' },
] as const;

export default function ProfileNavigation() {
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
