import React from 'react';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

import RatingStars from '../components/RatingStars';
import LocaleProvider from '../i18n/LocaleProvider';
import { createTranslator, localeMessages } from '../i18n/messages';
import { DEFAULT_LANG, type Lang } from '../i18n/config';

type ControlledProps = {
    initialValue?: number;
    onChange?: (value: number) => void;
};

function ControlledRating({ initialValue = 3, onChange }: ControlledProps) {
    const [value, setValue] = React.useState(initialValue);

    return (
        <RatingStars
            value={value}
            onChange={(nextValue) => {
                setValue(nextValue);
                onChange?.(nextValue);
            }}
            aria-label="Рейтинг"
        />
    );
}

function renderWithLocale(ui: React.ReactNode, lang: Lang = DEFAULT_LANG) {
    return render(<LocaleProvider initial={lang}>{ui}</LocaleProvider>);
}

const uaT = createTranslator(localeMessages.uk);

describe('RatingStars', () => {
    it('calls onChange when selecting a star with the mouse', async () => {
        const handleChange = vi.fn();
        const user = userEvent.setup();

        renderWithLocale(<ControlledRating initialValue={2} onChange={handleChange} />);

        const expectedLabel = uaT('product.ratingStars.option', { value: 4, max: 5 });
        const star = screen.getByRole('radio', { name: expectedLabel });
        await user.click(star);

        expect(handleChange).toHaveBeenCalledWith(4);
        expect(star).toHaveAttribute('aria-checked', 'true');
    });

    it('supports keyboard navigation and updates the selection', async () => {
        const handleChange = vi.fn();
        const user = userEvent.setup();

        renderWithLocale(<ControlledRating initialValue={2} onChange={handleChange} />);

        await user.tab();
        const selected = screen.getByRole('radio', {
            name: uaT('product.ratingStars.option', { value: 2, max: 5 }),
        });
        expect(selected).toHaveFocus();

        await user.keyboard('{ArrowRight}');
        expect(handleChange).toHaveBeenLastCalledWith(3);

        const thirdStar = screen.getByRole('radio', {
            name: uaT('product.ratingStars.option', { value: 3, max: 5 }),
        });
        expect(thirdStar).toHaveFocus();

        await user.keyboard('{Home}');
        expect(handleChange).toHaveBeenLastCalledWith(1);

        const firstStar = screen.getByRole('radio', {
            name: uaT('product.ratingStars.option', { value: 1, max: 5 }),
        });
        expect(firstStar).toHaveFocus();
    });
});
