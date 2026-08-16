<div class="relative isolate overflow-hidden bg-slate-950">
    <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
        <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ec4899] to-[#a855f7] opacity-25 sm:left-[calc(50%-30rem)] sm:w-[72rem]"></div>
    </div>

    <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
        <a href="/" class="flex items-center gap-3">
            <img src="/assets/images/repostit-logo.png" alt="Repostit" class="h-9 w-auto brightness-0 invert" />
            <span class="text-sm font-semibold tracking-wide text-white/80">PARTNERS</span>
        </a>
        <div class="flex items-center gap-3 text-sm">
            <a href="/login" class="rounded-full px-4 py-2 font-semibold text-white/80 hover:text-white">Log in</a>
            <a href="/register" class="rounded-full bg-white px-4 py-2 font-semibold text-slate-900 shadow-sm hover:bg-pink-50">Join free</a>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 pb-20 pt-14 lg:px-8 lg:pb-28 lg:pt-20">
        <div class="max-w-3xl">
            <p class="inline-flex items-center rounded-full border border-pink-300/30 bg-pink-300/10 px-3 py-1 text-sm font-semibold text-pink-200">Repostit Partner Program</p>
            <h1 class="mt-6 text-4xl font-bold tracking-tight text-white sm:text-6xl">Help people get more from every video.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">Repostit turns one piece of content into platform-ready posts, so creators, founders, and online businesses can keep publishing without the copy-and-paste work.</p>
            <div class="mt-9 flex flex-wrap items-center gap-4">
                <a href="/register" class="rounded-full bg-gradient-to-r from-pink-500 to-purple-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-pink-500/20 hover:from-pink-400 hover:to-purple-400">Join the program</a>
                <a href="/login" class="text-sm font-semibold text-white/80 hover:text-white">Already a partner? Log in →</a>
            </div>
        </div>

        <div class="mt-16 grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-7 backdrop-blur">
                <p class="text-sm font-semibold text-pink-200">20% recurring</p>
                <p class="mt-3 text-2xl font-bold text-white">Earn while they stay</p>
                <p class="mt-3 text-sm leading-6 text-slate-300">You earn 20% of every valid paid subscription you refer, for as long as the customer remains active.</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-7 backdrop-blur">
                <p class="text-sm font-semibold text-purple-200">One simple link</p>
                <p class="mt-3 text-2xl font-bold text-white">Share it naturally</p>
                <p class="mt-3 text-sm leading-6 text-slate-300">Create your partner link, mention Repostit when it genuinely fits, and keep your content in your own voice.</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-7 backdrop-blur">
                <p class="text-sm font-semibold text-fuchsia-200">Clear reporting</p>
                <p class="mt-3 text-2xl font-bold text-white">See what works</p>
                <p class="mt-3 text-sm leading-6 text-slate-300">Track clicks, referrals, conversions, and earnings from your partner dashboard.</p>
            </div>
        </div>

        <div class="mt-16 max-w-2xl rounded-3xl border border-pink-200/15 bg-gradient-to-br from-pink-500/10 to-purple-500/10 p-7">
            <p class="text-sm font-semibold uppercase tracking-widest text-pink-200">How it works</p>
            <ol class="mt-5 space-y-4 text-sm leading-6 text-slate-200">
                <li><span class="mr-3 inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 font-bold text-white">1</span>Create your free partner account.</li>
                <li><span class="mr-3 inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 font-bold text-white">2</span>Try Repostit and grab your personal referral link.</li>
                <li><span class="mr-3 inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 font-bold text-white">3</span>Share it with the people who would genuinely benefit.</li>
            </ol>
        </div>
    </main>

    <footer class="border-t border-white/10 px-6 py-8 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
            <span>© <?= date('Y') ?> Repostit. Built for people who publish.</span>
            <a href="https://repostit.io" class="hover:text-white">repostit.io →</a>
        </div>
    </footer>
</div>
