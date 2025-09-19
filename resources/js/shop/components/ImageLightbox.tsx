import React, { useEffect } from 'react';
import { useLocale } from '../i18n/LocaleProvider';

type Img = { url: string; alt?: string };
type Props = {
    images: Img[];
    openIndex: number | null;                 // null → закрито
    onClose: () => void;
    onPrev: () => void;
    onNext: () => void;
};

export default function ImageLightbox({ images, openIndex, onClose, onPrev, onNext }: Props) {
    const isOpen = openIndex != null && images.length > 0;
    const img = isOpen ? images[openIndex as number] : null;
    const { t } = useLocale();

    useEffect(() => {
        if (!isOpen) return;
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
            if (e.key === 'ArrowLeft') onPrev();
            if (e.key === 'ArrowRight') onNext();
        };
        document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, [isOpen, onClose, onPrev, onNext]);

    if (!isOpen || !img) return null;

    return (
        <div
            data-testid="image-lightbox"
            className="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4"
            onClick={onClose}
            role="dialog"
            aria-modal="true"
        >
            <button
                aria-label={t('common.lightbox.close')}
                onClick={onClose}
                className="absolute right-4 top-4 rounded bg-white/10 px-3 py-1 text-white hover:bg-white/20"
            >
                ✕
            </button>

            <button
                aria-label={t('common.lightbox.prev')}
                onClick={(e) => { e.stopPropagation(); onPrev(); }}
                className="absolute left-4 top-1/2 -translate-y-1/2 rounded bg-white/10 px-3 py-2 text-white hover:bg-white/20"
            >
                ‹
            </button>

            <img
                src={img.url}
                alt={img.alt ?? ''}
                className="max-h-[85vh] max-w-[90vw] object-contain select-none"
                onClick={(e) => e.stopPropagation()}
            />

            <button
                aria-label={t('common.lightbox.next')}
                onClick={(e) => { e.stopPropagation(); onNext(); }}
                className="absolute right-4 top-1/2 -translate-y-1/2 rounded bg-white/10 px-3 py-2 text-white hover:bg-white/20"
            >
                ›
            </button>
        </div>
    );
}
