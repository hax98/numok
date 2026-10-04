document.addEventListener('click', async (event) => {
    const placementButton = event.target.closest('[data-placement]');
    if (placementButton) {
        const presets = {
            'instagram-reel': { platform: 'instagram', kind: 'video', label: 'Instagram Reel' },
            'instagram-story': { platform: 'instagram', kind: 'story', label: 'Instagram Story' },
            'tiktok-bio': { platform: 'tiktok', kind: 'post', label: 'TikTok bio placement' },
        };
        const preset = presets[placementButton.dataset.placement];
        const form = document.getElementById('content-link-form');
        if (placementButton.disabled || !preset || !form) return;
        const title = document.getElementById('content-title');
        if (title.value.trim() && !window.confirm('Switch placement? Your content name stays, but the platform and format will change.')) return;
        document.getElementById('content-platform').value = preset.platform;
        document.getElementById('content-kind').value = preset.kind;
        document.getElementById('content-link-details').open = true;
        document.getElementById('placement-status').textContent = preset.label + ' selected. Name this placement, then create your link.';
        form.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'center' });
        title.focus({ preventScroll: true });
        return;
    }
    const button = event.target.closest('[data-copy]');
    if (!button) return;
    const original = button.textContent;
    try { await navigator.clipboard.writeText(button.dataset.copy); button.textContent = 'Copied'; }
    catch { button.textContent = 'Copy unavailable'; }
    setTimeout(() => { button.textContent = original; }, 1800);
});
