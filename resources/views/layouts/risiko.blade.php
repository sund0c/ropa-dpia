<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Manajemen Risiko')</title>
    <style>
        :root {
            --latar: #1e293b;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: system-ui, sans-serif;
            color: #1f2937;
            background: var(--latar);
        }

        .wadah {
            max-width: 920px;
            margin: 0 auto;
            padding: 1.25rem 1rem 3rem;
        }

        .kepala-app {
            color: #cbd5e1;
            font-size: .85rem;
            margin-bottom: .75rem;
        }

        .kepala-app strong {
            color: #fff;
        }

        .kartu {
            background: #fff;
            border-radius: 12px;
            padding: 1.75rem 2rem;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .35);
        }

        .meta {
            background: #f3f4f6;
            padding: .75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: .9rem;
        }

        .tabs {
            display: flex;
            gap: .25rem;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
        }

        .tabs a {
            padding: .5rem .9rem;
            text-decoration: none;
            color: #4b5563;
            border-radius: 6px 6px 0 0;
        }

        .tabs a.active {
            background: #1e40af;
            color: #fff;
        }

        .field {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            gap: .25rem;
            min-width: 0;
        }

        .field input,
        .field textarea,
        .field select {
            padding: .5rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font: inherit;
            background: #fff;
            min-width: 0;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .req {
            color: #dc2626;
        }

        .hint {
            color: #6b7280;
        }

        .error {
            color: #dc2626;
        }

        .alert {
            padding: .6rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .alert.ok {
            background: #dcfce7;
        }

        .alert.warn {
            background: #fef3c7;
        }

        .alert.err {
            background: #fee2e2;
        }

        button {
            padding: .55rem 1.1rem;
            border: 0;
            border-radius: 6px;
            background: #1e40af;
            color: #fff;
            cursor: pointer;
            font: inherit;
        }

        button.secondary {
            background: #6b7280;
        }

        button.btn-merah {
            background: #b91c1c;
        }

        button.btn-merah:hover {
            background: #991b1b;
        }

        button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }

        .check {
            display: flex;
            gap: .5rem;
            align-items: flex-start;
        }

        .check input {
            margin-top: .2rem;
        }

        [hidden] {
            display: none !important;
        }

        dialog.dialog {
            border: 0;
            border-radius: 10px;
            padding: 1.5rem;
            width: min(420px, 90vw);
            box-shadow: 0 10px 40px rgba(0, 0, 0, .25);
        }

        dialog.dialog::backdrop {
            background: rgba(0, 0, 0, .45);
        }

        dialog.dialog-lebar {
            width: min(680px, 92vw);
        }

        ol.persetujuan {
            padding-left: 1.3rem;
            line-height: 1.55;
        }

        ol.persetujuan li {
            margin-bottom: .7rem;
        }

        .data-json {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: .35rem;
        }

        a.btn-garis {
            display: inline-block;
            padding: .55rem 1.1rem;
            border: 1px solid #1e40af;
            border-radius: 6px;
            color: #1e40af;
            text-decoration: none;
        }

        a.btn-garis:hover {
            background: #eff6ff;
        }

        /* Card besar A / B */
        .panel {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1.1rem 1.25rem .25rem;
            margin-bottom: 1.25rem;
        }

        .panel-head {
            font-size: 1.05rem;
            margin: -1.1rem -1.25rem 1rem;
            padding: .7rem 1.25rem;
            background: #eff6ff;
            color: #1e3a8a;
            border-bottom: 1px solid #dbeafe;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .panel-head small {
            font-weight: normal;
            font-size: .85rem;
            color: #1e40af;
        }

        fieldset.kelompok {
            border: 0;
            padding: 0;
            margin: 0;
            min-width: 0;
        }

        @media (max-width: 640px) {
            .kartu {
                padding: 1.25rem 1rem;
                border-radius: 8px;
            }

            .row {
                grid-template-columns: 1fr;
            }

            .panel {
                padding: 1rem .9rem .1rem;
            }

            .panel-head {
                margin: -1rem -.9rem 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="wadah">
        <div class="kepala-app"><a href="/" style="color:#cbd5e1;text-decoration:none">← Beranda</a> · <strong>Manajemen Risiko</strong></div>

        <main class="kartu">
            @if (session('expired'))
                <div class="alert warn">Sesi sebelumnya sudah lebih dari 24 jam dan telah dihapus. Silakan mulai baru.
                </div>
            @endif
            @if (session('saved'))
                <div class="alert ok">{{ session('saved') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Buka/tutup popup (<dialog>)
        document.addEventListener('click', (e) => {
            const opener = e.target.closest('[data-open-dialog]');
            if (opener) {
                document.getElementById(opener.dataset.openDialog)?.showModal();
                return;
            }
            const closer = e.target.closest('[data-close-dialog]');
            if (closer) closer.closest('dialog')?.close();
        });
    </script>
</body>

</html>
