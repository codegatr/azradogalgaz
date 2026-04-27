    </section>
</main>

</div>

<script>
// Sidebar toggle (mobil)
(function(){
    const btn = document.getElementById('admMenuBtn');
    const sb  = document.getElementById('admSidebar');
    if (!btn || !sb) return;
    btn.addEventListener('click', () => sb.classList.toggle('open'));
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 980 && !sb.contains(e.target) && !btn.contains(e.target)) {
            sb.classList.remove('open');
        }
    });
})();

// Onay sorusu (data-onay)
document.addEventListener('click', (e) => {
    const a = e.target.closest('[data-onay]');
    if (!a) return;
    if (!confirm(a.dataset.onay || 'Emin misiniz?')) e.preventDefault();
});

// Tab handler
document.querySelectorAll('[data-tabs]').forEach(grp => {
    grp.querySelectorAll('.tabs-h .t').forEach(t => t.addEventListener('click', () => {
        const k = t.dataset.tab;
        grp.querySelectorAll('.tabs-h .t').forEach(x => x.classList.toggle('active', x===t));
        grp.querySelectorAll('.tab-body').forEach(x => x.classList.toggle('active', x.dataset.tab===k));
    }));
});

// Modal helper: <a data-modal="modalId"> açar; .modal-bg .close kapatır
document.querySelectorAll('[data-modal]').forEach(t => t.addEventListener('click', e => {
    e.preventDefault();
    const m = document.getElementById(t.dataset.modal);
    if (m) m.classList.add('open');
}));
document.querySelectorAll('.modal-bg').forEach(bg => {
    bg.addEventListener('click', e => { if (e.target === bg) bg.classList.remove('open'); });
    bg.querySelectorAll('[data-close]').forEach(c => c.addEventListener('click', () => bg.classList.remove('open')));
});
</script>
</body>
</html>
