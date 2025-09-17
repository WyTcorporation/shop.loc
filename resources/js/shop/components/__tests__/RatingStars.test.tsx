import React from 'react';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

import RatingStars from '../RatingStars';

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

describe('RatingStars', () => {
    it('calls onChange when selecting a star with the mouse', async () => {
        const handleChange = vi.fn();
        const user = userEvent.setup();

        render(<ControlledRating initialValue={2} onChange={handleChange} />);

        const star = screen.getByRole('radio', { name: 'Оцінка 4 з 5' });
        await user.click(star);

        expect(handleChange).toHaveBeenCalledWith(4);
        expect(star).toHaveAttribute('aria-checked', 'true');
    });

    it('supports keyboard navigation and updates the selection', async () => {
        const handleChange = vi.fn();
        const user = userEvent.setup();

        render(<ControlledRating initialValue={2} onChange={handleChange} />);

        await user.tab();
        const selected = screen.getByRole('radio', { name: 'Оцінка 2 з 5' });
        expect(selected).toHaveFocus();

        await user.keyboard('{ArrowRight}');
        expect(handleChange).toHaveBeenLastCalledWith(3);

        const thirdStar = screen.getByRole('radio', { name: 'Оцінка 3 з 5' });
        expect(thirdStar).toHaveFocus();

        await user.keyboard('{Home}');
        expect(handleChange).toHaveBeenLastCalledWith(1);

        const firstStar = screen.getByRole('radio', { name: 'Оцінка 1 з 5' });
        expect(firstStar).toHaveFocus();
    });
});
