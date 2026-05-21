# AI SaaS

A subscription web product where people pay for tiered access to an in-browser AI text assistant.

## Language

### Product

**AI Assistant**:
The in-browser product where a Subscriber writes a prompt and receives AI-generated text.
_Avoid_: chatbot, API, copilot (unless we add distinct products later)

**Prompt**:
The text a Subscriber submits to the AI Assistant.
_Avoid_: message, input (in customer-facing copy)

**AI Response**:
The text the AI Assistant returns for a Prompt.
_Avoid_: output, completion (in customer-facing copy)

**Managed AI Access**:
The platform runs AI Requests on behalf of Subscribers using shared infrastructure. Subscribers do not supply their own AI provider credentials; inference cost is borne by the platform and reflected in Subscription Plan pricing.
_Avoid_: BYOK, bring your own key, API key (in Subscriber-facing language)

**Active Subscription**:
A Subscriber’s **Recurring Billing** is in good standing (payment confirmed by the billing provider). Only then is the **Paywall** cleared and **Managed AI Access** uses live inference—not mock.
_Avoid_: mock upgrade, plan row changed without payment

**Revenue-funded Inference**:
The operator adds paid AI provider credit only after the first successful subscriber payment—not before launch. Cash flow: **Recurring Billing** in → inference spend out.
_Avoid_: pre-funding API balance from personal savings (at bootstrap)

### People

**Subscriber**:
A registered person who holds a Subscription Plan and uses the AI Assistant.
Today each Subscriber has their own account (B2C). Later, multiple Subscribers may belong to one Organization under a shared plan.
_Avoid_: user, account, customer (in domain discussion — code table may still say `users`)

**Organization** *(planned)*:
A company or team that buys one Subscription Plan shared by multiple Subscribers (B2B). Not in scope for the first release.
_Avoid_: workspace, tenant, company account (until defined)

**Administrator**:
A Subscriber with elevated access to manage other Subscribers and view platform usage.
_Avoid_: admin user, superuser

### Commercial

**Subscription Plan**:
A named tier that sets price and how many AI Requests a Subscriber may make per day.
_Avoid_: package, package plan, SKU

**Launch Plan** *(bootstrap)*:
The single paid **Subscription Plan** offered at **Paid-only Launch**. Other tiers in the catalog are not sold until the business expands pricing.

**Cost-aligned Pricing**:
The Launch Plan price is set from estimated inference cost per **AI Request**, times **Daily Allowance**, times a safety margin—so a maxed-out Subscriber still leaves margin after **Revenue-funded Inference**.
_Avoid_: cheap unlimited plans, pricing copied from US SaaS without recalculating API cost

**Launch Allowance**:
The **Daily Allowance** on the **Launch Plan** at bootstrap: **30 AI Requests** per calendar day.
_Avoid_: 100 requests/day (until revenue supports it)

**Launch Price**:
The **Launch Plan** recurring price at bootstrap: **฿249 per Billing Period** (monthly). Set with **Cost-aligned Pricing**; margin is intentionally tight to reduce barrier to first payment.
_Avoid_: $9.99, ฿99 (unless recalculated)

**Baht Billing**:
**Recurring Billing** charges in Thai Baht (THB) only at launch—matching **Launch Price** on invoices and checkout.
_Avoid_: USD checkout for Thai subscribers (at launch)

**Recurring Billing**:
Automatic monthly charging that keeps a Subscriber on a paid Subscription Plan until they cancel. The first release targets a card-based billing provider; paid plans are not active until billing is confirmed.
_Avoid_: subscription (alone — clashes with Subscription Plan), payment plan

**Plan Change**:
A Subscriber moving from one Subscription Plan to another (upgrade or downgrade). With Recurring Billing, the new plan takes effect according to billing rules (e.g. immediate upgrade, end-of-period downgrade).
_Avoid_: switch, migrate

**Billing Failure**:
When a scheduled charge for Recurring Billing does not succeed (e.g. expired card).

