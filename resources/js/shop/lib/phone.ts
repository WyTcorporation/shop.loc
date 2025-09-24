const NON_DIGIT_PLUS = /[^\d+]/g;
const NON_DIGIT = /[^\d]/g;
const MAX_PHONE_DIGITS = 15;

const splitIntoGroups = (digits: string): string[] => {
    const groups: string[] = [];
    for (let index = 0; index < digits.length; index += 3) {
        groups.push(digits.slice(index, index + 3));
    }
    return groups;
};

export const formatInternationalPhoneInput = (value: string): string => {
    const trimmed = value.trim();
    if (!trimmed) {
        return '';
    }

    const sanitized = trimmed.replace(NON_DIGIT_PLUS, '');
    if (!sanitized) {
        return '';
    }

    if (sanitized === '+') {
        return '+';
    }

    const digits = sanitized.replace(NON_DIGIT, '').slice(0, MAX_PHONE_DIGITS);
    if (!digits) {
        return '+';
    }

    const groups = splitIntoGroups(digits);
    return `+${groups.join(' ')}`;
};

export const normalizeInternationalPhone = (value: string): string | null => {
    const formatted = formatInternationalPhoneInput(value);
    const digits = formatted.replace(NON_DIGIT, '');

    if (!digits) {
        return null;
    }

    return `+${digits}`;
};

export const formatPhoneForDisplay = (value?: string | null): string => {
    if (typeof value !== 'string' || value.trim() === '') {
        return '';
    }

    return formatInternationalPhoneInput(value);
};
