import { Link } from 'react-router-dom';
import SeoHead from '../components/SeoHead';

export default function NotFoundPage() {
    return (
        <div className="mx-auto max-w-3xl p-6 text-center">
            <SeoHead title="Сторінку не знайдено — 404 — Shop" description="Сторінку не знайдено" canonical />
            <h1 className="text-3xl font-semibold mb-3">404 — Сторінку не знайдено</h1>
            <p className="text-gray-600 mb-6">Можливо, посилання застаріло або було видалено.</p>
            <Link to="/" className="underline">Повернутися до каталогу</Link>
        </div>
    );
}
