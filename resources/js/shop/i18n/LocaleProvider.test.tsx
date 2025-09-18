import React from 'react';
import { describe, expect, it } from 'vitest';
import { render, screen } from '@testing-library/react';

import LocaleProvider, { useLocale } from './LocaleProvider';
import { SUPPORTED_LANGS } from './config';
import { localeMessages } from './messages';

function MessagesProbe() {
    const { messages } = useLocale();
    return <span>{messages.languageName}</span>;
}

describe('LocaleProvider', () => {
    SUPPORTED_LANGS.forEach((lang) => {
        it(`exposes translations for ${lang}`, () => {
            render(
                <LocaleProvider initial={lang}>
                    <MessagesProbe />
                </LocaleProvider>
            );

            expect(screen.getByText(localeMessages[lang].languageName)).toBeInTheDocument();
        });
    });
});