**Grace Period**:
A short window after Billing Failure during which the Subscriber keeps their paid Subscription Plan and Daily Allowance while we retry payment. If payment is still not resolved, they enter **Subscription Lapse** — not a full account ban.
_Avoid_: suspension (unless we explicitly mean login blocked)

**Cancellation**:
A Subscriber’s decision to end Recurring Billing. They keep their paid Subscription Plan and Daily Allowance until the current **Billing Period** ends, then enter **Subscription Lapse** (no Free Plan at launch).
_Avoid_: unsubscribe (use only in UI copy if needed), delete account

**Billing Period**:
One month of Recurring Billing for a paid Subscription Plan. Upgrades may start immediately; **Cancellation** takes effect at the end of the current Billing Period.
_Avoid_: cycle, term

**AI Request**:
One counted use of the AI Assistant: a Subscriber sends one Prompt and receives one AI Response. Resets against the plan limit each calendar day (not a rolling 24-hour window).
_Avoid_: credit, token, API call (in customer-facing copy)

**Daily Allowance**:
The maximum number of AI Requests a Subscriber may make on the current calendar day under their Subscription Plan.
_Avoid_: quota, rate limit, cap (in customer-facing copy)

**Paid-only Launch** *(bootstrap)*:
No Free Subscription Plan at launch. Every Subscriber must be on a paid plan to use the AI Assistant. Chosen because the operator has no capital to subsidize **Managed AI Access** for non-paying users.
_Avoid_: freemium, free tier (for the launch phase)

**Landing Demo**:
An interactive mock on the marketing page: visitors can type a Prompt and see a canned **AI Response** without calling the real AI provider. It does not count toward **Daily Allowance** and must not incur inference cost.
_Avoid_: free plan, in-app trial, live OpenAI on the landing page

**Paywall**:
After login, the Subscriber must complete payment for a paid **Subscription Plan** before any in-app **AI Request**. Registration alone does not grant **Managed AI Access**.
_Avoid_: free trial, freemium inside the product

**Signup-then-Pay**:
The launch onboarding order: create an account (name, email, password), log in, hit the **Paywall**, complete **Recurring Billing** for the **Launch Plan**, then receive **Managed AI Access**.
_Avoid_: checkout-before-account, paywall on the marketing page only

**Tunnel Launch** *(bootstrap)*:
The app runs on the operator’s machine for $0 hosting; a free tunnel (e.g. ngrok, Cloudflare Tunnel) exposes HTTPS so Stripe webhooks and early Subscribers can reach the site. Replaced by paid hosting once revenue covers it.
_Avoid_: production hosting (until funded)

**Subscription Lapse**:
The state after **Cancellation** or unresolved **Billing Failure** when the Subscriber has no active paid Subscription Plan. They may still log in, but cannot make AI Requests until they start a new paid subscription.
_Avoid_: free plan, downgrade to free (not used at launch)

**Allowance Exhaustion**:
The state when a Subscriber has used all AI Requests allowed for the current calendar day. The AI Assistant refuses new Prompts until the day resets or the Subscriber upgrades to a higher Subscription Plan.
_Avoid_: throttling, rate limiting (those sound like partial slowdown, not a full stop)

## Flagged ambiguities

- Earlier sessions assumed a permanent **Free Plan** and **Freemium**. **Paid-only Launch** replaces that until the business can fund inference for non-payers.
- `schema.sql` still seeds Free / Pro / Premium — **Paid-only Launch** sells only the **Launch Plan**; other rows may stay for later.
- **Launch Price** at ฿249 with **Launch Allowance** 30/day is aggressive vs a ฿299 safety margin—monitor inference cost per **AI Request** after go-live.

## Example dialogue

**Dev:** Which plan do we sell at launch?  
**Expert:** Just the **Launch Plan** — one paid tier with **Cost-aligned Pricing**, not a race to the bottom.

**Dev:** Can we copy $9.99 with 100 requests/day?  
**Expert:** Not at bootstrap — we use **Launch Allowance** of 30/day and **Cost-aligned Pricing** instead.

