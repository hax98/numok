            </div>
        </main>
        <p class="rp-caption text-text-muted px-6 pb-6 m-0">© <?= date('Y') ?> Repostit · <a class="hover:text-ink" href="https://repostit.io/">repostit.io</a></p>
    </div>
    <?php if ($authAside): ?>
    <aside class="hidden lg:flex flex-col justify-center gap-10 bg-surface-subtle border-l border-line px-12 xl:px-20 py-16" aria-label="About the partner program">
        <div>
            <p class="rp-eyebrow">Repostit partner program</p>
            <p class="rp-display-l mt-3 max-w-[16ch]">20% of every payment, every month.</p>
        </div>
        <video class="block w-full max-w-[640px] h-auto rounded-2xl border border-line bg-surface" src="/assets/media/workflows-loop.mp4" poster="/assets/media/workflows-poster.jpg" width="1600" height="1094" muted loop playsinline autoplay preload="metadata" aria-label="The Repostit Workflows screen: TikTok reposting to Instagram Reels, YouTube Shorts, Facebook Reels and X"></video>
        <ul class="list-none m-0 p-0 grid gap-3 text-text-secondary">
            <li class="flex gap-3"><strong class="w-16 shrink-0 font-medium text-ink">Free</strong> No fees and no minimum audience size</li>
            <li class="flex gap-3"><strong class="w-16 shrink-0 font-medium text-ink">$200</strong> Bonus at 20 paying subscribers</li>
            <li class="flex gap-3"><strong class="w-16 shrink-0 font-medium text-ink">$5,000</strong> More when you reach 500</li>
        </ul>
    </aside>
    <?php endif; ?>
</div>
