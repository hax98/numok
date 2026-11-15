            </div>
        </main>
        
        <?php if (isset($_SESSION['partner_id'])): ?>
        <footer class="mt-auto">
            <div class="border-t border-line-soft">
                <div class="rp-page py-6 flex flex-wrap items-center justify-between gap-3 rp-caption text-text-muted">
                    <span>© <?= date('Y') ?> Repostit Partners</span>
                    <a class="hover:text-ink" href="https://repostit.io/">repostit.io</a>
                </div>
            </div>
        </footer>
        <?php endif; ?>
    </div>
</body>
</html>
