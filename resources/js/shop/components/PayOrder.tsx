import { useEffect, useMemo, useState } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, PaymentElement, useElements, useStripe } from '@stripe/react-stripe-js';
import { Button } from '@/components/ui/button';
import { useNotify } from '../ui/notify';
import { useLocale } from '../i18n/LocaleProvider';

function Inner({
    number,
    onPaid,
}: {
    number: string;
    onPaid?: (status?: string, paymentIntentId?: string) => void;
}) {
    const stripe = useStripe();
    const elements = useElements();
    const { error: notifyError, success: notifySuccess } = useNotify();
    const { t } = useLocale();
    const [submitting, setSubmitting] = useState(false);

    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    const returnUrl = `${origin}/order/${encodeURIComponent(number)}`;

    async function handlePay() {
        if (!stripe || !elements) return;
        setSubmitting(true);
        try {
            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: { return_url: returnUrl },
                redirect: 'if_required',
            });
            if (error) {
                notifyError({ title: error.message || t('checkout.payOrder.error') });
            } else {
                const status = paymentIntent?.status;
                const paymentIntentId = paymentIntent?.id;
                if (status === 'succeeded') {
                    notifySuccess({ title: t('checkout.payOrder.success') });
                    onPaid?.(status, paymentIntentId);
                } else if (status === 'processing') {
                    notifySuccess({ title: t('checkout.payOrder.processing') });
                    onPaid?.(status, paymentIntentId);
                } else {
                    // якщо 3DS або редірект — Stripe сам обробить сценарій
                    notifySuccess({ title: t('checkout.payOrder.processing') });
                }
            }
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <div className="space-y-3" data-testid="payment-form">
            <PaymentElement data-testid="payment-element" />
            <Button onClick={handlePay} disabled={!stripe || submitting}>
                {submitting ? t('checkout.payOrder.submitting') : t('checkout.payOrder.submit')}
            </Button>
        </div>
    );
}

export default function PayOrder({
    number,
    onPaid,
}: {
    number: string;
    onPaid?: (status?: string, paymentIntentId?: string) => void;
}) {
    const [clientSecret, setClientSecret] = useState<string | null>(null);
    const [publishableKey, setPublishableKey] = useState<string | null>(null);

    useEffect(() => {
        let on = true;
        (async () => {
            const res = await fetch('/api/payments/intent', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ number }),
            }).then(r => r.json());
            if (!on) return;
            setClientSecret(res.clientSecret);
            setPublishableKey(res.publishableKey);
        })();
        return () => { on = false; };
    }, [number]);

    const stripePromise = useMemo(() => publishableKey ? loadStripe(publishableKey) : null, [publishableKey]);

    if (!clientSecret || !stripePromise) return null;

    return (
        <Elements
            stripe={stripePromise}
            options={{
                clientSecret,
                appearance: { labels: 'floating' },
            }}
        >
            <Inner number={number} onPaid={onPaid} />
        </Elements>
    );
}
