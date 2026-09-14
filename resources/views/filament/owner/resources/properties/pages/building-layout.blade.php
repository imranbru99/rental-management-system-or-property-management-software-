<x-filament-panels::page>
    <div class="bl" x-data>
        <div class="bl-guide">
            <div>
                <p class="bl-guide-kicker">How to edit</p>
                <p class="bl-guide-copy">Click a unit card to open a full editor. Only one unit stays open so the page stays readable. Drag the handle to reorder, then save.</p>
            </div>
        </div>

        {{ $this->content }}
    </div>

    <style>
        .bl { display: flex; flex-direction: column; gap: 1rem; }
        .bl-guide {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.95rem 1.15rem;
            border-radius: 1.1rem;
            background: linear-gradient(135deg, #ecfdf5, #f0f9ff);
            border: 1px solid #99f6e4;
        }
        .bl-guide-kicker {
            margin: 0;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #0f766e;
        }
        .bl-guide-copy { margin: 0.2rem 0 0; color: #334155; font-size: 0.9rem; }

        .bl .bl-chip {
            display: flex;
            flex-direction: column;
            gap: 0.28rem;
            min-width: 0;
            width: 100%;
        }
        .bl .bl-chip-top,
        .bl .bl-chip-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .bl .bl-chip-code { font-size: 1.15rem; font-weight: 800; color: #0f172a; letter-spacing: -.02em; }
        .bl .bl-chip-name { color: #64748b; font-size: 0.82rem; font-weight: 600; }
        .bl .bl-chip-rent { font-weight: 800; color: #0f766e; font-size: 0.92rem; }
        .bl .bl-chip-rent small { margin-left: 0.2rem; font-weight: 600; color: #64748b; }
        .bl .bl-chip-spaces { font-size: 0.72rem; font-weight: 700; color: #64748b; line-height: 1.25; text-align: right; }
        .bl .bl-pill {
            display: inline-flex;
            padding: 0.12rem 0.5rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: capitalize;
        }
        .bl .bl-pill.ok { background: #ccfbf1; color: #0f766e; }
        .bl .bl-pill.wait { background: #fef3c7; color: #b45309; }
        .bl .bl-pill.fix { background: #fee2e2; color: #b91c1c; }
        .bl .bl-pill.idle { background: #e2e8f0; color: #475569; }
        .bl .bl-floor-label {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-width: 0;
        }
        .bl .bl-floor-name { font-weight: 800; color: #0f172a; }
        .bl .bl-floor-meta { color: #64748b; font-size: 0.8rem; font-weight: 600; }

        .bl .bl-floors > .fi-fo-repeater-items {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }
        .bl .bl-floors > .fi-fo-repeater-items > .fi-fo-repeater-item {
            overflow: hidden;
            border-radius: 1.25rem;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }
        .bl .bl-floors > .fi-fo-repeater-items > .fi-fo-repeater-item > .fi-fo-repeater-item-header {
            padding: 0.95rem 1.1rem;
            background: #fff;
        }
        .bl .bl-floors > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) {
            ring-color: transparent;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
        }
        .bl .bl-floors > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) > .fi-fo-repeater-item-header {
            background: linear-gradient(90deg, #f0fdfa, #fff);
            border-bottom: 1px solid #ccfbf1;
        }
        .bl .bl-floors > .fi-fo-repeater-items > .fi-fo-repeater-item > .fi-fo-repeater-item-content {
            padding: 1rem 1rem 1.15rem;
            background: #f8fafc;
        }

        .bl .bl-units > .fi-fo-repeater-items {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(16.25rem, 1fr));
            gap: 0.75rem;
            align-items: stretch;
        }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item {
            display: flex;
            flex-direction: column;
            min-height: 8.25rem;
            border-radius: 1.15rem;
            background: #fff;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item.fi-collapsed {
            cursor: pointer;
        }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item.fi-collapsed:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(13, 148, 136, 0.12);
        }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item.fi-collapsed > .fi-fo-repeater-item-header {
            flex: 1;
            align-items: flex-start;
            padding: 0.95rem 1rem;
        }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item.fi-collapsed .fi-fo-repeater-item-header-label {
            overflow: visible;
            white-space: normal;
        }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) {
            grid-column: 1 / -1;
            border: 2px solid #14b8a6;
            box-shadow: 0 18px 40px rgba(13, 148, 136, 0.14);
            background: #fff;
        }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) > .fi-fo-repeater-item-header {
            padding: 1rem 1.2rem;
            background: linear-gradient(90deg, #0f766e, #0e7490);
        }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) .bl-chip-code,
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) .bl-chip-name,
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) .bl-chip-spaces,
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) .bl-chip-rent,
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) .bl-chip-rent small {
            color: #fff;
        }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) .bl-pill.ok { background: #ccfbf1; color: #0f766e; }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) .bl-pill.wait { background: #fef3c7; color: #b45309; }
        .bl .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) > .fi-fo-repeater-item-content {
            padding: 1.25rem 1.25rem 1.4rem;
            background: linear-gradient(#ffffff, #f8fafc);
        }
        .bl .bl-units .fi-fo-repeater-item-header-label.fi-truncated { overflow: visible; text-overflow: unset; white-space: normal; }

        .bl .bl-spaces > .fi-fo-repeater-items {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(12.5rem, 1fr));
            gap: 0.7rem;
        }
        .bl .bl-spaces > .fi-fo-repeater-items > .fi-fo-repeater-item {
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .bl .bl-spaces > .fi-fo-repeater-items > .fi-fo-repeater-item > .fi-fo-repeater-item-header {
            padding: 0.65rem 0.75rem 0.35rem;
        }
        .bl .bl-spaces > .fi-fo-repeater-items > .fi-fo-repeater-item > .fi-fo-repeater-item-content {
            padding: 0.35rem 0.75rem 0.85rem;
        }

        .bl .bl-units > .fi-fo-repeater-add,
        .bl .bl-spaces > .fi-fo-repeater-add,
        .bl .bl-floors > .fi-fo-repeater-add { margin-top: 0.35rem; }

        .dark .bl-guide { background: linear-gradient(135deg, #042f2e, #0f172a); border-color: rgba(45, 212, 191, 0.25); }
        .dark .bl-guide-copy { color: #cbd5e1; }
        .dark .bl-chip-code,
        .dark .bl-floor-name { color: #f8fafc; }
        .dark .bl-floors > .fi-fo-repeater-items > .fi-fo-repeater-item > .fi-fo-repeater-item-header,
        .dark .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item,
        .dark .bl-spaces > .fi-fo-repeater-items > .fi-fo-repeater-item { background: #111827; }
        .dark .bl-floors > .fi-fo-repeater-items > .fi-fo-repeater-item > .fi-fo-repeater-item-content { background: #0b1220; }
        .dark .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item:not(.fi-collapsed) > .fi-fo-repeater-item-content {
            background: linear-gradient(#111827, #0b1220);
        }

        @media (max-width: 720px) {
            .bl .bl-units > .fi-fo-repeater-items,
            .bl .bl-spaces > .fi-fo-repeater-items { grid-template-columns: 1fr; }
        }
    </style>

    <script>
        document.addEventListener('click', (event) => {
            const header = event.target.closest('.bl-floors > .fi-fo-repeater-items > .fi-fo-repeater-item > .fi-fo-repeater-item-header, .bl-units > .fi-fo-repeater-items > .fi-fo-repeater-item > .fi-fo-repeater-item-header');
            if (! header) {
                return;
            }

            if (event.target.closest('button, a, [x-sortable-handle]')) {
                return;
            }

            const item = header.closest('.fi-fo-repeater-item');
            const list = item?.parentElement;
            if (! item || ! list) {
                return;
            }

            requestAnimationFrame(() => {
                list.querySelectorAll(':scope > .fi-fo-repeater-item').forEach((other) => {
                    if (other === item) {
                        return;
                    }

                    const data = window.Alpine?.$data(other);
                    if (data && data.isCollapsed === false) {
                        data.isCollapsed = true;
                    }
                });

                if (! item.classList.contains('fi-collapsed')) {
                    item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        });
    </script>
</x-filament-panels::page>
