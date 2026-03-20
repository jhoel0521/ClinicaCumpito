<style>
    * {
        box-sizing: border-box;
    }
    body {
        font-family:
            DejaVu Sans,
            sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        margin: 0;
        padding: 0;
    }
    .page {
        padding: 28px 32px 110px;
    }
    h3.section-title {
        font-size: 12px;
        font-weight: bold;
        color: #0d7b5c;
        margin: 14px 0 4px;
        padding: 4px 8px;
        background: #f0faf6;
        border-left: 3px solid #0d7b5c;
    }
    table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }
    table.data-table th {
        background: #f0faf6;
        border: 1px solid #b2ddd0;
        padding: 5px 8px;
        text-align: left;
        font-size: 9px;
        text-transform: uppercase;
        color: #0d7b5c;
    }
    table.data-table td {
        border: 1px solid #d1e8e2;
        padding: 5px 8px;
        vertical-align: top;
        font-size: 11px;
    }
    .instructions-row td {
        border-top: none;
        border-left: 1px solid #d1e8e2;
        border-right: 1px solid #d1e8e2;
        border-bottom: 1px solid #d1e8e2;
        padding: 2px 8px 5px 16px;
        font-style: italic;
        color: #555;
        font-size: 10px;
    }
    .observations {
        font-size: 10px;
        color: #555;
        font-style: italic;
        margin-top: 4px;
        padding: 4px 8px;
        border-left: 2px solid #d1e8e2;
    }
    .badge-pending {
        display: inline-block;
        padding: 2px 7px;
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .badge-received {
        display: inline-block;
        padding: 2px 7px;
        background: #d1e7dd;
        color: #0a3622;
        border: 1px solid #badbcc;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .abnormal {
        color: #dc3545;
        font-weight: bold;
    }
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 10px 32px 14px;
        background: white;
        border-top: 1px solid #ccc;
        text-align: right;
        font-size: 10px;
        color: #555;
    }
    .separator {
        margin: 18px 0;
        border: none;
        border-top: 1px dashed #d1e8e2;
    }
</style>
