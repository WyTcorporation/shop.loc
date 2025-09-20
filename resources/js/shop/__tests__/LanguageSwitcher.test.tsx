import React from 'react';
import { describe, expect, it, beforeEach, afterEach, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import LanguageSwitcher, { swapLangInPath } from '../components/LanguageSwitcher';
import LocaleProvider from '../i18n/LocaleProvider';
import { SUPPORTED_LANGS, type Lang } from '../i18n/config';

function stubLocation(url: string) {
    const parsed = new URL(url);
    const assign = vi.fn();
    const locationMock = {
        ancestorOrigins: {
            length: 0,
            item: () => null,
            contains: () => false,
        },
        href: parsed.href,
        origin: parsed.origin,
        protocol: parsed.protocol,
        host: parsed.host,
        hostname: parsed.hostname,
        port: parsed.port,
        pathname: parsed.pathname,
        search: parsed.search,
        hash: parsed.hash,
        assign,
        reload: vi.fn(),
        replace: vi.fn(),
        toString: () => parsed.href,
    } as unknown as Location;

    vi.stubGlobal('location', locationMock);

    return assign;
}

function renderSwitcher(initial: Lang) {
    return render(
        <LocaleProvider initial={initial}>
            <LanguageSwitcher />
        </LocaleProvider>
    );
}

describe('LanguageSwitcher', () => {
    beforeEach(() => {
        stubLocation('http://localhost/uk/catalog');
    });

    afterEach(() => {
        vi.unstubAllGlobals();
        vi.restoreAllMocks();
    });

    it('renders language buttons in the configured order', () => {
        renderSwitcher('uk');

        const buttons = screen.getAllByRole('button');
        const labels = buttons.map((button) => button.textContent);

        expect(labels).toEqual(SUPPORTED_LANGS.map((lang) => lang.toUpperCase()));
    });

    it('replaces the language prefix in the current path', async () => {
        const assignMock = stubLocation('http://localhost/en/catalog?foo=1#hash');
        const user = userEvent.setup();

        renderSwitcher('en');

        await user.click(screen.getByRole('button', { name: 'RU' }));

        expect(assignMock).toHaveBeenCalledWith('/ru/catalog?foo=1#hash');
    });

    it('inserts the language prefix when it is missing', async () => {
        const assignMock = stubLocation('http://localhost/catalog');
        const user = userEvent.setup();

        renderSwitcher('uk');

        await user.click(screen.getByRole('button', { name: 'PT' }));

        expect(assignMock).toHaveBeenCalledWith('/pt/catalog');
    });
});

describe('swapLangInPath', () => {
    afterEach(() => {
        vi.unstubAllGlobals();
        vi.restoreAllMocks();
    });

    it('removes the language segment when switching to Ukrainian', () => {
        stubLocation('http://localhost/en/catalog');

        expect(swapLangInPath('uk')).toBe('/catalog');
    });

    it('adds or replaces the language segment for non-default languages', () => {
        stubLocation('http://localhost/catalog');

        expect(swapLangInPath('ru')).toBe('/ru/catalog');

        stubLocation('http://localhost/pt/catalog');

        expect(swapLangInPath('en')).toBe('/en/catalog');
    });
});

