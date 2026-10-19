<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Livraison - DIINC-{{ str_pad($out->id, 4, '0', STR_PAD_LEFT) }}/BL</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Calibri, Carlito, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            background-color: #e5e7eb;
        }

        /* ---------- Barre d'aperçu (non imprimée) ---------- */
        .no-print-bar {
            position: fixed;
            top: 15px;
            right: 20px;
            z-index: 100;
        }

        .btn-print {
            background-color: #4f46e5;
            color: white;
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
        }

        .btn-print:hover {
            background-color: #4338ca;
        }

        /* ---------- Page A4 ---------- */
        .page {
            position: relative;
            width: 210mm;
            height: 296mm;
            margin: 0 auto;
            background: #fff;
            overflow: hidden;
            page-break-after: avoid;
            page-break-inside: avoid;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
        }

        /* ---------- Bandeau d'en-tête ---------- */
        .top-band {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 24.5mm;
            background-color: #8EAADB;
        }

        .top-red {
            position: absolute;
            top: 0;
            left: 150mm;
            width: 60mm;
            height: 17mm;
            background-color: #FF0000;
            clip-path: polygon(0 0, 100% 0, 81% 100%, 11% 64%);
        }

        .logo-wrap {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 24.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-wrap img {
            height: 15mm;
            width: auto;
            display: block;
        }

        .logo-text {
            color: #fff;
            font-family: "Arial Narrow", Arial, sans-serif;
            font-weight: bold;
            font-size: 10.5pt;
            margin-left: 1mm;
            margin-top: 4mm;
            letter-spacing: 0.2px;
        }

        /* ---------- Titre ---------- */
        .title-box {
            position: absolute;
            top: 26.4mm;
            right: 10mm;
            width: 111mm;
            height: 19mm;
            border: 1px solid #000;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #203864;
            font-weight: bold;
            font-size: 27pt;
            white-space: nowrap;
        }

        /* ---------- Bloc gris REF / DATE / CLIENT ---------- */
        .info-panel {
            position: absolute;
            top: 46.6mm;
            left: 11.8mm;
            width: 184.5mm;
            height: 29mm;
            background-color: #BFBFBF;
            border: 1px solid #2F5597;
        }

        .info-box {
            position: absolute;
            background: #fff;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            padding: 0 2.5mm;
            font-size: 11pt;
        }

        .box-ref {
            top: 7mm;
            left: 4mm;
            width: 55mm;
            height: 8.2mm;
        }

        .box-ref b {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            margin-left: 1.5mm;
        }

        .box-date {
            top: 7mm;
            left: 122mm;
            width: 56mm;
            height: 7mm;
        }

        .box-client {
            top: 21mm;
            left: 120.5mm;
            width: 62mm;
            min-height: 8mm;
            font-weight: bold;
            font-size: 10pt;
            line-height: 1.15;
            padding-top: 1mm;
            padding-bottom: 1mm;
        }

        /* ---------- Contenu principal ---------- */
        .content {
            position: absolute;
            top: 75.6mm;
            left: 0;
            width: 100%;
        }

        .objet {
            margin-top: 5mm;
            text-align: center;
            font-family: "Times New Roman", Times, serif;
            font-size: 12.5pt;
            line-height: 8mm;
            text-transform: uppercase;
        }

        .objet u {
            font-weight: bold;
        }

        .items-wrap {
            margin: 4.5mm 5.5mm 0 5.5mm;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-family: "Times New Roman", Times, serif;
            border: 1px solid #000;
        }

        .items-table thead th {
            background-color: #8EAADB;
            border: 1px solid #000;
            font-weight: bold;
            font-size: 13pt;
            text-align: center;
            height: 6.2mm;
            padding: 0 1mm;
            line-height: 1;
            text-transform: uppercase;
        }

        .items-table tbody td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-top: none;
            border-bottom: none;
            padding: 1.5mm 2mm;
            font-size: 11pt;
            vertical-align: top;
        }

        .items-table tbody tr.filler td {
            height: 8mm;
            padding: 0;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 1px solid #000;
        }

        /* ---------- Signatures ---------- */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin: 14mm 36mm 0 25mm;
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
        }

        /* ---------- Pied de page ---------- */
        .footer-item {
            position: absolute;
            bottom: 9mm;
            height: 7.7mm;
            display: flex;
            align-items: center;
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
        }

        .footer-item svg {
            width: 7.7mm;
            height: 7.7mm;
            margin-right: 3mm;
            flex-shrink: 0;
        }

        .f-phone { left: 31mm; }
        .f-mail  { left: 79mm; }
        .f-addr  { left: 129.5mm; }

        /* ---------- Impression ---------- */
        @media print {
            .no-print-bar {
                display: none !important;
            }

            html, body {
                background: #fff;
                width: 210mm;
                height: 296mm;
                overflow: hidden;
            }

            .page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button onclick="window.print()" class="btn-print">Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="page">

        {{-- Bandeau bleu + forme rouge + logo --}}
        <div class="top-band"></div>
        <div class="top-red"></div>
        <div class="logo-wrap">
            <img src="data:image/png;base64,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" alt="">
            <span class="logo-text">Digital Indoor Inc.</span>
        </div>

        {{-- Titre --}}
        <div class="title-box">BON DE LIVRAISON</div>

        {{-- Bloc gris : REF / DATE / CLIENT --}}
        <div class="info-panel">
            <div class="info-box box-ref">
                REF :&nbsp;<b>DIINC-{{ str_pad($out->id, 4, '0', STR_PAD_LEFT) }}/BL</b>
            </div>

            <div class="info-box box-date">
                DATE :&nbsp;{{ $out->movement_date ? \Carbon\Carbon::parse($out->movement_date)->format('d/m/Y') : date('d/m/Y') }}
            </div>

            <div class="info-box box-client">
                CLIENT :&nbsp;{{ $out->project->name ?? $out->other_destination ?? 'N/A' }}
            </div>
        </div>

        <div class="content">

            {{-- Objet --}}
            <div class="objet">
                <u>OBJET</u> : {{ $out->reason ?? 'FOURNITURE ET LIVRAISON DE MATÉRIEL' }}
            </div>

            {{-- Tableau --}}
            <div class="items-wrap">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 37.6%;">Désignation</th>
                            <th style="width: 26.9%;">Modèle</th>
                            <th style="width: 8.7%;">Qté</th>
                            <th style="width: 26.8%;">Observation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedItems = $out->equipmentItems->groupBy('equipment_id');
                            $fillerRows = max(0, 5 - $groupedItems->count());
                        @endphp

                        @foreach($groupedItems as $equipmentId => $items)
                            @php $firstItem = $items->first(); @endphp
                            <tr>
                                <td>
                                    <strong>{{ $firstItem->equipment->title ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    {{ $firstItem->equipment->features ?? '—' }}
                                    @if($items->pluck('serial_number')->filter()->isNotEmpty())
                                        <br><small style="color: #444;">S/N: {{ $items->pluck('serial_number')->implode(', ') }}</small>
                                    @endif
                                </td>
                                <td style="text-align: center; font-weight: bold;">
                                    {{ $items->count() }}
                                </td>
                                <td>
                                    {{ $out->reason ?? '' }}
                                </td>
                            </tr>
                        @endforeach

                        @for($i = 0; $i < $fillerRows; $i++)
                            <tr class="filler">
                                <td></td><td></td><td></td><td></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Signatures --}}
            <div class="signatures">
                <span>DIGITAL INDOOR INC</span>
                <span>LE CLIENT</span>
            </div>
        </div>

        {{-- Pied de page --}}
        <div class="footer-item f-phone">
            <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="2" width="36" height="36" rx="3" fill="#000"/>
                <rect x="2" y="30" width="36" height="8" fill="#000"/>
                <path d="M12 9c-1.5 0-3 1.4-3 3 0 8 8 16 16 16 1.6 0 3-1.500 3-3l-4-3-2.500 1.500c-3-1-6-4-7-7L17 14l-3-5z" fill="none" stroke="#fff" stroke-width="2" stroke-linejoin="round"/>
            </svg>
            <div>+(225)22 27 46 76 41<br>+(225)0708146227</div>
        </div>

        <div class="footer-item f-mail">
            <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="7" width="36" height="26" fill="none" stroke="#000" stroke-width="2.500"/>
                <path d="M2 7l18 15L38 7M2 33l14-13M38 33L24 20" fill="none" stroke="#000" stroke-width="2.500"/>
            </svg>
            <div>Dincinc@gmail.com<br>digitalindoor.com</div>
        </div>

        <div class="footer-item f-addr">
            <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 2C12.800 2 7 7.800 7 15c0 9.500 13 23 13 23s13-13.500 13-23C33 7.800 27.200 2 20 2z" fill="#000"/>
                <circle cx="20" cy="15" r="5" fill="#fff"/>
            </svg>
            <div>Résidence Ecosys 2/Cocody<br>Rivera 4 M'Badon<br>24 BP 2460 ABIDJAN 24</div>
        </div>

    </div>

</body>
</html>