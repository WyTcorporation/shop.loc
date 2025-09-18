import React from 'react';
import { describe, expect, it } from 'vitest';
import { render, screen } from '@testing-library/react';

import LocaleProvider, { useLocale } from './LocaleProvider';
import { SUPPORTED_LANGS, resolveLocale } from './config';
import { localeMessages } from './messages';

function MessagesProbe() {
    const { messages, locale } = useLocale();
    return (
        <>
            <span data-testid="language-name">{messages.languageName}</span>
            <span data-testid="active-locale">{locale}</span>
        </>
    );
}

describe('LocaleProvider', () => {
    SUPPORTED_LANGS.forEach((lang) => {
        it(`exposes translations for ${lang}`, () => {
            render(
                <LocaleProvider initial={lang}>
                    <MessagesProbe />
                </LocaleProvider>
            );
            expect(screen.getByTestId('language-name')).toHaveTextContent(localeMessages[lang].languageName);
            expect(screen.getByTestId('active-locale')).toHaveTextContent(resolveLocale(lang));
        });
    });
});

