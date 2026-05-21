/**
 * AI SaaS — dashboard AI form handler
 */
(function () {
    const form = document.getElementById('ai-form');
    if (!form) return;

    const promptEl = document.getElementById('prompt');
    const submitBtn = document.getElementById('ai-submit');
    const errorEl = document.getElementById('ai-error');
    const responseWrap = document.getElementById('ai-response-wrap');
    const responseEl = document.getElementById('ai-response');
    const spinner = submitBtn.querySelector('.spinner-border');
    const submitText = submitBtn.querySelector('.submit-text');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const prompt = promptEl.value.trim();
        if (!prompt) return;

        errorEl.classList.add('d-none');
        responseWrap.classList.add('d-none');
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        submitText.textContent = 'Thinking...';

        try {
            const res = await fetch('api/ai_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: prompt }),
                credentials: 'same-origin',
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.error || 'Request failed');
            }

            responseEl.textContent = data.response;
            responseWrap.classList.remove('d-none');
            promptEl.value = '';

            if (data.usage && data.usage.remaining <= 0) {
                promptEl.disabled = true;
                submitBtn.disabled = true;
            }
        } catch (err) {
            errorEl.textContent = err.message;
            errorEl.classList.remove('d-none');
        } finally {
            spinner.classList.add('d-none');
            submitText.textContent = 'Send to AI';
            if (!promptEl.disabled) {
                submitBtn.disabled = false;
            }
        }
    });
})();
