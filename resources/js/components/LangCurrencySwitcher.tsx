import { cn } from '@/lib/utils';
import { useApp } from '@/providers/app-provider';
import { Languages, Coins } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from './ui/select';

type Orientation = 'horizontal' | 'vertical';

interface LangCurrencySwitcherProps {
    orientation?: Orientation;
    className?: string;
    disableLocaleWhileLoading?: boolean;
}

export function LangCurrencySwitcher({
    orientation = 'horizontal',
    className,
    disableLocaleWhileLoading = true,
}: LangCurrencySwitcherProps) {
    const {
        locale,
        currency,
        locales,
        currencies,
        setLocale,
        setCurrency,
        isDictionaryLoading,
    } = useApp();

    const isVertical = orientation === 'vertical';

    return (
        <div
            className={cn(
                'flex items-center gap-2',
                isVertical && 'flex-col items-stretch',
                className,
            )}
        >
            <Select
                value={locale}
                onValueChange={setLocale}
                disabled={disableLocaleWhileLoading && isDictionaryLoading}
            >
                <SelectTrigger
                    aria-label="Select language"
                    className={cn(
                        'h-9 min-w-[140px] justify-between text-sm',
                        isVertical && 'w-full',
                    )}
                >
                    <span className="flex items-center gap-2">
                        <Languages className="size-4 opacity-60" />
                        <SelectValue placeholder="Language" />
                    </span>
                </SelectTrigger>
                <SelectContent>
                    {locales.map((item) => (
                        <SelectItem key={item.value} value={item.value}>
                            {item.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>

            <Select value={currency} onValueChange={setCurrency}>
                <SelectTrigger
                    aria-label="Select currency"
                    className={cn(
                        'h-9 min-w-[130px] justify-between text-sm',
                        isVertical && 'w-full',
                    )}
                >
                    <span className="flex items-center gap-2">
                        <Coins className="size-4 opacity-60" />
                        <SelectValue placeholder="Currency" />
                    </span>
                </SelectTrigger>
                <SelectContent>
                    {currencies.map((item) => (
                        <SelectItem key={item.value} value={item.value}>
                            {item.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}

