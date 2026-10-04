import { initializeApp } from 'https://www.gstatic.com/firebasejs/11.10.0/firebase-app.js';
import { getAuth, GoogleAuthProvider, signInWithPopup, signInWithEmailAndPassword, signOut, setPersistence, browserSessionPersistence } from 'https://www.gstatic.com/firebasejs/11.10.0/firebase-auth.js';
const root = document.getElementById('repostit-linker');
const config = document.getElementById('firebase-public-config');
if (root && config) {
    const auth = getAuth(initializeApp(JSON.parse(config.textContent), 'partner-portal'));
    await setPersistence(auth, browserSessionPersistence);
    const status = document.getElementById('connection-status');
    const consent = document.getElementById('link-consent');
    let busy = false;
    const run = async (signIn) => {
        if (busy) return;
        if (!consent.checked) { status.textContent = 'Please authorize linking your account first.'; return; }
        busy = true;
        root.querySelectorAll('button').forEach(button => { button.disabled = true; });
        status.textContent = 'Verifying your account and loading available social metrics...';
        try {
            const user = await signIn();
            if (!user) throw new Error('Sign in to your existing Repostit account with Google or email first.');
            const response = await fetch('/creator/connect', { method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': root.dataset.csrf },
                body: JSON.stringify({ idToken: await user.getIdToken(true), consent: true }) });
            const data = await response.json();
            if (!response.ok || data.error) throw new Error(data.error || 'Unable to link this account.');
            status.textContent = 'Account linked. Your workspace is refreshing.';
            location.reload();
        } catch (error) {
            const messages = { 'auth/unauthorized-domain': 'Google sign-in is not enabled for this portal domain yet. Try your Repostit email and password.',
                'auth/popup-blocked': 'Allow the Google sign-in popup, then try again.', 'auth/popup-closed-by-user': 'Sign-in was cancelled. Nothing was linked.',
                'auth/invalid-credential': 'Those Repostit sign-in details were not accepted.', 'auth/too-many-requests': 'Sign-in is temporarily limited. Please try later.' };
            status.textContent = messages[error.code] || error.message || 'Connection unavailable. Please retry later.';
        } finally { busy = false; root.querySelectorAll('button').forEach(button => { button.disabled = false; }); }
    };
    document.getElementById('connect-google').addEventListener('click', () => run(async () => {
        const provider = new GoogleAuthProvider(); provider.setCustomParameters({ prompt: 'select_account' });
        return (await signInWithPopup(auth, provider)).user;
    }));
    document.getElementById('refresh-repostit').addEventListener('click', () => run(async () => { await auth.authStateReady(); return auth.currentUser; }));
    document.getElementById('repostit-email-login').addEventListener('submit', event => {
        event.preventDefault();
        run(async () => {
            const password = document.getElementById('repostit-password');
            try { return (await signInWithEmailAndPassword(auth, document.getElementById('repostit-email').value.trim(), password.value)).user; }
            finally { password.value = ''; }
        });
    });
    document.getElementById('disconnect-repostit')?.closest('form').addEventListener('submit', () => { signOut(auth); });
}
