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
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 12px 20px;
            margin: 20px auto;
            width: 210mm;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            margin: 0 auto 30px auto;
            background: #fff;
            overflow: hidden;
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

            body {
                background: #fff;
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
        <span style="font-weight: 500; color: #334155;">Aperçu avant impression du Bon de Livraison</span>
        <button onclick="window.print()" class="btn-print">🖨️ Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="page">

        {{-- Bandeau bleu + forme rouge + logo --}}
        <div class="top-band"></div>
        <div class="top-red"></div>
        <div class="logo-wrap">
            <img src="{{ asset('images/LOGO_BLANC_simple.png') }}" alt="Digital Indoor Inc.">
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