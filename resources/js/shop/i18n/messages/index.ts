import type { Lang } from '../config';

import en from './en';
import pt from './pt';
import ru from './ru';
import uk from './uk';

export type Messages = typeof uk;

export const localeMessages = {
    uk,
    en,
    ru,
    pt,
} satisfies Record<Lang, Messages>;

export function getMessages(lang: Lang): Messages {
    return localeMessages[lang];
}

