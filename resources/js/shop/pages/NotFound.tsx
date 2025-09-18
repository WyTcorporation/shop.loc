import { Link } from 'react-router-dom';
import SeoHead from '../components/SeoHead';
import { useLocale } from '../i18n/LocaleProvider';

export default function NotFoundPage() {
    const { t } = useLocale();
    const brand = t('common.brand');

    return (
        <div className="mx-auto max-w-3xl p-6 text-center">
            <SeoHead
                title={t('common.notFound.seoTitle', { brand })}
                description={t('common.notFound.seoDescription')}
                canonical
            />
            <h1 className="text-3xl font-semibold mb-3">{t('common.notFound.title')}</h1>
            <p className="text-gray-600 mb-6">{t('common.notFound.description')}</p>
            <Link to="/" className="underline">
                {t('common.notFound.action')}
            </Link>
        </div>
    );
}
