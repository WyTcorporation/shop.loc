import React from 'react';
import { Link, useParams } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import SeoHead from '../components/SeoHead';
import WishlistButton from '../components/WishlistButton';
import { fetchSellerProducts, type Product, type SellerProductsResponse, type Vendor } from '../api';
import { resolveErrorMessage } from '../lib/errors';
import { formatPhoneForDisplay, normalizeInternationalPhone } from '../lib/phone';
import { formatPrice } from '../ui/format';
import { GA } from '../ui/ga';
import { useDocumentTitle } from '../hooks/useDocumentTitle';
import { useLocale } from '../i18n/LocaleProvider';

type SellerPageErrorKey = 'sellerPage.notFound' | 'sellerPage.errors.loadProducts';

type SellerPageError =
    | { type: 'translation'; key: SellerPageErrorKey }
    | { type: 'custom'; message: string };

export default function SellerPage() {
    const { id } = useParams<{ id: string }>();
    const vendorKey = id ?? '';

    const { t } = useLocale();
    const brand = t('common.brand');

    const [vendor, setVendor] = React.useState<Vendor | null>(null);
    const [products, setProducts] = React.useState<Product[]>([]);
    const [page, setPage] = React.useState(1);
    const [lastPage, setLastPage] = React.useState(1);
    const [loading, setLoading] = React.useState(false);
    const [error, setError] = React.useState<SellerPageError | null>(null);

    React.useEffect(() => {
        setPage(1);
    }, [vendorKey]);

    React.useEffect(() => {
        if (!vendorKey) {
            setVendor(null);
            setProducts([]);
            setLastPage(1);
            setError({ type: 'translation', key: 'sellerPage.notFound' });
            setLoading(false);
            return;
        }

        let ignore = false;
        setLoading(true);
        setError(null);

        fetchSellerProducts(vendorKey, { page })
            .then((response: SellerProductsResponse) => {
                if (ignore) return;
                const items = response.data ?? [];
                setVendor(response.vendor);
                setProducts(items);
                setLastPage(response.last_page ?? 1);
                GA.view_item_list(items, t('sellerPage.ga.listName', { name: response.vendor.name }));
            })
            .catch((err) => {
                if (ignore) return;
                setVendor(null);
                setProducts([]);
                setLastPage(1);
                let usedFallback = false;
                const message = resolveErrorMessage(err, () => {
                    usedFallback = true;
                    return '';
                });

                if (usedFallback) {
                    setError({ type: 'translation', key: 'sellerPage.errors.loadProducts' });
                } else {
                    setError({ type: 'custom', message });
                }
            })
            .finally(() => {
                if (!ignore) {
                    setLoading(false);
                }
            });

        return () => {
            ignore = true;
        };
    }, [page, vendorKey, t]);

    const pageTitle = t('sellerPage.pageTitle', { name: vendor?.name });
    const documentTitle = t('sellerPage.documentTitle', { name: vendor?.name, brand });
    useDocumentTitle(documentTitle);

    const seoTitle = t('sellerPage.seo.title', { name: vendor?.name, brand });
    const formattedVendorPhone = formatPhoneForDisplay(vendor?.contact_phone);
    const seoDescription = t('sellerPage.seo.description', {
        description: vendor?.description ?? '',
        email: vendor?.contact_email ?? '',
        phone: formattedVendorPhone,
    });

    const canPrev = page > 1;
    const canNext = page < lastPage;

    const resolvedError = error
        ? error.type === 'translation'
            ? t(error.key)
            : error.message
        : null;

    const emailLabel = vendor?.contact_email
        ? t('sellerPage.contact.email', { email: vendor.contact_email })
        : null;
    const phoneHref = vendor?.contact_phone ? normalizeInternationalPhone(vendor.contact_phone) : null;
    const phoneLabel = vendor?.contact_phone && formattedVendorPhone
        ? t('sellerPage.contact.phone', { phone: formattedVendorPhone })
        : null;

    const paginationStatus = t('sellerPage.pagination.status', { page, lastPage });

    return (
        <div className="mx-auto max-w-6xl space-y-8 px-4 py-8">
            <SeoHead title={seoTitle} description={seoDescription} robots="index,follow" />

            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                {vendor ? (
                    <div className="space-y-3">
                        <h1 className="text-2xl font-semibold">{vendor.name}</h1>
                        <p className="text-sm text-gray-500">{pageTitle}</p>
                        {vendor.description && (
                            <p className="text-sm text-gray-600">{vendor.description}</p>
                        )}
                        <div className="flex flex-wrap gap-4 text-sm text-gray-600">
                            {vendor.contact_email && emailLabel && (
                                <a
                                    href={`mailto:${vendor.contact_email}`}
                                    className="text-blue-600 hover:text-blue-800 hover:underline"
                                >
                                    {emailLabel}
                                </a>
                            )}
                            {vendor.contact_phone && phoneLabel && phoneHref && (
                                <a
                                    href={`tel:${phoneHref}`}
                                    className="text-blue-600 hover:text-blue-800 hover:underline"
                                >
                                    {phoneLabel}
                                </a>
                            )}
                        </div>
                    </div>
                ) : loading ? (
                    <div className="text-sm text-gray-500">{t('sellerPage.loadingVendor')}</div>
                ) : (
                    <div className="text-sm text-gray-500">{t('sellerPage.notFound')}</div>
                )}
            </div>

            {resolvedError && (
                <div className="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{resolvedError}</div>
            )}

            <section className="space-y-4">
                <h2 className="text-xl font-semibold">{t('sellerPage.productsTitle')}</h2>
                {loading ? (
                    <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                        {Array.from({ length: 8 }).map((_, index) => (
                            <Card key={index} className="p-3">
                                <Skeleton className="mb-3 h-40 w-full" />
                                <Skeleton className="mb-2 h-4 w-3/4" />
                                <Skeleton className="h-4 w-1/2" />
                            </Card>
                        ))}
                    </div>
                ) : products.length === 0 ? (
                    <div className="text-sm text-gray-600">{t('sellerPage.noProducts')}</div>
                ) : (
                    <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                        {products.map((product) => {
                            const primaryImage =
                                product.images?.find((img) => img.is_primary) ??
                                (product.images && product.images.length > 0 ? product.images[0] : undefined);

                            return (
                                <Card key={product.id} className="overflow-hidden">
                                    <Link to={`/product/${product.slug ?? product.id}`} className="block">
                                        <div className="aspect-square bg-muted/40">
                                            {primaryImage ? (
                                                <img
                                                    src={primaryImage.url}
                                                    alt={primaryImage.alt ?? product.name}
                                                    className="h-full w-full object-cover"
                                                    loading="lazy"
                                                />
                                            ) : (
                                                <div className="flex h-full items-center justify-center text-sm text-gray-500">
                                                    {t('sellerPage.noImage')}
                                                </div>
                                            )}
                                        </div>
                                        <div className="p-3">
                                            <div className="line-clamp-2 text-sm font-medium">{product.name}</div>
                                            <div className="mt-1 text-sm text-gray-600">
                                                {formatPrice(product.price, product.currency ?? 'EUR')}
                                            </div>
                                        </div>
                                    </Link>
                                    <div className="flex items-center justify-end px-3 pb-3">
                                        <WishlistButton product={product} />
                                    </div>
                                </Card>
                            );
                        })}
                    </div>
                )}
            </section>

            {lastPage > 1 && (
                <div className="flex items-center justify-center gap-3">
                    <Button variant="outline" disabled={!canPrev} onClick={() => setPage((x) => Math.max(1, x - 1))}>
                        {t('sellerPage.pagination.prev')}
                    </Button>
                    <span className="text-sm text-gray-600">{paginationStatus}</span>
                    <Button variant="outline" disabled={!canNext} onClick={() => setPage((x) => x + 1)}>
                        {t('sellerPage.pagination.next')}
                    </Button>
                </div>
            )}
        </div>
    );
}
