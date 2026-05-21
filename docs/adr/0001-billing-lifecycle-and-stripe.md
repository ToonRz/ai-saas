# Billing lifecycle and Stripe for recurring plans

We sell paid Subscription Plans via Recurring Billing (Stripe). Subscribers who cancel keep their paid plan until the current Billing Period ends, then move to the Free Plan. When Billing Failure occurs, we allow a Grace Period on the paid plan before the same downgrade to Free — never a full account ban for non-payment. Allowance Exhaustion is a separate daily cap: hard block until calendar reset or Plan Change, with no pay-per-day top-ups in v1.

This matches common SaaS expectations, keeps churned users on Freemium, and aligns with `cancel_at_period_end` and dunning patterns in Stripe. Alternatives considered: immediate downgrade on cancel or failed payment (harsher, more support load), and account suspension until paid (breaks Freemium funnel).
