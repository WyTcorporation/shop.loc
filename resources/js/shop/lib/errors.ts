type Fallback = string | (() => string);

function resolveFallback(fallback: Fallback) {
    return typeof fallback === 'function' ? fallback() : fallback;
}

export function resolveErrorMessage(error: unknown, fallback: Fallback = 'Сталася помилка. Спробуйте ще раз.') {
    if (!error) return resolveFallback(fallback);

    const maybeResponse = (error as { response?: { data?: unknown } })?.response?.data;

    if (maybeResponse) {
        if (typeof maybeResponse === 'string') {
            return maybeResponse;
        }

        if (typeof maybeResponse === 'object') {
            const message = (maybeResponse as { message?: unknown }).message;
            if (typeof message === 'string' && message.trim()) {
                return message;
            }

            const errors = (maybeResponse as { errors?: Record<string, unknown> }).errors;
            if (errors && typeof errors === 'object') {
                for (const value of Object.values(errors)) {
                    if (!value) continue;
                    if (Array.isArray(value) && value.length) {
                        const [first] = value;
                        if (typeof first === 'string' && first.trim()) {
                            return first;
                        }
                    }
                    if (typeof value === 'string' && value.trim()) {
                        return value;
                    }
                }
            }
        }
    }

    if (error instanceof Error && error.message) {
        return error.message;
    }

    return resolveFallback(fallback);
}
