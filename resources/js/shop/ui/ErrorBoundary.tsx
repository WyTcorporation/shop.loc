import React from 'react';
import {useLocation, Link} from 'react-router-dom';
import {reportError} from '../monitoring';


type State = { error: Error | null };
type Props = { children: React.ReactNode };


class Boundary extends React.Component<React.PropsWithChildren, State, Props> {
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

        return (
            <div className="mx-auto max-w-xl p-6 text-center">
                <h2 className="mb-2 text-xl font-semibold">Щось пішло не так</h2>
                <p className="mb-4 text-sm text-muted-foreground">
                    {this.state.error.message || 'Несподівана помилка.'}
                </p>
                <div className="flex justify-center gap-3">
                    <button
                        onClick={() => window.location.reload()}
                        className="rounded-md border px-3 py-2 text-sm"
                    >
                        Перезавантажити
                    </button>
                    <Link to="/" className="rounded-md border px-3 py-2 text-sm">
                        На головну
                    </Link>
                </div>
            </div>
        );
    }
}

export function AppErrorBoundary({children}: React.PropsWithChildren) {
    const location = useLocation();
    // ключ змінюється при навігації → Boundary скидає state
    return <Boundary key={location.key}>{children}</Boundary>;
}
