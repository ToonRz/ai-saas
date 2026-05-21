<?php
require_once __DIR__ . '/includes/auth.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row align-items-center min-vh-75 py-5 g-5">
    <div class="col-lg-6">
        <span class="hero-badge">🚀 Paid-only Launch Plan</span>
        <h1 class="display-4 fw-bold mb-3">Your Premium AI Assistant</h1>
        <p class="lead text-muted mb-4">
            Get instant, high-quality text generation on demand. Draft blog posts, summarize complex documents, and brainstorm ideas with our shared infrastructure.
        </p>
        
        <div class="card card-premium p-4 mb-4">
            <h5 class="fw-bold mb-2">Launch Plan Pricing</h5>
            <div class="d-flex align-items-baseline mb-3">
                <span class="display-5 fw-bold text-primary">฿249</span>
                <span class="text-muted ms-2">/ billing period (monthly)</span>
            </div>
            <ul class="list-unstyled mb-4">
                <li class="mb-2">✨ <strong>Daily Allowance:</strong> 30 AI Requests per calendar day</li>
                <li class="mb-2">🛡️ <strong>Managed AI Access:</strong> No API keys or credit cards required for AI providers</li>
                <li class="mb-2">💾 Save history and view past requests anytime</li>
                <li class="mb-0">⚡ High-priority, high-speed text assistant response times</li>
            </ul>
            <div class="d-flex gap-2">
                <a href="register.php" class="btn btn-primary-custom px-4 py-2">Subscribe Now</a>
                <a href="login.php" class="btn btn-outline-secondary px-4 py-2">Log In</a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card card-premium shadow-sm border-0">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h4 class="fw-bold mb-1">Interactive Landing Demo</h4>
                <p class="text-muted small mb-0">Try the AI Assistant prompt simulator below (no registration needed)</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Select a preset prompt suggestion:</label>
                    <div>
                        <button type="button" class="suggestion-chip" onclick="setPreset('Agile Summary')">Summarize agile development</button>
                        <button type="button" class="suggestion-chip" onclick="setPreset('Welcome Email')">Draft a welcome email</button>
                        <button type="button" class="suggestion-chip" onclick="setPreset('Product Names')">Brainstorm SaaS names</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="demo-prompt" class="form-label fw-bold">Your Prompt</label>
                    <textarea class="form-control" id="demo-prompt" rows="4" placeholder="Type a prompt here or click a suggestion chip above..."></textarea>
                </div>
                <button type="button" class="btn btn-primary-custom w-100" id="btn-demo-submit" onclick="runDemo()">
                    Generate AI Response
                </button>
                
                <div id="demo-response-wrap" class="mt-4 d-none">
                    <h6 class="text-muted fw-bold small">AI Response</h6>
                    <div class="ai-response p-3 bg-light rounded border border-light-subtle">
                        <span id="demo-response" class="typewriter-text"></span>
                    </div>
                    <div class="alert alert-info mt-3 py-2 small mb-0">
                        ℹ️ This is a mock response from the interactive landing demo. Real subscribers enjoy live inference via <strong>Managed AI Access</strong>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const presets = {
    'Agile Summary': `Agile software development is a collaborative, iterative approach to building software.

Key principles include:
1. Deliver working software frequently in short iterations (sprints).
2. Maintain daily collaboration between business stakeholders and developers.
3. Continuously adapt plans based on user feedback and changing requirements.`,

    'Welcome Email': `Subject: Welcome to the future of writing! 🚀

Hi there,

Thank you for choosing our AI Assistant. We are excited to help you scale your copywriting workflow.

Here is how to get started:
1. Access your subscriber Dashboard.
2. Enter your Prompt in the text editor.
3. Review your generated AI Response instantly.

If you need any help, our support team is always available.

Best regards,
The AI Assistant Team`,

    'Product Names': `Here are 5 premium brand names for an AI-powered SaaS text utility:

1. MindSpark AI — Sparking creative text generation.
2. PromptStream — Smooth, continuous assistant writing.
3. WritePulse — Bringing rhythm and life to drafts.
4. Intellecta — High-grade cognitive text assistance.
5. Antigravity Text — Lifting copy editing barriers.`
};

function setPreset(key) {
    document.getElementById('demo-prompt').value = presets[key];
    document.getElementById('demo-response-wrap').classList.add('d-none');
}

let typingInterval = null;

function runDemo() {
    const promptInput = document.getElementById('demo-prompt').value.trim();
    if (!promptInput) {
        alert('Please enter a Prompt or select a suggestion chip.');
        return;
    }

    const submitBtn = document.getElementById('btn-demo-submit');
    const responseWrap = document.getElementById('demo-response-wrap');
    const responseEl = document.getElementById('demo-response');

    // Reset typewriter
    if (typingInterval) clearInterval(typingInterval);
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Thinking...';
    responseWrap.classList.add('d-none');
    responseEl.textContent = '';

    // Mock network latency (800ms)
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Generate AI Response';
        responseWrap.classList.remove('d-none');

        // Determine output
        let rawResponse = '';
        let foundPreset = false;
        for (const key in presets) {
            if (presets[key] === promptInput) {
                rawResponse = presets[key];
                foundPreset = true;
                break;
            }
        }

        if (!foundPreset) {
            rawResponse = `[Interactive Landing Demo] This is a mock AI Response.

Your prompt was:
"${promptInput}"

To get live OpenAI-powered replies and save your request history, register an account and start recurring billing for the Launch Plan (daily allowance: 30 AI Requests)!`;
        }

        // Typewriter animation effect
        let index = 0;
        responseEl.classList.add('typewriter-text');
        typingInterval = setInterval(() => {
            if (index < rawResponse.length) {
                responseEl.textContent += rawResponse.charAt(index);
                index++;
                // Scroll down
                const container = responseEl.parentElement;
                container.scrollTop = container.scrollHeight;
            } else {
                clearInterval(typingInterval);
                responseEl.classList.remove('typewriter-text');
            }
        }, 15);

    }, 800);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
