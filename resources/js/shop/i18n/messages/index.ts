import type { Lang } from '../config';

import en from './en';
import pt from './pt';
import ru from './ru';
import uk from './uk';

export type Messages = typeof uk;

type MessageFunction = (params: any) => string;
type MessageValue = string | MessageFunction;

type DotPaths<T, Prefix extends string = ''> = {
    [K in keyof T & string]: T[K] extends MessageValue
        ? `${Prefix}${K}`
        : T[K] extends Record<string, any>
            ? `${Prefix}${K}` | DotPaths<T[K], `${Prefix}${K}.`>
            : `${Prefix}${K}`;
}[keyof T & string];

type PathValue<T, Key extends string> = Key extends `${infer Head}.${infer Tail}`
    ? Head extends keyof T
        ? PathValue<T[Head], Tail>
        : never
    : Key extends keyof T
        ? T[Key]
        : never;

type ExtractParams<V> = V extends (params: infer P) => string ? P : undefined;

export type TranslationKey = DotPaths<Messages>;

export type TranslationParams<K extends TranslationKey> = ExtractParams<PathValue<Messages, K>>;

export type Translator = <K extends TranslationKey>(
    key: K,
    ...args: TranslationParams<K> extends undefined ? [] : [TranslationParams<K>]
) => string;

function resolvePath(obj: Record<string, any>, key: string): MessageValue | Record<string, any> {
    return key.split('.').reduce((acc: any, part) => {
        if (acc && typeof acc === 'object' && part in acc) {
            return acc[part];
        }
        throw new Error(`Missing translation for key "${key}"`);
    }, obj);
}

export function createTranslator(messages: Messages): Translator {
    return ((key: TranslationKey, ...args: any[]) => {
        const value = resolvePath(messages as Record<string, any>, key);
        if (typeof value === 'function') {
            return (value as MessageFunction)(args[0]);
        }
        if (typeof value === 'string') {
            return value;
        }
        throw new Error(`Invalid translation value for key "${key}"`);
    }) as Translator;
}

export const localeMessages = {
    uk,
    en,
    ru,
    pt,
} satisfies Record<Lang, Messages>;

export function getMessages(lang: Lang): Messages {
    return localeMessages[lang];
}
