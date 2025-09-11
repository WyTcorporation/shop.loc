import React, { useEffect } from 'react';

export type LightboxImage = { url: string; alt?: string };

export default function ImageLightbox({
                                          images,
                                          index,
                                          onClose,
                                          onIndexChange,
                                      }: {
    images: LightboxImage[];
    index: number;
    onClose: () => void;
    onIndexChange: (i: number) => void;
}) {
    const total = images.length;
    const safeIndex = Math.max(0, Math.min(index, total - 1));
    const img = images[safeIndex];

    const next = () => onIndexChange((safeIndex + 1) % total);
    const prev = () => onIndexChange((safeIndex - 1 + total) % total);

    useEffect(() => {
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
            if (e.key === 'ArrowRight') next();
            if (e.key === 'ArrowLeft') prev();
        };
        document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, [safeIndex, total]);

    if (!img) return null;

    return (
        <div
            className="fixed inset-0 z-50 bg-black/80 text-white"
            role="dialog"
            aria-modal="true"
            onClick={onClose}
        >
            <button
                onClick={onClose}
                className="absolute right-4 top-4 rounded-full bg-white/10 px-3 py-1 text-sm hover:bg-white/20"
                aria-label="Close"
            >
                ✕
            </button>

            {total > 1 && (
                <>
                    <button
                        onClick={(e) => { e.stopPropagation(); prev(); }}
                        className="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/10 px-3 py-2 text-2xl leading-none hover:bg-white/20"
                        aria-label="Prev"
                    >
                        ‹
                    </button>
                    <button
                        onClick={(e) => { e.stopPropagation(); next(); }}
                        className="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/10 px-3 py-2 text-2xl leading-none hover:bg-white/20"
                        aria-label="Next"
                    >
                        ›
                    </button>
                </>
            )}

            <div className="flex h-full items-center justify-center p-4" onClick={(e) => e.stopPropagation()}>
                <img
                    src={img.url}
                    alt={img.alt ?? ''}
                    className="max-h-[90vh] max-w-[90vw] object-contain"
                />
            </div>

            {total > 1 && (
                <div className="absolute bottom-4 left-0 right-0 mx-auto flex max-w-5xl justify-center gap-2 px-4">
                    {images.map((im, i) => (
                        <button
                            key={i}
                            onClick={() => onIndexChange(i)}
                            className={`h-14 w-14 overflow-hidden rounded-md border ${i === safeIndex ? 'ring-2 ring-white' : 'opacity-70 hover:opacity-100'}`}
                            aria-label={`Open image ${i + 1}`}
                        >
                            <img src={im.url} alt="" className="h-full w-full object-cover" />
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
