import React from 'react';
import {useLocation, Link} from 'react-router-dom';
import {reportError} from '../monitoring';
import { useLocale } from '../i18n/LocaleProvider';
import type { Translator } from '../i18n/messages';


type State = { error: Error | null };
type BoundaryProps = React.PropsWithChildren<{ t: Translator }>;


class Boundary extends React.Component<BoundaryProps, State> {
    state: State = {error: null};

    static getDerivedStateFromError(error: Error): State {
        return {error};
    }

    componentDidCatch(error: Error, info: React.ErrorInfo) {
        reportError(error, { componentStack: info.componentStack });
        console.error('ErrorBoundary caught:', error, info);
    }

    render() {
        if (!this.state.error) return this.props.children;

        const { t } = this.props;
        const message = this.state.error?.message || t('common.errorBoundary.descriptionFallback');

        return (
            <div className="mx-auto max-w-xl p-6 text-center">
                <h2 className="mb-2 text-xl font-semibold">{t('common.errorBoundary.title')}</h2>
                <p className="mb-4 text-sm text-muted-foreground">{message}</p>
                <div className="flex justify-center gap-3">
                    <button
                        onClick={() => window.location.reload()}
                        className="rounded-md border px-3 py-2 text-sm"
                    >
                        {t('common.errorBoundary.reload')}
                    </button>
                    <Link to="/" className="rounded-md border px-3 py-2 text-sm">
                        {t('common.errorBoundary.home')}
                    </Link>
                </div>
            </div>
        );
    }
}

export function AppErrorBoundary({children}: React.PropsWithChildren) {
    const location = useLocation();
    const { t } = useLocale();
    // ключ змінюється при навігації → Boundary скидає state
    return <Boundary key={location.key} t={t}>{children}</Boundary>;
}