**Dev:** What if they use all 30 every day?  
**Expert:** **Launch Price** (฿249) and **Launch Allowance** (30) must still cover that case—we accept a thinner margin to win the first paying Subscribers.

**Dev:** Why not ฿299?  
**Expert:** We chose a lower **Launch Price** for faster first conversion; if costs run hot, raise price or lower **Launch Allowance** later.

**Dev:** Will subscribers see dollars on checkout?  
**Expert:** No — **Baht Billing** only at launch.

**Dev:** When someone upgrades later, what changes?  
**Expert:** After bootstrap we may add more **Subscription Plan** tiers; their **Daily Allowance** changes on **Plan Change**.

**Dev:** Is that one request per Prompt?  
**Expert:** Yes — that’s one **AI Request** toward their **Daily Allowance** on their **Subscription Plan**. At midnight (calendar day) the count starts fresh; we’re not doing rolling 24-hour windows.

**Dev:** Can a company buy one plan for ten people?  
**Expert:** Not yet — that’s an **Organization** later. For now every **Subscriber** is on their own account, even if we’re already calling the product “SaaS.”

**Dev:** How does Pro get paid for?  
**Expert:** They start **Recurring Billing** for the Pro **Subscription Plan**. Each month they’re charged until they cancel; we don’t rely on them remembering to pay again manually.

**Dev:** What about the mock upgrade button today?  
**Expert:** That’s a stand-in until **Recurring Billing** is wired up — it changes the plan in our database without a real charge.

**Dev:** Can someone use the product without paying?  
**Expert:** Not inside the app. They see a **Landing Demo** on the marketing site; after login, the **Paywall** blocks **AI Requests** until **Recurring Billing** is active. Otherwise they’re in **Subscription Lapse**.

**Dev:** I registered but didn’t pay yet — can I open the dashboard AI?  
**Expert:** That’s **Signup-then-Pay**: account exists, but the **Paywall** blocks you until **Recurring Billing** for the **Launch Plan** succeeds.

**Dev:** Can I pay before creating a password?  
**Expert:** Not at launch — we register first, then pay inside the app.

**Dev:** Does the homepage burn OpenAI credits?  
**Expert:** No — **Landing Demo** is mock-only. Live inference starts only with an **Active Subscription** after the billing provider confirms payment—not on register alone.

**Dev:** Someone toggled the plan in the database without paying — do they get real AI?  
**Expert:** No. **Active Subscription** requires confirmed **Recurring Billing**; otherwise **Paywall** / mock only.

**Dev:** Should I buy OpenAI credit before launch?  
**Expert:** Not at bootstrap — **Revenue-funded Inference**. First **Active Subscription** payment, then fund the provider.

**Dev:** Can they keep chatting after hitting the daily cap on Pro?  
**Expert:** That’s **Allowance Exhaustion** — hard stop until tomorrow or a **Plan Change** to a higher paid plan. No pay-per-day top-ups in v1.

**Dev:** Card declined on renewal — do we lock them out?  
**Expert:** **Billing Failure** → **Grace Period** on the paid plan. If it still fails → **Subscription Lapse**: login OK, **AI Assistant** blocked until they pay again. Not a ban.

**Dev:** They cancel Pro mid-month — instant cut-off?  
**Expert:** No — **Cancellation** runs through the **Billing Period**, then **Subscription Lapse**. No free tier to fall back to.

**Dev:** Do users paste their OpenAI API key?  
**Expert:** No — **Managed AI Access**. One platform stack; **Daily Allowance** on each **Subscription Plan** is how we control spend.

**Dev:** Are we rebuilding from scratch?  
**Expert:** v1 evolves the existing web app: same **AI Assistant** and plans, add real **Recurring Billing** and replace mock upgrades. **Organization** and BYOK stay later.

**Dev:** Do we pay for hosting on day one?  
**Expert:** No — **Tunnel Launch** on a local stack until subscriber revenue covers real hosting.
