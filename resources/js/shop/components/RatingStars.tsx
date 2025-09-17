import React from 'react';
import { cn } from '../../lib/utils';

type RatingStarsProps = {
    value: number;
    onChange: (value: number) => void;
    max?: number;
    className?: string;
} & Pick<React.AriaAttributes, 'aria-label' | 'aria-labelledby'>;

const clamp = (value: number, max: number) => {
    const upperBound = Math.max(1, Math.floor(max));
    return Math.min(Math.max(1, Math.round(value)), upperBound);
};

export default function RatingStars({
    value,
    onChange,
    max = 5,
    className,
    'aria-label': ariaLabel,
    'aria-labelledby': ariaLabelledby,
}: RatingStarsProps) {
    const normalizedMax = Math.max(1, Math.floor(max));
    const normalizedValue = clamp(value, normalizedMax);
    const [hoveredValue, setHoveredValue] = React.useState<number | null>(null);
    const buttonsRef = React.useRef<Array<HTMLButtonElement | null>>([]);

    const highlightValue = hoveredValue ?? normalizedValue;
    const values = React.useMemo(() => Array.from({ length: normalizedMax }, (_, index) => index + 1), [normalizedMax]);

    const focusButton = React.useCallback((index: number) => {
        const button = buttonsRef.current[index];
        if (button) {
            button.focus();
        }
    }, []);

    const handleSelect = React.useCallback(
        (nextValue: number) => {
            const newValue = clamp(nextValue, normalizedMax);
            if (newValue !== normalizedValue) {
                onChange(newValue);
            }
            setHoveredValue(null);
        },
        [normalizedMax, normalizedValue, onChange],
    );

    const handleKeyDown = React.useCallback(
        (event: React.KeyboardEvent<HTMLButtonElement>, index: number) => {
            switch (event.key) {
                case 'ArrowRight':
                case 'ArrowUp': {
                    event.preventDefault();
                    const nextIndex = Math.min(normalizedMax - 1, index + 1);
                    handleSelect(nextIndex + 1);
                    focusButton(nextIndex);
                    break;
                }
                case 'ArrowLeft':
                case 'ArrowDown': {
                    event.preventDefault();
                    const nextIndex = Math.max(0, index - 1);
                    handleSelect(nextIndex + 1);
                    focusButton(nextIndex);
                    break;
                }
                case 'Home': {
                    event.preventDefault();
                    handleSelect(1);
                    focusButton(0);
                    break;
                }
                case 'End': {
                    event.preventDefault();
                    handleSelect(normalizedMax);
                    focusButton(normalizedMax - 1);
                    break;
                }
                case ' ': // Space
                case 'Enter': {
                    event.preventDefault();
                    handleSelect(index + 1);
                    break;
                }
                default:
                    break;
            }
        },
        [focusButton, handleSelect, normalizedMax],
    );

    return (
        <div className={className}>
            <div
                role="radiogroup"
                aria-label={ariaLabel}
                aria-labelledby={ariaLabelledby}
                className="flex items-center gap-1"
            >
                {values.map((starValue, index) => {
                    const isFilled = starValue <= highlightValue;
                    const isSelected = starValue === normalizedValue;
                    const label = `Оцінка ${starValue} з ${normalizedMax}`;

                    return (
                        <button
                            key={starValue}
                            ref={(element) => {
                                buttonsRef.current[index] = element;
                            }}
                            type="button"
                            role="radio"
                            tabIndex={isSelected ? 0 : -1}
                            aria-checked={isSelected}
                            aria-label={label}
                            onMouseEnter={() => setHoveredValue(starValue)}
                            onMouseLeave={() => setHoveredValue(null)}
                            onFocus={() => setHoveredValue(starValue)}
                            onBlur={() => setHoveredValue(null)}
                            onKeyDown={(event) => handleKeyDown(event, index)}
                            onClick={() => handleSelect(starValue)}
                            className={cn(
                                'inline-flex h-9 w-9 items-center justify-center rounded-md text-2xl transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-black/70',
                                isFilled ? 'text-yellow-500' : 'text-gray-300',
                            )}
                        >
                            <span aria-hidden="true">{isFilled ? '★' : '☆'}</span>
                        </button>
                    );
                })}
            </div>
            <p className="mt-1 text-xs text-muted-foreground" aria-live="polite">
                {normalizedValue} з {normalizedMax}
            </p>
        </div>
    );
}
