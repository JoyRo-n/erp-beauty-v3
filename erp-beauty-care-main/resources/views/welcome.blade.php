
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Care ERP</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f7f7fb;
            color: #29263a;
        }
        .layout { display: flex; min-height: 100vh; }
        aside {
            width: 245px;
            background: #302b43;
            color: white;
            padding: 28px 18px;
        }
        .brand { font-size: 20px; font-weight: bold; margin-bottom: 8px; }
        .subtitle { font-size: 12px; color: #c8c3d7; margin-bottom: 35px; }
        .menu-title {
            font-size: 11px;
            color: #aaa4bf;
            margin: 24px 10px 10px;
            text-transform: uppercase;
        }
        .menu {
            display: block;
            padding: 12px 10px;
            margin: 5px 0;
            color: #e8e4f0;
            text-decoration: none;
            border-radius: 7px;
            font-size: 14px;
        }
        .menu.active, .menu:hover { background: #514766; color: white; }
        main { flex: 1; min-width: 0; }
        header {
            background: white;
            padding: 20px 32px;
            border-bottom: 1px solid #eceaf2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header span { color: #777287; font-size: 13px; }
        .content { padding: 32px; }
        h1 { font-size: 27px; margin: 0 0 10px; }
        .intro { color: #777287; margin-bottom: 28px; }
        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }
        .card {
            background: white;
            border: 1px solid #eeecf3;
            border-radius: 12px;
            padding: 22px;
        }
        .card-label { color: #777287; font-size: 13px; }
        .card-value { font-size: 24px; font-weight: bold; margin-top: 13px; }
        .section {
            margin-top: 25px;
            background: white;
            border: 1px solid #eeecf3;
            border-radius: 12px;
            padding: 24px;
        }
        .section h2 { font-size: 18px; margin: 0 0 8px; }
        .section p { color: #777287; font-size: 14px; line-height: 1.7; }
        .status {
            display: inline-block;
            padding: 6px 10px;
            background: #eeeaf8;
            color: #554276;
            border-radius: 20px;
            font-size: 12px;
        }
        @media (max-width: 800px) {
            .layout { display: block; }
            aside { width: 100%; }
            .cards { grid-template-columns: 1fr; }
            .content { padding: 22px; }
            header { padding: 18px 22px; }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside>
        <div class="brand">Beauty Care ERP</div>
        <div class="subtitle">Arunika Beauty Nusantara</div>

        <div class="menu-title">Workspace</div>
        <a class="menu active" href="/">Dashboard</a>

        <div class="menu-title">Sales</div>
        <a class="menu" href="#">Customers</a>
        <a class="menu" href="#">CRM / Leads</a>
        <a class="menu" href="#">Quotations</a>
        <a class="menu" href="#">Sales Orders</a>

        <div class="menu-title">Operations</div>
        <a class="menu" href="#">Inventory</a>
        <a class="menu" href="#">Purchasing</a>
        <a class="menu" href="#">Finance</a>
    </aside>

    <main>
        <header>
            <strong>Dashboard</strong>
            <span>PT Arunika Beauty Nusantara</span>
        </header>

        <div class="content">
            <h1>Welcome to Beauty Care ERP</h1>
            <p class="intro">Satu workspace untuk mengelola operasional bisnis kecantikan.</p>

            <div class="cards">
                <div class="card">
                    <div class="card-label">Customers</div>
                    <div class="card-value">—</div>
                    <span class="status">Belum ada data</span>
                </div>
                <div class="card">
                    <div class="card-label">Sales Orders</div>
                    <div class="card-value">—</div>
                    <span class="status">Belum ada data</span>
                </div>
                <div class="card">
                    <div class="card-label">CRM Leads</div>
                    <div class="card-value">—</div>
                    <span class="status">Belum ada data</span>
                </div>
            </div>

            <div class="section">
                <h2>Getting Started</h2>
                <p>
                    Dashboard awal ERP Beauty Care telah disiapkan.
                    Modul Sales & CRM akan dikembangkan berikutnya,
                    kemudian diintegrasikan dengan inventory dan finance.
                </p>
                <span class="status">Initial UI · Development</span>
            </div>
        </div>
    </main>
</div>
</body>
</html>
