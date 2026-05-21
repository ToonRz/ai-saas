# Paid-only launch (no Free Plan) due to zero capital

At launch there is no Free Subscription Plan. Subscribers must pay for a plan to use the AI Assistant because the operator cannot fund **Managed AI Access** for non-paying users. After **Cancellation** or failed **Grace Period**, Subscribers enter **Subscription Lapse** (login allowed, AI blocked) instead of a Free Plan.

This supersedes the Freemium assumption in ADR-0001 for the bootstrap phase. Freemium may return later once inference costs are sustainable. Rejected for launch: permanent Free Plan with real OpenAI (burns cash).

Prospects may try a **Landing Demo** on the marketing page only; logged-in Subscribers hit a **Paywall** until payment succeeds (no in-app free trial). **Managed AI Access** with live inference begins only on **Active Subscription** (billing webhook), not on registration or manual plan changes.
