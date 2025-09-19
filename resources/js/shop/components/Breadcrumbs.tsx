import React from 'react';
import { Link } from 'react-router-dom';
import { useLocale } from '../i18n/LocaleProvider';

type Crumb = { label: string; href?: string };

export default function Breadcrumbs({ items }: { items: Crumb[] }) {
    const { t } = useLocale();

    if (!items?.length) return null;

    return (
        <nav
            aria-label={t('common.navigation.breadcrumbAria')}
            className="mb-4 text-sm text-muted-foreground"
            data-testid="breadcrumbs"
        >
            <ol className="flex flex-wrap items-center gap-1">
                {items.map((it, i) => {
                    const isLast = i === items.length - 1;
                    const testId =
                        i === 0 ? 'bc-home' : isLast ? 'bc-current' : 'bc-category';

                    return (
                        <li key={i} className="flex items-center gap-1">
                            {it.href && !isLast ? (
                                <Link
                                    to={it.href}
                                    className="hover:underline"
                                    data-testid={testId}
                                >
                                    {it.label}
                                </Link>
                            ) : (
                                <span
                                    className={isLast ? 'text-foreground' : undefined}
                                    data-testid={testId}
                                >
                  {it.label}
                </span>
                            )}
                            {!isLast && <span className="mx-1 opacity-60">/</span>}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}
