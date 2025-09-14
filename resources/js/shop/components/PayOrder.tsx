import { useEffect, useMemo, useState } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, PaymentElement, useElements, useStripe } from '@stripe/react-stripe-js';
import { Button } from '@/components/ui/button';
import { useNotify } from '../ui/notify';

function Inner({ number, onPaid }: { number: string; onPaid?: () => void }) {
    const stripe = useStripe();
    const elements = useElements();
    const { error: notifyError, success } = useNotify();
    const [submitting, setSubmitting] = useState(false);

    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    const returnUrl = `${origin}/order/${encodeURIComponent(number)}`;

    async function handlePay() {
        if (!stripe || !elements) return;
        setSubmitting(true);
        try {
            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: { return_url: returnUrl },
            });
            if (error) {
                notifyError({ title: error.message || 'Оплата не пройшла' });
            } else {
                // якщо 3DS потрібен — Stripe зробить редірект
                success({ title: 'Оплата обробляється…' });
            }
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <div className="space-y-3">
            <PaymentElement />
            <Button onClick={handlePay} disabled={!stripe || submitting}>
                {submitting ? 'Оплата…' : 'Сплатити'}
            </Button>
        </div>
    );
}

export default function PayOrder({ number, onPaid }: { number: string; onPaid?: () => void }) {
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
