<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap');

    .partner-auth-page {
        --auth-ink: #17141c;
        --auth-muted: #6b6573;
        --auth-line: #e1dde5;
        --auth-paper: #fbfaf8;
        --auth-purple: #5b3df5;
        --auth-purple-dark: #4224cb;
        --auth-lavender: #ebe7ff;
        min-height: 100vh;
        display: grid;
        grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
        background: var(--auth-paper);
        color: var(--auth-ink);
        font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
    }

    .partner-auth-page *, .partner-auth-page *::before, .partner-auth-page *::after { box-sizing: border-box; }
    .partner-auth-page a { color: inherit; text-decoration: none; }
    .partner-auth-form-side { display: flex; min-height: 100vh; flex-direction: column; padding: 28px clamp(25px, 6vw, 88px) 36px; }
    .partner-auth-nav { display: flex; align-items: center; justify-content: space-between; gap: 20px; }
    .partner-auth-brand { display: inline-flex; align-items: center; gap: 10px; font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; letter-spacing: -.04em; }
    .partner-auth-mark { display: inline-grid; width: 32px; height: 32px; place-items: center; border-radius: 9px; background: var(--auth-purple); overflow: hidden; }
    .partner-auth-mark img { width: 26px; height: 26px; object-fit: contain; filter: brightness(0) invert(1); }
    .partner-auth-back { color: var(--auth-muted); font-size: 12px; font-weight: 600; }
    .partner-auth-back:hover, .partner-auth-secondary a:hover { color: var(--auth-purple); }
    .partner-auth-content { width: min(100%, 456px); margin: auto; padding: 66px 0 48px; }
    .partner-auth-eyebrow { margin: 0; color: var(--auth-purple); font-size: 10px; font-weight: 700; letter-spacing: .17em; }
    .partner-auth-content h1 { margin: 15px 0 14px; font-family: 'Space Grotesk', sans-serif; font-size: clamp(34px, 4vw, 51px); font-weight: 600; letter-spacing: -.07em; line-height: 1.02; }
    .partner-auth-lede { max-width: 420px; margin: 0 0 28px; color: var(--auth-muted); font-size: 15px; line-height: 1.6; }
    .partner-auth-alert { margin: 0 0 20px; padding: 12px 14px; border: 1px solid #f0b8c2; border-radius: 12px; background: #fff3f4; color: #9c263d; font-size: 13px; line-height: 1.45; }
    .partner-auth-alert.success { border-color: #b8dbc4; background: #f1faf4; color: #21613a; }
    .partner-auth-form { display: grid; gap: 17px; }
    .partner-auth-field { display: grid; gap: 7px; }
    .partner-auth-field label { color: var(--auth-ink); font-size: 12px; font-weight: 700; }
    .partner-auth-field input { width: 100%; height: 48px; padding: 0 14px; border: 1px solid var(--auth-line); border-radius: 11px; outline: none; background: #fff; color: var(--auth-ink); font: inherit; font-size: 14px; transition: border-color .18s ease, box-shadow .18s ease; }
    .partner-auth-field input::placeholder { color: #aaa4b0; }
    .partner-auth-field input:focus { border-color: var(--auth-purple); box-shadow: 0 0 0 4px rgba(91, 61, 245, .11); }
    .partner-auth-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: -2px; }
    .partner-auth-check { display: inline-flex; align-items: center; gap: 8px; color: var(--auth-muted); font-size: 12px; }
    .partner-auth-check input { width: 15px; height: 15px; accent-color: var(--auth-purple); }
    .partner-auth-forgot { color: var(--auth-purple); font-size: 12px; font-weight: 700; }
    .partner-auth-submit { width: 100%; min-height: 50px; margin-top: 3px; border: 1px solid var(--auth-purple); border-radius: 999px; background: var(--auth-purple); color: #fff; cursor: pointer; font: inherit; font-size: 14px; font-weight: 700; box-shadow: 0 9px 22px rgba(91, 61, 245, .17); transition: background .18s ease, transform .18s ease; }
    .partner-auth-submit:hover { background: var(--auth-purple-dark); transform: translateY(-1px); }
    .partner-auth-secondary { margin: 24px 0 0; color: var(--auth-muted); font-size: 13px; text-align: center; }
    .partner-auth-secondary a { color: var(--auth-purple); font-weight: 700; }
    .partner-auth-footnote { margin-top: auto; color: #928c99; font-size: 11px; }
    .partner-auth-visual { position: relative; display: flex; min-height: 100vh; flex-direction: column; justify-content: space-between; overflow: hidden; padding: 56px clamp(28px, 6vw, 90px); background: var(--auth-ink); color: #fff; }
    .partner-auth-visual::before { content: ''; position: absolute; top: 12%; right: 12%; width: 130px; height: 30px; background: #f7d6df; transform: rotate(5deg); }
    .partner-auth-visual-copy, .partner-auth-art, .partner-auth-visual-footer { position: relative; z-index: 1; }
    .partner-auth-visual-kicker { margin: 0; color: #c9bfff; font-size: 10px; font-weight: 700; letter-spacing: .18em; }
    .partner-auth-visual h2 { max-width: 490px; margin: 19px 0 0; font-family: 'Space Grotesk', sans-serif; font-size: clamp(38px, 4.5vw, 63px); font-weight: 600; letter-spacing: -.075em; line-height: .99; }
    .partner-auth-visual h2 em { color: #9e8bff; font-style: normal; }
    .partner-auth-art { width: min(100%, 550px); margin: 50px auto 0; padding: 11px; border: 1px solid rgba(255,255,255,.16); border-radius: 21px; background: var(--auth-lavender); transform: rotate(-2deg); }
    .partner-auth-art img { display: block; width: 100%; height: auto; border-radius: 13px; }
    .partner-auth-visual-footer { display: flex; align-items: center; justify-content: space-between; gap: 16px; color: #b7b1c1; font-size: 11px; }
    .partner-auth-visual-footer strong { color: #fff; font-weight: 600; }

    @media (max-width: 860px) {
        .partner-auth-page { display: block; }
        .partner-auth-form-side { min-height: 100vh; }
        .partner-auth-visual { display: none; }
        .partner-auth-content { padding: 74px 0 50px; }
    }
    @media (max-width: 480px) {
        .partner-auth-form-side { padding: 20px 20px 28px; }
        .partner-auth-content { padding: 62px 0 42px; }
        .partner-auth-row { align-items: flex-start; flex-direction: column; gap: 10px; }
    }
</style>
